<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\AffiliateProduct;
use App\Models\Post;
use App\Services\MediaStorageService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class BlogController extends Controller
{
    public function index(Request $request)
    {
        $search = trim($request->string('q')->toString());
        $sort = $request->string('sort', 'latest')->toString();

        $baseQuery = Post::query()
            ->where('is_published', true)
            ->where(function ($q) {
                $q->whereNull('published_at')
                    ->orWhere('published_at', '<=', now());
            })
            ->with('author:id,name');

        if ($search !== '') {
            $baseQuery->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('excerpt', 'like', "%{$search}%")
                    ->orWhere('content', 'like', "%{$search}%");
            });
        }

        if ($sort === 'popular') {
            $baseQuery->orderByDesc('view_count')->orderByDesc('published_at');
        } else {
            $baseQuery->orderByDesc('published_at')->orderByDesc('created_at');
        }

        $posts = $baseQuery
            ->paginate(9)
            ->withQueryString()
            ->through(fn ($p) => $this->transformPostCard($p));

        // ดึงบทความยอดนิยม 4 อันดับสำหรับแสดงเป็นไฮไลต์
        $popularPosts = Post::query()
            ->where('is_published', true)
            ->where(function ($q) {
                $q->whereNull('published_at')
                    ->orWhere('published_at', '<=', now());
            })
            ->with('author:id,name')
            ->orderByDesc('view_count')
            ->orderByDesc('published_at')
            ->limit(4)
            ->get()
            ->map(fn ($p) => $this->transformPostCard($p))
            ->values()
            ->all();

        return Inertia::render('Blog/Index', [
            'posts' => $posts,
            'popularPosts' => $popularPosts,
            'filters' => [
                'q' => $search,
                'sort' => $sort,
            ],
        ]);
    }

    public function show(Request $request, string $slug)
    {
        $post = Post::query()
            ->where('slug', $slug)
            ->where('is_published', true)
            ->where(function ($q) {
                $q->whereNull('published_at')
                    ->orWhere('published_at', '<=', now());
            })
            ->with('author:id,name')
            ->firstOrFail();

        // นับยอดวิว 1 ครั้งต่อ Session ต่อบทความ
        $viewedPosts = $request->session()->get('viewed_blog_posts', []);
        $isNewView = !in_array($post->id, $viewedPosts, true);
        if ($isNewView) {
            $post->increment('view_count');
            $viewedPosts[] = $post->id;
            $request->session()->put('viewed_blog_posts', $viewedPosts);
        }

        // บทความแนะนำอื่นๆ (3 บทความล่าสุด/ยอดนิยมที่ไม่ใช่บทความนี้)
        $relatedPosts = Post::query()
            ->where('is_published', true)
            ->where('id', '!=', $post->id)
            ->where(function ($q) {
                $q->whereNull('published_at')
                    ->orWhere('published_at', '<=', now());
            })
            ->with('author:id,name')
            ->orderByDesc('published_at')
            ->orderByDesc('view_count')
            ->limit(3)
            ->get()
            ->map(fn ($p) => $this->transformPostCard($p))
            ->values()
            ->all();

        // ดึงสินค้าสายมู (Affiliate) มาแสดงท้ายบทความเพื่อเพิ่มโอกาสคลิกซื้อ
        $affiliateProducts = AffiliateProduct::query()
            ->with('platform:id,name,logo_url')
            ->where('is_active', true)
            ->whereHas('platform', fn ($q) => $q->where('is_active', true))
            ->orderBy('sort_order')
            ->limit(4)
            ->get(['id', 'platform_id', 'title', 'description', 'image_url', 'affiliate_url']);

        return Inertia::render('Blog/Show', [
            'post' => [
                'id' => $post->id,
                'title' => $post->title,
                'slug' => $post->slug,
                'excerpt' => $post->excerpt,
                'content' => $this->normalizeStorageUrls($post->content),
                'cover_image_url' => MediaStorageService::url($post->cover_image),
                'author_name' => $post->author?->name ?? 'ทีมงานแจกตัง',
                'published_at' => ($post->published_at ?? $post->created_at)?->format('d/m/Y'),
                'published_at_iso' => ($post->published_at ?? $post->created_at)?->toIso8601String(),
                'view_count' => (int) ($post->view_count ?? 0) + ($isNewView ? 1 : 0),
                'reading_time' => $this->estimateReadingMinutes($post->content),
                'meta_title' => $post->meta_title ?: $post->title,
                'meta_description' => $post->meta_description ?: $post->excerpt,
            ],
            'relatedPosts' => $relatedPosts,
            'affiliateProducts' => $affiliateProducts,
        ]);
    }

    private function transformPostCard(Post $p): array
    {
        return [
            'id' => $p->id,
            'title' => $p->title,
            'slug' => $p->slug,
            'excerpt' => $p->excerpt,
            'cover_image_url' => MediaStorageService::url($p->cover_image),
            'author_name' => $p->author?->name ?? 'ทีมงานแจกตัง',
            'published_at' => ($p->published_at ?? $p->created_at)?->format('d/m/Y'),
            'published_at_iso' => ($p->published_at ?? $p->created_at)?->toIso8601String(),
            'view_count' => (int) ($p->view_count ?? 0),
            'reading_time' => $this->estimateReadingMinutes($p->content),
        ];
    }

    /**
     * แปลง URL รูปที่อาจถูกบันทึกเป็น http://localhost/storage/... ให้เป็น Relative URL /storage/...
     */
    private function normalizeStorageUrls(?string $html): string
    {
        if (!$html) {
            return '';
        }

        return preg_replace('#https?://(localhost|127\.0\.0\.1)(:\d+)?/storage/#i', '/storage/', $html) ?? $html;
    }

    private function estimateReadingMinutes(?string $html): int
    {
        $plain = trim(strip_tags((string) $html));
        $charCount = mb_strlen($plain, 'UTF-8');
        return max(1, (int) ceil($charCount / 600));
    }
}

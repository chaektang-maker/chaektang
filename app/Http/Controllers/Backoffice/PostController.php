<?php

namespace App\Http\Controllers\Backoffice;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Services\MediaStorageService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;

class PostController extends Controller
{
    public function index(Request $request)
    {
        $search = trim($request->string('q')->toString());
        $status = $request->string('status', 'all')->toString();

        $query = Post::with('author:id,name');

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%")
                    ->orWhere('excerpt', 'like', "%{$search}%");
            });
        }

        if ($status === 'published') {
            $query->where('is_published', true);
        } elseif ($status === 'draft') {
            $query->where('is_published', false);
        }

        $posts = $query
            ->orderByDesc('created_at')
            ->get()
            ->map(fn ($p) => [
                'id' => $p->id,
                'title' => $p->title,
                'slug' => $p->slug,
                'excerpt' => Str::limit($p->excerpt, 100),
                'cover_image' => MediaStorageService::url($p->cover_image),
                'author_name' => $p->author?->name,
                'published_at' => $p->published_at?->format('Y-m-d H:i'),
                'is_published' => (bool) $p->is_published,
                'view_count' => (int) ($p->view_count ?? 0),
                'created_at' => $p->created_at?->format('Y-m-d'),
            ]);

        $stats = [
            'total' => Post::count(),
            'published' => Post::where('is_published', true)->count(),
            'draft' => Post::where('is_published', false)->count(),
            'total_views' => (int) Post::sum('view_count'),
        ];

        return Inertia::render('Backoffice/Posts/Index', [
            'posts' => $posts,
            'filters' => [
                'q' => $search,
                'status' => $status,
            ],
            'stats' => $stats,
        ]);
    }

    public function create(Request $request)
    {
        return Inertia::render('Backoffice/Posts/Create');
    }

    /**
     * อัปโหลดรูปแทรกในเนื้อหา (ใช้จาก Rich Text Editor และฟอร์มสินค้า/แพลตฟอร์ม)
     */
    public function uploadImage(Request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:4096',
            'folder' => 'nullable|string|in:posts,affiliate-products,platforms',
        ]);

        $folder = $request->input('folder', 'posts');
        $categoryMap = [
            'posts' => ['dir' => 'posts/content', 'prefix' => 'post-img'],
            'affiliate-products' => ['dir' => 'affiliate-products', 'prefix' => 'product'],
            'platforms' => ['dir' => 'platforms', 'prefix' => 'platform'],
        ];
        $target = $categoryMap[$folder] ?? $categoryMap['posts'];

        $storedPath = MediaStorageService::store($request->file('image'), $target['dir'], $target['prefix']);
        $url = MediaStorageService::url($storedPath);

        return response()->json([
            'url' => $url,
            'path' => $storedPath,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255',
            'excerpt' => 'nullable|string|max:1000',
            'content' => 'required|string',
            'cover_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:4096',
            'published_at' => 'nullable|date',
            'is_published' => 'boolean',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
        ]);

        $validated['author_id'] = $request->user()->id;
        $validated['slug'] = Post::generateUniqueSlug($validated['slug'] ?: $validated['title']);
        $validated['is_published'] = (bool) ($validated['is_published'] ?? false);

        if ($validated['is_published'] && empty($validated['published_at'])) {
            $validated['published_at'] = now();
        }

        if ($request->hasFile('cover_image')) {
            $validated['cover_image'] = MediaStorageService::store(
                $request->file('cover_image'),
                'posts/covers',
                'cover'
            );
        }

        Post::create($validated);

        return redirect()->route('backoffice.posts.index')->with('success', 'สร้างบทความเรียบร้อยแล้ว');
    }

    public function edit(Request $request, Post $post)
    {
        $normalizedContent = $post->content
            ? (preg_replace('#https?://(localhost|127\.0\.0\.1)(:\d+)?/storage/#i', '/storage/', $post->content) ?? $post->content)
            : '';

        return Inertia::render('Backoffice/Posts/Edit', [
            'post' => [
                'id' => $post->id,
                'title' => $post->title,
                'slug' => $post->slug,
                'excerpt' => $post->excerpt,
                'content' => $normalizedContent,
                'cover_image' => MediaStorageService::url($post->cover_image),
                'cover_image_path' => $post->cover_image,
                'published_at' => $post->published_at?->format('Y-m-d\TH:i'),
                'is_published' => (bool) $post->is_published,
                'meta_title' => $post->meta_title,
                'meta_description' => $post->meta_description,
            ],
        ]);
    }

    public function update(Request $request, Post $post)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255',
            'excerpt' => 'nullable|string|max:1000',
            'content' => 'required|string',
            'cover_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:4096',
            'remove_cover_image' => 'boolean',
            'published_at' => 'nullable|date',
            'is_published' => 'boolean',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
        ]);

        $validated['slug'] = Post::generateUniqueSlug($validated['slug'] ?: $validated['title'], $post->id);
        $validated['is_published'] = (bool) ($validated['is_published'] ?? false);

        if ($validated['is_published'] && empty($validated['published_at']) && !$post->published_at) {
            $validated['published_at'] = now();
        }

        if (!empty($validated['remove_cover_image']) && $post->cover_image) {
            MediaStorageService::delete($post->cover_image);
            $validated['cover_image'] = null;
        } elseif ($request->hasFile('cover_image')) {
            if ($post->cover_image) {
                MediaStorageService::delete($post->cover_image);
            }
            $validated['cover_image'] = MediaStorageService::store(
                $request->file('cover_image'),
                'posts/covers',
                'cover'
            );
        } else {
            unset($validated['cover_image']);
        }
        unset($validated['remove_cover_image']);

        $post->update($validated);

        return redirect()->route('backoffice.posts.index')->with('success', 'อัปเดตบทความเรียบร้อยแล้ว');
    }

    /**
     * สลับสถานะ เผยแพร่ / แบบร่าง ในคลิกเดียว
     */
    public function togglePublish(Request $request, Post $post)
    {
        $newStatus = !$post->is_published;
        $post->update([
            'is_published' => $newStatus,
            'published_at' => ($newStatus && !$post->published_at) ? now() : $post->published_at,
        ]);

        return back()->with('success', $newStatus ? "เผยแพร่บทความ \"{$post->title}\" แล้ว" : "เปลี่ยนบทความ \"{$post->title}\" เป็นแบบร่างแล้ว");
    }

    public function destroy(Request $request, Post $post)
    {
        if ($post->cover_image) {
            MediaStorageService::delete($post->cover_image);
        }
        $post->delete();

        return redirect()->route('backoffice.posts.index')->with('success', 'ลบบทความเรียบร้อยแล้ว');
    }
}

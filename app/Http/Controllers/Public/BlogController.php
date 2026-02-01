<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\Request;
use Inertia\Inertia;

class BlogController extends Controller
{
    public function index(Request $request)
    {
        $posts = Post::query()
            ->where('is_published', true)
            ->where(function ($q) {
                $q->whereNull('published_at')
                    ->orWhere('published_at', '<=', now());
            })
            ->with('author:id,name')
            ->orderByDesc('published_at')
            ->orderByDesc('created_at')
            ->paginate(12)
            ->through(fn ($p) => [
                'id' => $p->id,
                'title' => $p->title,
                'slug' => $p->slug,
                'excerpt' => $p->excerpt,
                'cover_image_url' => $p->cover_image ? asset('storage/' . $p->cover_image) : null,
                'author_name' => $p->author?->name,
                'published_at' => $p->published_at?->format('d/m/Y'),
                'published_at_iso' => $p->published_at?->toIso8601String(),
            ]);

        return Inertia::render('Blog/Index', [
            'posts' => $posts,
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

        $post->increment('view_count');

        return Inertia::render('Blog/Show', [
            'post' => [
                'id' => $post->id,
                'title' => $post->title,
                'slug' => $post->slug,
                'excerpt' => $post->excerpt,
                'content' => $post->content,
                'cover_image_url' => $post->cover_image ? asset('storage/' . $post->cover_image) : null,
                'author_name' => $post->author?->name,
                'published_at' => $post->published_at?->format('d/m/Y'),
                'view_count' => $post->view_count + 1,
                'meta_title' => $post->meta_title ?: $post->title,
                'meta_description' => $post->meta_description ?: $post->excerpt,
            ],
        ]);
    }
}

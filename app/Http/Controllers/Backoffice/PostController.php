<?php

namespace App\Http\Controllers\Backoffice;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;

class PostController extends Controller
{
    public function index(Request $request)
    {
        if (!$request->user() || !$request->user()->isAdmin()) {
            abort(403, 'Only admin can access this page.');
        }

        $posts = Post::with('author:id,name')
            ->orderByDesc('created_at')
            ->get()
            ->map(fn ($p) => [
                'id' => $p->id,
                'title' => $p->title,
                'slug' => $p->slug,
                'excerpt' => Str::limit($p->excerpt, 80),
                'cover_image' => $p->cover_image ? asset('storage/' . $p->cover_image) : null,
                'author_name' => $p->author?->name,
                'published_at' => $p->published_at?->format('Y-m-d H:i'),
                'is_published' => $p->is_published,
                'view_count' => $p->view_count,
                'created_at' => $p->created_at->format('Y-m-d'),
            ]);

        return Inertia::render('Backoffice/Posts/Index', ['posts' => $posts]);
    }

    public function create(Request $request)
    {
        if (!$request->user() || !$request->user()->isAdmin()) {
            abort(403, 'Only admin can access this page.');
        }

        return Inertia::render('Backoffice/Posts/Create');
    }

    /**
     * อัปโหลดรูปแทรกในเนื้อหา (ใช้จาก Rich Text Editor)
     */
    public function uploadImage(Request $request)
    {
        if (!$request->user() || !$request->user()->isAdmin()) {
            abort(403);
        }

        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:4096',
        ]);

        $path = $request->file('image')->store('posts', 'public');

        return response()->json([
            'url' => asset('storage/' . $path),
        ]);
    }

    public function store(Request $request)
    {
        if (!$request->user() || !$request->user()->isAdmin()) {
            abort(403, 'Only admin can access this page.');
        }

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
        $validated['slug'] = $validated['slug'] ?? Str::slug($validated['title']);
        $validated['is_published'] = $validated['is_published'] ?? false;

        if ($request->hasFile('cover_image')) {
            $validated['cover_image'] = $request->file('cover_image')->store('posts', 'public');
        }

        Post::create($validated);

        return redirect()->route('backoffice.posts.index')->with('success', 'สร้างบทความเรียบร้อยแล้ว');
    }

    public function edit(Request $request, Post $post)
    {
        if (!$request->user() || !$request->user()->isAdmin()) {
            abort(403, 'Only admin can access this page.');
        }

        return Inertia::render('Backoffice/Posts/Edit', [
            'post' => [
                'id' => $post->id,
                'title' => $post->title,
                'slug' => $post->slug,
                'excerpt' => $post->excerpt,
                'content' => $post->content,
                'cover_image' => $post->cover_image ? asset('storage/' . $post->cover_image) : null,
                'cover_image_path' => $post->cover_image,
                'published_at' => $post->published_at?->format('Y-m-d\TH:i'),
                'is_published' => $post->is_published,
                'meta_title' => $post->meta_title,
                'meta_description' => $post->meta_description,
            ],
        ]);
    }

    public function update(Request $request, Post $post)
    {
        if (!$request->user() || !$request->user()->isAdmin()) {
            abort(403, 'Only admin can access this page.');
        }

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

        $validated['slug'] = $validated['slug'] ?? Str::slug($validated['title']);
        $validated['is_published'] = $validated['is_published'] ?? false;

        if (!empty($validated['remove_cover_image']) && $post->cover_image) {
            Storage::disk('public')->delete($post->cover_image);
            $validated['cover_image'] = null;
        } elseif ($request->hasFile('cover_image')) {
            if ($post->cover_image) {
                Storage::disk('public')->delete($post->cover_image);
            }
            $validated['cover_image'] = $request->file('cover_image')->store('posts', 'public');
        }
        unset($validated['remove_cover_image']);

        $post->update($validated);

        return redirect()->route('backoffice.posts.index')->with('success', 'อัปเดตบทความเรียบร้อยแล้ว');
    }

    public function destroy(Request $request, Post $post)
    {
        if (!$request->user() || !$request->user()->isAdmin()) {
            abort(403, 'Only admin can access this page.');
        }

        if ($post->cover_image) {
            Storage::disk('public')->delete($post->cover_image);
        }
        $post->delete();

        return redirect()->route('backoffice.posts.index')->with('success', 'ลบบทความเรียบร้อยแล้ว');
    }
}

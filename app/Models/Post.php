<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Post extends Model
{
    protected $fillable = [
        'title',
        'slug',
        'excerpt',
        'content',
        'cover_image',
        'author_id',
        'published_at',
        'is_published',
        'meta_title',
        'meta_description',
        'view_count',
        'sort_order',
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'is_published' => 'boolean',
        'view_count' => 'integer',
        'sort_order' => 'integer',
    ];

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    /**
     * ป้องกันการแปลงวันที่เป็น UTC ตอนส่ง JSON
     */
    protected function serializeDate(\DateTimeInterface $date): string
    {
        return \Carbon\Carbon::instance($date)->timezone(config('app.timezone', 'Asia/Bangkok'))->format('Y-m-d H:i:s');
    }

    /**
     * สร้าง Slug ที่รองรับทั้งภาษาไทยและภาษาอังกฤษ และไม่ซ้ำในฐานข้อมูล
     */
    public static function generateUniqueSlug(string $text, ?int $ignoreId = null): string
    {
        $slug = trim($text);
        // แทนที่ช่องว่างและอักขระพิเศษด้วยขีดกลาง (-) โดยเก็บตัวอักษรไทย สระ วรรณยุกต์ และตัวเลขไว้ครบ
        $slug = preg_replace('/[^\p{L}\p{M}\p{N}]+/u', '-', $slug) ?? '';
        $slug = trim($slug, '-');
        $slug = mb_strtolower($slug, 'UTF-8');

        if ($slug === '') {
            $slug = 'post-' . now()->format('YmdHis');
        }

        $slugBase = mb_substr($slug, 0, 220, 'UTF-8');
        $candidate = $slugBase;
        $counter = 2;

        while (
            static::where('slug', $candidate)
                ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
                ->exists()
        ) {
            $candidate = $slugBase . '-' . $counter;
            $counter++;
        }

        return $candidate;
    }

    public static function boot()
    {
        parent::boot();
        static::saving(function ($post) {
            if (!empty($post->content)) {
                $post->content = preg_replace('#https?://(localhost|127\.0\.0\.1)(:\d+)?/storage/#i', '/storage/', $post->content) ?? $post->content;
            }
            if (empty($post->slug)) {
                $post->slug = static::generateUniqueSlug($post->title ?: 'post', $post->id);
            }
            if (empty($post->excerpt) && !empty($post->content)) {
                $plain = trim(preg_replace('/\s+/u', ' ', strip_tags($post->content)));
                $post->excerpt = Str::limit($plain, 160);
            }
            if ($post->is_published && empty($post->published_at)) {
                $post->published_at = now();
            }
        });
    }

    public function getCoverImageUrlAttribute(): ?string
    {
        return \App\Services\MediaStorageService::url($this->cover_image);
    }
}

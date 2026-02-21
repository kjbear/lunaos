<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Doc extends Model
{
    protected $fillable = [
        'slug',
        'title',
        'section',
        'content',
        'order',
    ];

    protected $casts = [
        'order' => 'integer',
    ];

    public function scopeBySection($query, string $section)
    {
        return $query->where('section', $section);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('order')->orderBy('title');
    }

    /**
     * Search using FTS5
     */
    public static function search(string $query): \Illuminate\Database\Eloquent\Builder
    {
        return static::query()
            ->whereRaw("id IN (SELECT rowid FROM docs_fts WHERE docs_fts MATCH ?)", [$query]);
    }
}
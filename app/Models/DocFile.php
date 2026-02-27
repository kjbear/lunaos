<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class DocFile extends Model
{
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'collection_id',
        'category_id',
        'title',
        'slug',
        'file_path',
        'source_url',
        'content_hash',
        'word_count',
        'sort_order',
    ];

    protected $casts = [
        'word_count' => 'integer',
        'sort_order' => 'integer',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = (string) Str::uuid();
            }
            if (empty($model->slug)) {
                $model->slug = Str::slug($model->title);
            }
        });
    }

    public function collection()
    {
        return $this->belongsTo(DocCollection::class, 'collection_id');
    }

    public function category()
    {
        return $this->belongsTo(DocCategory::class, 'category_id');
    }

    public function getContent(): string
    {
        if (file_exists($this->file_path)) {
            return file_get_contents($this->file_path);
        }
        return '';
    }

    public function getProcessedContent(): string
    {
        $content = $this->getContent();
        
        // Remove YAML frontmatter
        $content = preg_replace('/^---\s*\n.*?\n---\s*\n/s', '', $content);
        
        return $content;
    }

    public function exists(): bool
    {
        return file_exists($this->file_path);
    }
}
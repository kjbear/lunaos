<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class DocCollection extends Model
{
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'source_url',
        'storage_path',
        'file_count',
        'last_synced_at',
    ];

    protected $casts = [
        'last_synced_at' => 'datetime',
        'file_count' => 'integer',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = (string) Str::uuid();
            }
            if (empty($model->slug)) {
                $model->slug = Str::slug($model->name);
            }
        });
    }

    public function categories()
    {
        return $this->hasMany(DocCategory::class, 'collection_id')->orderBy('sort_order')->orderBy('name');
    }

    public function rootCategories()
    {
        return $this->hasMany(DocCategory::class, 'collection_id')->whereNull('parent_id')->orderBy('sort_order')->orderBy('name');
    }

    public function files()
    {
        return $this->hasMany(DocFile::class, 'collection_id')->orderBy('sort_order')->orderBy('title');
    }

    public function updateFileCount(): void
    {
        $this->file_count = $this->files()->count();
        $this->save();
    }
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class DocCategory extends Model
{
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'collection_id',
        'parent_id',
        'name',
        'slug',
        'path',
        'sort_order',
    ];

    protected $casts = [
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
                $model->slug = Str::slug($model->name);
            }
        });
    }

    public function collection()
    {
        return $this->belongsTo(DocCollection::class, 'collection_id');
    }

    public function parent()
    {
        return $this->belongsTo(DocCategory::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(DocCategory::class, 'parent_id')->orderBy('sort_order')->orderBy('name');
    }

    public function files()
    {
        return $this->hasMany(DocFile::class, 'category_id')->orderBy('sort_order')->orderBy('title');
    }

    public function getFullPath(): string
    {
        if ($this->parent) {
            return $this->parent->getFullPath() . ' > ' . $this->name;
        }
        return $this->name;
    }
}
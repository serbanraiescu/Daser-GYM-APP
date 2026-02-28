<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Page extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'content',
        'meta_description',
        'schema_type',
        'faq_data',
        'is_active',
        'show_in_footer',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'show_in_footer' => 'boolean',
        'faq_data' => 'array',
    ];
}

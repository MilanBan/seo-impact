<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property string $locale
 * @property string $slug
 * @property string $title
 * @property string $headline
 * @property string $summary
 * @property string|null $seo_title
 * @property string|null $seo_description
 * @property bool $published
 */
class PageTranslation extends Model
{
    public $timestamps = false;

    protected $guarded = [];

    protected function casts(): array
    {
        return ['sections' => 'array', 'published' => 'boolean'];
    }
}

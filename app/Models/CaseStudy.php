<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property string $client_name
 * @property string|null $industry
 * @property array<string, mixed>|null $metrics
 * @property-read Collection<int, CaseStudyTranslation> $translations
 */
class CaseStudy extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return ['metrics' => 'array'];
    }

    /** @return HasMany<CaseStudyTranslation, $this> */
    public function translations(): HasMany
    {
        return $this->hasMany(CaseStudyTranslation::class);
    }
}

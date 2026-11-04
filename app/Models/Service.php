<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property string $key
 * @property string $icon
 * @property-read Collection<int, ServiceTranslation> $translations
 */
class Service extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return ['active' => 'boolean'];
    }

    /** @return HasMany<ServiceTranslation, $this> */
    public function translations(): HasMany
    {
        return $this->hasMany(ServiceTranslation::class);
    }
}

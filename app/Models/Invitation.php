<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $email
 * @property int $client_organization_id
 * @property Carbon $expires_at
 * @property Carbon|null $accepted_at
 * @property-read ClientOrganization $organization
 */
class Invitation extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return ['expires_at' => 'datetime', 'accepted_at' => 'datetime'];
    }

    /** @return BelongsTo<ClientOrganization, $this> */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(ClientOrganization::class, 'client_organization_id');
    }

    public function isUsable(): bool
    {
        return $this->accepted_at === null && $this->expires_at->isFuture();
    }
}

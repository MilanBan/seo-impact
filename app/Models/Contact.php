<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Contact extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return ['primary' => 'boolean'];
    }

    /** @return BelongsTo<ClientOrganization, $this> */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(ClientOrganization::class, 'client_organization_id');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $client_organization_id
 * @property string $name
 * @property string|null $description
 * @property string $status
 * @property Carbon|null $due_at
 * @property-read Collection<int, Task> $tasks
 * @property-read Collection<int, FinancialRecord> $financialRecords
 * @property-read Collection<int, ClientFile> $files
 */
class Project extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return ['starts_at' => 'date', 'due_at' => 'date'];
    }

    /** @return BelongsTo<ClientOrganization, $this> */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(ClientOrganization::class, 'client_organization_id');
    }

    /** @return BelongsToMany<Service, $this> */
    public function services(): BelongsToMany
    {
        return $this->belongsToMany(Service::class);
    }

    /** @return HasMany<Task, $this> */
    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    /** @return HasMany<FinancialRecord, $this> */
    public function financialRecords(): HasMany
    {
        return $this->hasMany(FinancialRecord::class);
    }

    /** @return HasMany<ClientFile, $this> */
    public function files(): HasMany
    {
        return $this->hasMany(ClientFile::class);
    }
}

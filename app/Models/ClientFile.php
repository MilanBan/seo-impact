<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property string $name
 * @property string $path
 * @property string $disk
 * @property string $mime_type
 * @property int $size
 * @property bool $client_visible
 * @property-read Project $project
 */
class ClientFile extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return ['client_visible' => 'boolean'];
    }

    /** @return BelongsTo<Project, $this> */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /** @return BelongsTo<User, $this> */
    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}

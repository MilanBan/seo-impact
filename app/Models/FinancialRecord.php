<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $project_id
 * @property string $document_number
 * @property string $currency
 * @property numeric-string $total
 * @property Carbon $issued_at
 * @property Carbon $due_at
 * @property numeric-string $paid_amount
 * @property numeric-string $outstanding_amount
 * @property string $payment_status
 * @property-read Collection<int, Payment> $payments
 */
class FinancialRecord extends Model
{
    protected $guarded = [];

    protected $appends = ['paid_amount', 'outstanding_amount', 'payment_status'];

    protected function casts(): array
    {
        return ['issued_at' => 'date', 'due_at' => 'date', 'total' => 'decimal:2'];
    }

    /** @return Attribute<numeric-string, never> */
    protected function paidAmount(): Attribute
    {
        return Attribute::get(fn () => number_format((float) $this->payments()->sum('amount'), 2, '.', ''));
    }

    /** @return Attribute<numeric-string, never> */
    protected function outstandingAmount(): Attribute
    {
        return Attribute::get(fn () => number_format(max(0, (float) $this->total - (float) $this->paid_amount), 2, '.', ''));
    }

    /** @return Attribute<string, never> */
    protected function paymentStatus(): Attribute
    {
        return Attribute::get(function (): string {
            if ((float) $this->outstanding_amount <= 0) {
                return 'paid';
            }
            if ((float) $this->paid_amount > 0) {
                return 'partial';
            }
            if ($this->due_at->isPast()) {
                return 'overdue';
            }

            return 'open';
        });
    }

    /** @return BelongsTo<Project, $this> */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /** @return HasMany<Payment, $this> */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }
}

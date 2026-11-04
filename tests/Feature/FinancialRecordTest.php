<?php

use App\Models\ClientOrganization;
use App\Models\FinancialRecord;
use App\Models\Project;

it('derives balances and status from payments', function () {
    $organization = ClientOrganization::create(['name' => 'Test', 'slug' => 'test']);
    $project = Project::create(['client_organization_id' => $organization->id, 'name' => 'Growth']);
    $record = FinancialRecord::create(['project_id' => $project->id, 'document_number' => 'INV-TEST-1', 'currency' => 'EUR', 'total' => 1000, 'issued_at' => now(), 'due_at' => now()->addWeek()]);
    expect($record->fresh()->outstanding_amount)->toBe('1000.00')->and($record->fresh()->payment_status)->toBe('open');
    $record->payments()->create(['amount' => 350, 'paid_at' => now()]);
    expect($record->fresh()->paid_amount)->toBe('350.00')->and($record->fresh()->outstanding_amount)->toBe('650.00')->and($record->fresh()->payment_status)->toBe('partial');
    $record->payments()->create(['amount' => 650, 'paid_at' => now()]);
    expect($record->fresh()->outstanding_amount)->toBe('0.00')->and($record->fresh()->payment_status)->toBe('paid');
});

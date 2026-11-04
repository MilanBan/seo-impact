<?php

use App\Mail\LeadConfirmation;
use App\Mail\LeadReceived;
use Illuminate\Support\Facades\Mail;

it('creates one lead and queues both emails', function () {
    Mail::fake();
    $this->post('/en/contact', [
        'name' => 'Ada Lovelace', 'email' => 'ada@example.com', 'company' => 'Analytical Engines',
        'service' => 'Technical SEO', 'message' => 'We need a measurable international organic growth programme.', 'website' => '',
    ])->assertRedirect();
    $this->assertDatabaseCount('leads', 1);
    $this->assertDatabaseHas('leads', ['email' => 'ada@example.com', 'locale' => 'en', 'status' => 'new']);
    Mail::assertQueued(LeadReceived::class, 1);
    Mail::assertQueued(LeadConfirmation::class, 1);
});

it('rejects bots caught by the honeypot', function () {
    $this->post('/en/contact', ['name' => 'Bot', 'email' => 'bot@example.com', 'message' => 'This is a sufficiently long spam message.', 'website' => 'spam.test'])->assertSessionHasErrors('website');
    $this->assertDatabaseCount('leads', 0);
});

<?php

use App\Models\ClientOrganization;
use App\Models\Invitation;

it('accepts a valid invitation exactly once', function () {
    $organization = ClientOrganization::create(['name' => 'Invited', 'slug' => 'invited']);
    $token = 'valid-test-token';
    $invitation = Invitation::create(['client_organization_id' => $organization->id, 'email' => 'new-client@example.com', 'token_hash' => hash('sha256', $token), 'expires_at' => now()->addHours(72)]);
    $this->post("/invitation/$token", ['name' => 'New Client', 'password' => 'a-very-secure-password', 'password_confirmation' => 'a-very-secure-password'])->assertRedirect('/en/portal');
    expect($invitation->fresh()->accepted_at)->not->toBeNull();
    $this->assertDatabaseHas('users', ['email' => 'new-client@example.com', 'client_organization_id' => $organization->id]);
    $this->post("/invitation/$token", [])->assertGone();
});

it('rejects expired invitations', function () {
    $organization = ClientOrganization::create(['name' => 'Expired', 'slug' => 'expired']);
    $token = 'expired-test-token';
    Invitation::create(['client_organization_id' => $organization->id, 'email' => 'late@example.com', 'token_hash' => hash('sha256', $token), 'expires_at' => now()->subMinute()]);
    $this->get("/invitation/$token")->assertGone();
});

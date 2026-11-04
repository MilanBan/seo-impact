<?php

use App\Enums\UserRole;
use App\Models\ClientFile;
use App\Models\ClientOrganization;
use App\Models\Project;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;

it('only exposes the authenticated clients own organization', function () {
    $a = ClientOrganization::create(['name' => 'Client A', 'slug' => 'client-a']);
    $b = ClientOrganization::create(['name' => 'Client B', 'slug' => 'client-b']);
    Project::create(['client_organization_id' => $a->id, 'name' => 'Project A']);
    Project::create(['client_organization_id' => $b->id, 'name' => 'Project B']);
    $user = User::factory()->create(['role' => UserRole::Client, 'client_organization_id' => $a->id, 'email_verified_at' => now()]);
    $this->actingAs($user)->get('/en/portal')->assertOk()->assertInertia(fn (Assert $page) => $page->component('portal/Dashboard')->has('projects', 1)->where('projects.0.name', 'Project A'));
});

it('denies direct downloads from another client', function () {
    Storage::fake('local');
    $a = ClientOrganization::create(['name' => 'Client A', 'slug' => 'a']);
    $b = ClientOrganization::create(['name' => 'Client B', 'slug' => 'b']);
    $project = Project::create(['client_organization_id' => $b->id, 'name' => 'Private B']);
    $file = ClientFile::create(['project_id' => $project->id, 'name' => 'secret.pdf', 'path' => 'secret.pdf', 'disk' => 'local', 'mime_type' => 'application/pdf', 'size' => 12, 'client_visible' => true]);
    $user = User::factory()->create(['role' => UserRole::Client, 'client_organization_id' => $a->id, 'email_verified_at' => now()]);
    $this->actingAs($user)->get("/en/portal/files/$file->id")->assertForbidden();
});

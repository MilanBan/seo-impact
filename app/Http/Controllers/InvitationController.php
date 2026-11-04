<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Models\Invitation;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;
use Inertia\Response;

class InvitationController extends Controller
{
    public function show(string $token): Response
    {
        $invitation = $this->resolve($token);
        $organization = $invitation->organization;

        return Inertia::render('auth/AcceptInvitation', [
            'token' => $token,
            'email' => $invitation->email,
            'organization' => $organization->name,
        ]);
    }

    public function accept(Request $request, string $token): RedirectResponse
    {
        $invitation = $this->resolve($token);
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        abort_if(User::query()->where('email', $invitation->email)->exists(), 422, 'This email already has an account.');

        $user = DB::transaction(function () use ($invitation, $validated) {
            $locked = Invitation::query()->lockForUpdate()->findOrFail($invitation->id);
            abort_unless($locked->isUsable(), 410, 'This invitation has expired or was already used.');

            $user = User::create([
                'name' => $validated['name'],
                'email' => $locked->email,
                'password' => Hash::make($validated['password']),
                'role' => UserRole::Client,
                'client_organization_id' => $locked->client_organization_id,
                'email_verified_at' => now(),
            ]);
            $locked->update(['accepted_at' => now()]);

            return $user;
        });

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('portal.'.($user->preferred_locale ?: 'en'));
    }

    private function resolve(string $token): Invitation
    {
        $invitation = Invitation::query()->with('organization')->where('token_hash', hash('sha256', $token))->firstOrFail();
        abort_unless($invitation->isUsable(), 410, 'This invitation has expired or was already used.');

        return $invitation;
    }
}

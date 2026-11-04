<x-mail::message>
# Your private client portal is ready

You have been invited to the SEO Impact portal for **{{ $invitation->organization->name }}**.
The secure invitation can be used once and expires in 72 hours.

<x-mail::button :url="route('invitation.show', $token)">
Activate account
</x-mail::button>

If you were not expecting this invitation, you can ignore this email.

SEO Impact
</x-mail::message>

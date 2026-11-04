<?php

namespace App\Filament\Resources\Invitations\Pages;

use App\Filament\Resources\Invitations\InvitationResource;
use App\Mail\ClientInvitation;
use App\Models\Invitation;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class CreateInvitation extends CreateRecord
{
    protected static string $resource = InvitationResource::class;

    protected string $plainToken;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $this->plainToken = Str::random(64);
        $data['token_hash'] = hash('sha256', $this->plainToken);
        $data['role'] = 'client';
        $data['expires_at'] = now()->addHours(72);

        return $data;
    }

    protected function afterCreate(): void
    {
        /** @var Invitation $invitation */
        $invitation = $this->record;
        $invitation->load('organization');
        Mail::to($invitation->email)->queue(new ClientInvitation($invitation, $this->plainToken));
    }
}

<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreLeadRequest;
use App\Mail\LeadConfirmation;
use App\Mail\LeadReceived;
use App\Models\Lead;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Mail;

class LeadController extends Controller
{
    public function store(StoreLeadRequest $request, string $locale): RedirectResponse
    {
        $lead = Lead::create($request->safe()->except('website') + ['locale' => $locale, 'source' => 'contact', 'status' => 'new']);
        Mail::to(config('mail.leads_to'))->queue(new LeadReceived($lead));
        Mail::to($lead->email)->queue(new LeadConfirmation($lead));

        return back()->with('success', config("site.content.$locale.form.success"));
    }
}

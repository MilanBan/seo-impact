<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreLeadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, string>> */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120'], 'email' => ['required', 'email:rfc', 'max:190'],
            'company' => ['nullable', 'string', 'max:160'], 'phone' => ['nullable', 'string', 'max:40'],
            'service' => ['nullable', 'string', 'max:100'], 'message' => ['required', 'string', 'min:20', 'max:5000'],
            'website' => ['nullable', 'size:0'], 'utm_source' => ['nullable', 'string', 'max:120'],
            'utm_medium' => ['nullable', 'string', 'max:120'], 'utm_campaign' => ['nullable', 'string', 'max:120'],
        ];
    }
}

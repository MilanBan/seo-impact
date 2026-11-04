<x-mail::message>
# New inquiry from {{ $lead->name }}

**Email:** {{ $lead->email }}  
**Company:** {{ $lead->company ?: '—' }}  
**Service:** {{ $lead->service ?: '—' }}  
**Language:** {{ strtoupper($lead->locale) }}

{{ $lead->message }}
</x-mail::message>

@component('mail::message')
# {{ $specialEvent->event_title }}

{{ $description }}

@component('mail::panel')
The detailed special event preview is attached as a PDF.
@endcomponent

@component('mail::table')
| Field | Details |
| :--- | :--- |
| Event Code | {{ $specialEvent->event_code }} |
| Event Type | {{ $specialEvent->eventType?->title ?? '-' }} |
| Academic Year | {{ $specialEvent->academicYear?->academic_year ?? '-' }} |
| Event Dates | {{ $specialEvent->event_start_date?->format('d M Y') ?? '-' }} to {{ $specialEvent->event_end_date?->format('d M Y') ?? '-' }} |
| Venue | {{ $specialEvent->venue ?: '-' }} |
| Status | {{ \App\Models\SpecialEvent::STATUSES[$specialEvent->status] ?? ucfirst($specialEvent->status) }} |
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent

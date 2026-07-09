<?php

namespace App\Mail;

use App\Models\SpecialEvent;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;

class SpecialEventDetailsMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        private readonly SpecialEvent $specialEvent,
        private readonly string $mailSubject,
        private readonly string $description,
    ) {
    }

    public function build(): self
    {
        $this->specialEvent->loadMissing([
            'eventType',
            'academicYear',
            'grades',
            'divisions.grade',
            'staffCoordinators',
            'teacherCoordinators',
            'timings',
            'attachments',
        ]);

        $pdfContent = Pdf::loadView('special-events.show-pdf', [
            'specialEvent' => $this->specialEvent,
        ])
            ->setPaper('a4')
            ->output();
        $filename = Str::slug($this->specialEvent->event_code.'-'.$this->specialEvent->event_title).'.pdf';

        return $this
            ->subject($this->mailSubject)
            ->markdown('emails.special-event-details', [
                'specialEvent' => $this->specialEvent,
                'description' => $this->description,
            ])
            ->attachData($pdfContent, $filename, ['mime' => 'application/pdf']);
    }
}

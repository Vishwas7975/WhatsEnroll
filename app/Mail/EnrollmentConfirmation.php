<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class EnrollmentConfirmation extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $studentName,
        public string $courseName,
        public string $username,
        public string $password,
        public string $portalUrl,
        public float  $amountPaid,
        public bool   $isNewUser = true
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Enrollment Confirmed — ' . $this->courseName . ' | ' . config('app.name', 'WhatsEnroll'),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.enrollment_confirmation',
            with: [
                'studentName' => $this->studentName,
                'courseName'  => $this->courseName,
                'username'    => $this->username,
                'password'    => $this->password,
                'portalUrl'   => $this->portalUrl,
                'amountPaid'  => $this->amountPaid,
                'isNewUser'   => $this->isNewUser,
            ],
        );
    }
}
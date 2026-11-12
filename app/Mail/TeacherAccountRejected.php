<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TeacherAccountRejected extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $rejectionReason;

    public function __construct(User $user, string $rejectionReason)
    {
        $this->user = $user;
        $this->rejectionReason = $rejectionReason;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Pendaftaran Akun Guru Ditolak',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.teacher.rejected',
        );
    }
}

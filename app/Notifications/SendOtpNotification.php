<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SendOtpNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $otp;

    public function __construct($otp) {
        $this->otp = $otp;
        $this->queue = 'send_otp';
    }

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject(trans('public.reset_pin_otp'))
            ->line($this->otp)
            ->line(trans('public.apply_otp'));
    }

    public function toArray($notifiable): array
    {
        return [];
    }
}

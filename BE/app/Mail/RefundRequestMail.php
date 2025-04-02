<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class RefundRequestMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public $refundRequest;
    public $order;
    public $statusMessage;
    public function __construct($refundRequest, $order, $statusMessage)
    {
        $this->refundRequest = $refundRequest;
        $this->order = $order;
        $this->statusMessage = $statusMessage;
    }
    public function build()
    {
        return $this->subject('Cập nhật yêu cầu hoàn hàng')
                    ->view('emails.refund_request');
    }
}

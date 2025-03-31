<?php

namespace App\Mail;

use Illuminate\Http\Request;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Http\Controllers\Api\VoucherApiController;

class OrderConfirmationMail extends Mailable
{
    use Queueable, SerializesModels;
    public $order;

    /**
     * Create a new message instance.
     */
    public function __construct($order)
    {
        $this->order = $order;
    }
    public function build()
    {
        return $this->subject('Xác nhận đơn hàng')
            ->view('emails.order_confirmation')
            ->with([
                'order' => $this->order
            ]);
    }
}

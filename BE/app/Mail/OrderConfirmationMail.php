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
    public $discountAmount;

    /**
     * Create a new message instance.
     */
    public function __construct($order, $discountAmount)
    {
        $this->order = $order;
        $this->discountAmount = $discountAmount;
    }
    public function build()
    {
        return $this->subject('Xác nhận đơn hàng')
            ->view('emails.order_confirmation')
            ->with([
                'order' => $this->order,
                'discountAmount' => $this->discountAmount
            ]);
    }
}

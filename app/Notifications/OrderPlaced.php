<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderPlaced extends Notification
{
    use Queueable;

    public function __construct(public readonly Order $order) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Order Placed: '.$this->order->order_number)
            ->greeting('Hello '.($notifiable->name ?? 'Customer').',')
            ->line('Your order has been placed successfully.')
            ->line('Order number: '.$this->order->order_number)
            ->line('Total amount: '.number_format((float) $this->order->total_amount, 2))
            ->line('We will notify you when your order status changes.');
    }

    /**
     * Get the database representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => 'order_placed',
            'order_id' => $this->order->id,
            'order_number' => $this->order->order_number,
            'total_amount' => (float) $this->order->total_amount,
            'status' => $this->order->status,
            'payment_method' => $this->order->payment_method,
            'message' => "Your order #{$this->order->order_number} has been placed successfully!",
        ];
    }
}

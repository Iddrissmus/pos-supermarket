<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Business;
use App\Models\SubscriptionPlan;

class SubscriptionInvoiceNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $business;
    public $plan;

    /**
     * Create a new notification instance.
     */
    public function __construct(Business $business, $plan)
    {
        $this->business = $business;
        $this->plan = $plan;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $url = route('subscription.payment.show', ['business' => $this->business->uuid ?? $this->business->id]);
        $amount = number_format($this->plan->price, 2);

        return (new MailMessage)
                    ->subject('Invoice for ' . $this->business->name . ' Subscription')
                    ->greeting('Hello ' . $notifiable->name . ',')
                    ->line('Your business account "' . $this->business->name . '" has been created on our platform.')
                    ->line('To activate your account, please pay the subscription fee for the ' . $this->plan->name . ' plan.')
                    ->line('Amount Due: GHS ' . $amount)
                    ->action('Pay Subscription', $url)
                    ->line('If you have any questions, please contact support.')
                    ->line('Thank you for using our application!');
    }
}

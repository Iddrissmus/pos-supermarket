<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Business;

class SubscriptionExpiring extends Notification implements ShouldQueue
{
    use Queueable;

    public $business;
    public $daysLeft;

    /**
     * Create a new notification instance.
     */
    public function __construct(Business $business, int $daysLeft)
    {
        $this->business = $business;
        $this->daysLeft = $daysLeft;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $url = route('login'); // Or specific renewal link

        return (new MailMessage)
                    ->subject('Subscription Expiry Warning - ' . $this->business->name)
                    ->greeting('Hello ' . $notifiable->name . ',')
                    ->line("Your subscription for {$this->business->name} will expire in {$this->daysLeft} days.")
                    ->line('Please renew your subscription to avoid service interruption.')
                    ->action('Renew Now', $url)
                    ->line('Thank you for using our application!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'business_id' => $this->business->id,
            'days_left' => $this->daysLeft,
            'expires_at' => $this->business->subscription_expires_at,
            'message' => "Your subscription expires in {$this->daysLeft} days."
        ];
    }
}

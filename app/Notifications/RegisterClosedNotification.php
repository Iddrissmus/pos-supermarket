<?php

namespace App\Notifications;

use App\Models\CashDrawerSession;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RegisterClosedNotification extends Notification
{
    use Queueable;

    public $session;
    public $summary;

    public function __construct(CashDrawerSession $session, array $summary)
    {
        $this->session = $session;
        $this->summary = $summary;
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $difference = $this->summary['difference'];
        $status = 'balanced';
        $color = 'green';
        $urgency = 'normal';

        if ($difference > 0) {
            $status = 'overage';
            $color = 'blue'; // Extra money is okay but weird
        } elseif ($difference < 0) {
            $status = 'shortage';
            $color = 'red';
            $urgency = 'critical';
        }

        $msg = "Drawer closed by {$this->session->user->name}. Total Cash: GHS " . number_format($this->summary['actual_amount'], 2);
        
        if ($status !== 'balanced') {
            $msg .= " (" . ucfirst($status) . ": GHS " . number_format(abs($difference), 2) . ")";
        }

        return [
            'type' => 'register_closed',
            'title' => 'Register Closed',
            'message' => $msg,
            'session_id' => $this->session->id,
            'branch_id' => $this->session->branch_id,
            'cashier_name' => $this->session->user->name,
            'actual_amount' => number_format($this->summary['actual_amount'], 2),
            'expected_amount' => number_format($this->summary['expected_amount'], 2),
            'difference' => number_format($difference, 2),
            'status' => $status,
            'icon' => 'fa-cash-register',
            'color' => $color,
            'urgency' => $urgency,
            // Assuming there isn't a dedicated show page for session yet, referencing dashboard or similar
            'action_url' => route('dashboard.manager'), 
        ];
    }
}

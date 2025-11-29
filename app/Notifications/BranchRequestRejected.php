<?php

namespace App\Notifications;

use App\Models\BranchRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BranchRequestRejected extends Notification
{
    use Queueable;

    protected $branchRequest;

    public function __construct(BranchRequest $branchRequest)
    {
        $this->branchRequest = $branchRequest;
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'branch_request_rejected',
            'title' => 'Branch Request Rejected',
            'message' => "Your branch request '{$this->branchRequest->branch_name}' has been rejected",
            'rejection_reason' => $this->branchRequest->rejection_reason,
            'action_url' => route('branches.create'),
        ];
    }
}

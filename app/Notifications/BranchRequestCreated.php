<?php

namespace App\Notifications;

use App\Models\BranchRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BranchRequestCreated extends Notification
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
            'type' => 'branch_request_created',
            'title' => 'New Branch Request',
            'message' => "Branch request '{$this->branchRequest->branch_name}' from {$this->branchRequest->requestedBy->name} needs approval",
            'branch_request_id' => $this->branchRequest->id,
            'business_name' => $this->branchRequest->business->name,
            'branch_name' => $this->branchRequest->branch_name,
            'location' => $this->branchRequest->location,
            'action_url' => route('superadmin.branch-requests.show', $this->branchRequest->id),
        ];
    }
}

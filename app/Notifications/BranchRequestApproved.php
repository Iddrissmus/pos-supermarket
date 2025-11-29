<?php

namespace App\Notifications;

use App\Models\BranchRequest;
use App\Models\Branch;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BranchRequestApproved extends Notification
{
    use Queueable;

    protected $branchRequest;
    protected $branch;

    public function __construct(BranchRequest $branchRequest, Branch $branch)
    {
        $this->branchRequest = $branchRequest;
        $this->branch = $branch;
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'branch_request_approved',
            'title' => 'Branch Request Approved',
            'message' => "Your branch request '{$this->branchRequest->branch_name}' has been approved and created",
            'branch_id' => $this->branch->id,
            'branch_name' => $this->branch->name,
            'action_url' => route('businesses.myMap'),
        ];
    }
}

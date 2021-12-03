<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class MembershipApproveNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $channels;
    protected $url;
    protected $view;
    protected $message;
    protected $subject;
    protected $approver;
    protected $member;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($member, $approver, $channels)
    {
        $this->member = $member;
        $this->approver = $approver;
        $this->channels = $channels;

        if($approver->is_approved){
            $this->subject = 'Membership Approval Approved, Company Name: ' . $member->company_name;
            $this->url = route('approval_request.show', $approver->approval_request_id);
            $this->view = 'emails.membership.approval_notification';;
            $this->message = 'Membership Approval Approved, Company Name: ' . $member->company_name;
        }
        elseif($approver->is_rejected) {
            $this->subject = 'Membership Approval Rejected, Company Name: ' . $member->company_name;
            $this->url = route('approval_request.show', $approver->approval_request_id);
            $this->view = 'emails.membership.approval_notification';;
            $this->message = 'Membership Approval Rejected, Company Name: ' . $member->company_name;
        }
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return $this->channels;
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject($this->subject)
            ->markdown($this->view, ['member' => $this->member, 'url' => $this->url, 'user' => $notifiable, 'message' => $this->message, 'approver' => $this->approver]);
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            'message' => $this->message,
            'url' => $this->url,
        ];
    }
}

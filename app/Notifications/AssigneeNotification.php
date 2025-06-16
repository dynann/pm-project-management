<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AssigneeNotification extends Notification 
{
    protected $issue;
    protected $assigner;

    public function __construct($issue, $assigner = null)
    {
        $this->issue = $issue;
        $this->assigner = $assigner;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

     public function toMail($notifiable)
    {
        $assignerName = $this->assigner ? $this->assigner->name : 'System';
        
        return (new MailMessage)
            ->subject('You have been assigned to an issue')
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('You have been assigned to the issue: ' . $this->issue->title)
            ->line('Assigned by: ' . $assignerName)
            ->line('Issue Description: ' . ($this->issue->description ?? 'No description provided'))
            ->action('View Issue', url('/issues/' . $this->issue->id))
            ->line('Thank you!');
    }
}
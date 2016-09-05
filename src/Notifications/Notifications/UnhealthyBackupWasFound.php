<?php

namespace Spatie\Backup\Notifications\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\SlackMessage;
use Spatie\Backup\Events\UnhealthyBackupWasFound as UnhealthyBackupWasFoundEvent;
use Spatie\Backup\Notifications\BaseNotification;

class UnhealthyBackupWasFound extends BaseNotification
{

    /** @var \Spatie\Backup\Events\UnhealthyBackupWasFound */
    protected $event;

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->success()
            ->subject("Party!!")
            ->line("Cleanup has failed");
    }

    public function toSlack($notifiable)
    {
        return (new SlackMessage)
            ->success()
            ->line("A backup was made of {$this->event->backupDestination->getBackupName()}! Hurray!");
    }

    public function setEvent(UnhealthyBackupWasFoundEvent $event)
    {
        $this->event = $event;

        return $this;
    }
}

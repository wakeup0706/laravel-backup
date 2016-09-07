<?php

namespace Spatie\Backup\Notifications\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\SlackAttachment;
use Illuminate\Notifications\Messages\SlackMessage;
use Spatie\Backup\Events\CleanupHasFailed as CleanupHasFailedEvent;
use Spatie\Backup\Notifications\BaseNotification;

class CleanupHasFailed extends BaseNotification
{

    /** @var \Spatie\Backup\Events\CleanupHasFailed */
    protected $event;

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $mailMessage = (new MailMessage)
            ->error()
            ->subject("Cleaning up the backups of `{$this->getApplicationName()}` failed.")
            ->line("An error occurred while cleaning up the backups of `{$this->getApplicationName()}`")
            ->line("Exception message: `{$this->event->exception->getMessage()}`")
            ->line("Exception trace: `" . $this->event->exception->getTraceAsString() . "`");

        $this->getBackupDestinationProperties()->each(function($value, $name) use ($mailMessage) {
            $mailMessage->line("{$name}: $value");
        });

        return $mailMessage;
    }

    public function toSlack($notifiable)
    {
        return (new SlackMessage)
            ->error()
            ->content("An error occurred while cleaning up the backups of `{$this->getApplicationName()}`")
            ->attachment(function (SlackAttachment $attachment) {
                $attachment
                    ->title('Exception message')
                    ->content($this->event->exception->getMessage());
            })
            ->attachment(function (SlackAttachment $attachment) {
                $attachment
                    ->title('Exception trace')
                    ->content($this->event->exception->getTraceAsString());
            })
            ->attachment(function(SlackAttachment $attachment) {
                $attachment->fields($this->getBackupDestinationProperties()->toArray());
            });

    }

    public function setEvent(CleanupHasFailedEvent $event)
    {
        $this->event = $event;

        return $this;
    }
}

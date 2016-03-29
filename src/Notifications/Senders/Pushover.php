<?php

namespace Spatie\Backup\Notifications\Senders;

use Illuminate\Contracts\Config\Repository;
use Spatie\Backup\Notifications\BaseSender;

class Pushover extends BaseSender
{
    /** @var array */
    protected $config;

    /**
     * @param Repository $config
     */
    public function __construct(Repository $config)
    {
        $this->config = $config->get('laravel-backup.notifications.pushover');
    }

    /**
     * Sends the message to the Pushover API
     * @return void
     */
    public function send()
    {
        curl_setopt_array($ch = curl_init(), [
            CURLOPT_URL => 'https://api.pushover.net/1/messages.json',
            CURLOPT_POSTFIELDS => [
                'token' => $this->config['token'],
                'user' => $this->config['user'],
                'title' => $this->subject,
                'message' => $this->message,
                'sound' => $this->getSound(),
            ],
            CURLOPT_SAFE_UPLOAD => true,
        ]);
        curl_exec($ch);
        curl_close($ch);
    }

    /**
     * Returns the proper sound for the notification type according to the config file
     * @return string The sound string to use
     */
    private function getSound()
    {
        $sounds = isset($this->config['sounds']) ? $this->config['sounds'] : ['success' => 'pushover', 'error' => 'siren'];

        $sound = $this->type === static::TYPE_SUCCESS ? $sounds['success'] : $sounds['error'];

        return $sound;
    }

}

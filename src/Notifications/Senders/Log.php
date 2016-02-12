<?php

namespace Spatie\Backup\Notifications\Sender;

use Illuminate\Contracts\Logging\Log as LogContract;
use Spatie\Backup\Notifications\BaseSender;

class Log extends BaseSender
{
    /** @var \Illuminate\Contracts\Logging\Log */
    protected $log;

    public function __construct(LogContract $log)
    {
        $this->log = $log;
    }

    public function send()
    {
        $method = ($this->type === self::TYPE_SUCCESS ? 'info' : 'error');

        $this->log->$method("{$this->subject}: {$this->message}");
    }
}

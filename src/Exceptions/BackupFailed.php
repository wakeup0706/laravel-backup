<?php

namespace Spatie\Backup\Exceptions;

use Exception;
use Spatie\Backup\BackupDestination\BackupDestination;

class BackupFailed extends Exception
{
    public ?BackupDestination $backupDestination = null;

    public static function from(Exception $exception): static {
        return new static($exception->getMessage(), $exception->getCode(), $exception->getPrevious());
    }

    public function destination(BackupDestination $backupDestination): static {
        $this->backupDestination = $backupDestination;

        return $this;
    }
}

<?php

namespace Spatie\Backup\Commands;

use Exception;
use Spatie\Backup\Traits\Retryable;
use Spatie\Backup\Events\CleanupHasFailed;
use Spatie\Backup\Tasks\Cleanup\CleanupJob;
use Spatie\Backup\Tasks\Cleanup\CleanupStrategy;
use Spatie\Backup\BackupDestination\BackupDestinationFactory;

class CleanupCommand extends BaseCommand
{
    use Retryable;

    /** @var string */
    protected $signature = 'backup:clean {--disable-notifications} {--tries=}';

    /** @var string */
    protected $description = 'Remove all backups older than specified number of days in config.';

    protected CleanupStrategy $strategy;

    public function __construct(CleanupStrategy $strategy)
    {
        parent::__construct();

        $this->strategy = $strategy;
    }

    public function handle()
    {
        consoleOutput()->comment($this->currentTry > 1 ? sprintf('Attempt n°%d...', $this->currentTry) : 'Starting cleanup...');

        $disableNotifications = $this->option('disable-notifications');

        $this->setTries('cleanup');

        try {
            $config = config('backup');

            $backupDestinations = BackupDestinationFactory::createFromArray($config['backup']);

            $cleanupJob = new CleanupJob($backupDestinations, $this->strategy, $disableNotifications);

            $cleanupJob->run();

            consoleOutput()->comment('Cleanup completed!');
        } catch (Exception $exception) {
            if ($this->shouldRetry()) {
                if ($this->hasRetryDelay('cleanup')) {
                    $this->sleepFor($this->getRetryDelay('cleanup'));
                }

                $this->currentTry += 1;
                return $this->handle();
            }

            if (! $disableNotifications) {
                event(new CleanupHasFailed($exception));
            }

            return 1;
        }
    }
}

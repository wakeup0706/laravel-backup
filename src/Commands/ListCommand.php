<?php

namespace Spatie\Backup\Commands;

use Illuminate\Support\Collection;
use Spatie\Backup\Helpers\Format;
use Spatie\Backup\Tasks\Monitor\BackupDestinationStatus;
use Spatie\Backup\Tasks\Monitor\BackupDestinationStatusFactory;

class ListCommand extends BaseCommand
{
    /** @var string */
    protected $signature = 'backup:list';

    /** @var string */
    protected $description = 'Display a list of all backups.';

    public function handle()
    {
        $statuses = BackupDestinationStatusFactory::createForMonitorConfig(config('laravel-backup.monitorBackups'));

        $this->displayOverview($statuses);

        $this->displayConnectionErrors($statuses);
    }

    protected function displayOverview(Collection $backupDestinationStatuses)
    {
        $headers = ['Name', 'Disk', 'Reachable', 'Healthy', '# of backups', 'Newest backup', 'Used storage'];

        $rows = $backupDestinationStatuses->map(function (BackupDestinationStatus $backupDestinationStatus) {
            return $this->convertToRow($backupDestinationStatus);
        });

        $this->table($headers, $rows);
    }

    public function convertToRow(BackupDestinationStatus $backupDestinationStatus): array
    {
        $row = [
            $backupDestinationStatus->getBackupName(),
            $backupDestinationStatus->getDiskName(),
            Format::getEmoji($backupDestinationStatus->isReachable()),
            Format::getEmoji($backupDestinationStatus->isHealthy()),
            'amount' => $backupDestinationStatus->getAmountOfBackups(),
            'newest' => $backupDestinationStatus->getDateOfNewestBackup()
                ? Format::ageInDays($backupDestinationStatus->getDateOfNewestBackup())
                : 'No backups present',
            'usedStorage' => $backupDestinationStatus->getHumanReadableUsedStorage(),
        ];

        if (! $backupDestinationStatus->isReachable()) {
            foreach (['amount', 'newest', 'usedStorage'] as $propertyName) {
                $row[$propertyName] = '/';
            }
        }

        $row = $this->applyStylingToRow($row, $backupDestinationStatus);

        return $row;
    }

    protected function applyStylingToRow(array $row, BackupDestinationStatus $backupDestinationStatus): array
    {
        if ($backupDestinationStatus->newestBackupIsToolOld() || (! $backupDestinationStatus->getDateOfNewestBackup())) {
            $row['newest'] = "<error>{$row['newest']}</error>";
        }

        if ($backupDestinationStatus->backupUsesTooMuchStorage()) {
            $row['usedStorage'] = "<error>{$row['usedStorage']} </error>";
        }

        return $row;
    }

    protected function displayConnectionErrors(Collection $backupDestinationStatuses)
    {
        $unreachableBackupDestinationStatuses = $backupDestinationStatuses
            ->filter(function (BackupDestinationStatus $backupDestinationStatus) {
                return ! $backupDestinationStatus->isReachable();
            });

        if ($unreachableBackupDestinationStatuses->isEmpty()) {
            return;
        }

        $this->warn('');
        $this->warn('Unreachable backup destinations');
        $this->warn('-------------------------------');

        $unreachableBackupDestinationStatuses->each(function (BackupDestinationStatus $backupStatus) {
            $this->warn("Could not reach backups for {$backupStatus->getBackupName()} on disk {$backupStatus->getFilesystemName()} because:");
            $this->warn($backupStatus->getConnectionError()->getMessage());
            $this->warn('');
        });
    }
}

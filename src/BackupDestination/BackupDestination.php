<?php

namespace Spatie\Backup\BackupDestination;

use Illuminate\Contracts\Filesystem\Factory;
use Illuminate\Contracts\Filesystem\Filesystem;
use Exception;

class BackupDestination
{
    /** @var \Illuminate\Contracts\Filesystem\Filesystem */
    protected $disk;

    /** @var string */
    protected $backupDirectory;

    /** @var Exception */
    protected $connectionError;

    /**
     * BackupDestination constructor.
     *
     * @param \Illuminate\Contracts\Filesystem\Filesystem $disk
     * @param string                                      $backupName
     */
    public function __construct(Filesystem $disk, $backupName)
    {
        $this->disk = $disk;

        $this->backupName = preg_replace('/[^a-zA-Z0-9.]/', '-', $backupName);
    }

    /**
     * @return string
     */
    public function getFilesystemType()
    {
        $adapterClass = get_class($this->disk->getDriver()->getAdapter());

        $filesystemType = last(explode('\\', $adapterClass));

        return strtolower($filesystemType);
    }

    /**
     * @param string $filesystemName
     * @param string $backupName
     *
     * @return \Spatie\Backup\BackupDestination\BackupDestination
     */
    public static function create($filesystemName, $backupName)
    {
        $disk = app(Factory::class)->disk($filesystemName);

        return new static($disk, $backupName);
    }

    /**
     * @param string $file
     */
    public function write($file)
    {
        $destination = $this->backupName.'/'.pathinfo($file, PATHINFO_BASENAME);

        $handle = fopen($file, 'r+');

        $this->disk->getDriver()->writeStream($destination, $handle);
    }

    /**
     * @return string
     */
    public function getBackupName()
    {
        return $this->backupName;
    }

    /**
     * @return \Spatie\Backup\BackupDestination\BackupCollection
     */
    public function getBackups()
    {
        $files = $this->isReachable() ? $this->disk->allFiles($this->backupName) : [];

        return BackupCollection::createFromFiles(
            $this->disk,
            $files
        );
    }

    /**
     * @return \Exception
     */
    public function getConnectionError() : Exception
    {
        return $this->connectionError;
    }

    public function isReachable() : bool
    {
        try {
            $this->disk->allFiles($this->backupName);

            return true;
        } catch (Exception $exception) {
            $this->connectionError = $exception;

            return false;
        }
    }

    /*
     * Return the used storage in bytes
     */
    public function getUsedStorage() : int
    {
        return $this->getBackups()->getSize();
    }

    /**
     * @return \Spatie\Backup\BackupDestination\Backup|null
     */
    public function getNewestBackup()
    {
        return $this->getBackups()->getNewestBackup();
    }

    /**
     * @param \Carbon\Carbon $date
     *
     * @return bool
     */
    public function isNewestBackupOlderThan($date)
    {
        $newestBackup = $this->getNewestBackup();

        if (is_null($newestBackup)) {
            return true;
        }

        return $newestBackup->getDate()->gt($date);
    }
}

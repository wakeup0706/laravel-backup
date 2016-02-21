<?php

namespace Spatie\Backup\BackupDestination;

use Carbon\Carbon;
use Illuminate\Contracts\Filesystem\Filesystem;

class Backup
{
    /** @var \Spatie\Backup\BackupDestination\Disk */
    protected $disk;

    /** @var string */
    protected $path;

    /**
     * @param \Illuminate\Contracts\Filesystem\Filesystem $disk
     * @param string                                      $path
     */
    public function __construct(Filesystem $disk, $path)
    {
        $this->disk = $disk;
        $this->path = $path;
    }

    /**
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @return bool
     */
    public function exists()
    {
        return $this->disk->exists($this->path);
    }

    /**
     * @return \Carbon\Carbon
     */
    public function getDate()
    {
        return Carbon::createFromTimestamp($this->disk->lastModified($this->path));
    }

    /**
     * Get the size in bytes.
     *
     * @return int
     */
    public function getSize()
    {
        if (!$this->exists()) {
            return 0;
        }

        return $this->disk->size($this->path);
    }

    public function delete()
    {
        $this->disk->delete($this->path);
        consoleOutput()->info("Deleted backup {$this->path}");
    }
}

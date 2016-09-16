<?php

namespace Spatie\Backup\Test\Integration\Events;

use Illuminate\Support\Facades\Artisan;
use Spatie\Backup\Events\BackupHasFailed;
use Spatie\Backup\Test\Integration\TestCase;

class BackupHasFailedTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();
    }

    /** @test */
    public function it_will_fire_an_event_when_a_backup_has_failed()
    {
        $this->app['config']->set('laravel-backup.backup.destination.disks', ['ftp']);

        $this->expectsEvent(BackupHasFailed::class);

        Artisan::call('backup:run', ['--only-files' => true]);
    }

    /** @test */
    public function it_will_fire_a_backup_failed_event_when_there_are_no_files_or_databases_to_be_backed_up()
    {
        $this->app['config']->set('laravel-backup.backup.source.files.include', []);
        $this->app['config']->set('laravel-backup.backup.source.databases', []);

        $this->expectsEvent(BackupHasFailed::class);

        Artisan::call('backup:run');
    }
}

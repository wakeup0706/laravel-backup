<?php

namespace Spatie\Backup\Tests\Events;

use Spatie\Backup\Tests\TestCase;
use Illuminate\Support\Facades\Event;
use Spatie\Backup\Events\BackupZipWasCreated;

class BackupZipWasCreatedTest extends TestCase
{
    /** @test */
    public function it_will_fire_a_backup_zip_was_created_event_when_the_zip_was_created()
    {
        Event::fake();

        $this->artisan('backup:run', ['--only-files' => true]);

        Event::assertDispatched(BackupZipWasCreated::class);
    }
}

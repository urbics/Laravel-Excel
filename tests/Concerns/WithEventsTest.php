<?php

namespace Urbics\Laraexcel\Tests\Concerns;

use Urbics\Laraexcel\Sheet;
use Urbics\Laraexcel\Writer;
use Urbics\Laraexcel\Tests\TestCase;
use Urbics\Laraexcel\Events\AfterSheet;
use Urbics\Laraexcel\Events\BeforeSheet;
use Urbics\Laraexcel\Events\BeforeExport;
use Urbics\Laraexcel\Events\BeforeWriting;
use Urbics\Laraexcel\Tests\Data\Stubs\ExportWithEvents;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Urbics\Laraexcel\Tests\Data\Stubs\BeforeExportListener;

class WithEventsTest extends TestCase
{
    /**
     * @test
     */
    public function events_get_called()
    {
        $event = new ExportWithEvents();

        $eventsTriggered = 0;

        $event->beforeExport = function ($event) use (&$eventsTriggered) {
            $this->assertInstanceOf(BeforeExport::class, $event);
            $this->assertInstanceOf(Writer::class, $event->getWriter());
            $eventsTriggered++;
        };

        $event->beforeWriting = function ($event) use (&$eventsTriggered) {
            $this->assertInstanceOf(BeforeWriting::class, $event);
            $this->assertInstanceOf(Writer::class, $event->getWriter());
            $eventsTriggered++;
        };

        $event->beforeSheet = function ($event) use (&$eventsTriggered) {
            $this->assertInstanceOf(BeforeSheet::class, $event);
            $this->assertInstanceOf(Sheet::class, $event->getSheet());
            $eventsTriggered++;
        };

        $event->afterSheet = function ($event) use (&$eventsTriggered) {
            $this->assertInstanceOf(AfterSheet::class, $event);
            $this->assertInstanceOf(Sheet::class, $event->getSheet());
            $eventsTriggered++;
        };

        $this->assertInstanceOf(BinaryFileResponse::class, $event->download('filename.xlsx'));
        $this->assertEquals(4, $eventsTriggered);
    }

    /**
     * @test
     */
    public function can_have_invokable_class_as_listener()
    {
        $event = new ExportWithEvents();

        $event->beforeExport = new BeforeExportListener(function ($event) {
            $this->assertInstanceOf(BeforeExport::class, $event);
            $this->assertInstanceOf(Writer::class, $event->getWriter());
        });

        $this->assertInstanceOf(BinaryFileResponse::class, $event->download('filename.xlsx'));
    }
}

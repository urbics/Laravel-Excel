<?php

namespace Urbics\Laraexcel\Tests\Data\Stubs;

use Urbics\Laraexcel\Writer;
use Illuminate\Support\Collection;
use Urbics\Laraexcel\Tests\TestCase;
use Urbics\Laraexcel\Concerns\WithTitle;
use Urbics\Laraexcel\Concerns\Exportable;
use Urbics\Laraexcel\Concerns\WithEvents;
use Urbics\Laraexcel\Events\BeforeWriting;
use Urbics\Laraexcel\Concerns\FromCollection;
use Urbics\Laraexcel\Concerns\ShouldAutoSize;
use Urbics\Laraexcel\Concerns\RegistersEventListeners;

class SheetWith100Rows implements FromCollection, WithTitle, ShouldAutoSize, WithEvents
{
    use Exportable, RegistersEventListeners;

    /**
     * @var string
     */
    private $title;

    /**
     * @param string $title
     */
    public function __construct(string $title)
    {
        $this->title = $title;
    }

    /**
     * @return Collection
     */
    public function collection()
    {
        $collection = new Collection;
        for ($i = 0; $i < 100; $i++) {
            $row = new Collection();
            for ($j = 0; $j < 5; $j++) {
                $row[] = $this->title() . '-' . $i . '-' . $j;
            }

            $collection->push($row);
        }

        return $collection;
    }

    /**
     * @return string
     */
    public function title(): string
    {
        return $this->title;
    }

    /**
     * @param BeforeWriting $event
     */
    public static function beforeWriting(BeforeWriting $event)
    {
        TestCase::assertInstanceOf(Writer::class, $event->writer);
    }
}

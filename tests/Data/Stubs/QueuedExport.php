<?php

namespace Urbics\Laraexcel\Tests\Data\Stubs;

use Urbics\Laraexcel\Concerns\Exportable;
use Urbics\Laraexcel\Concerns\WithMultipleSheets;

class QueuedExport implements WithMultipleSheets
{
    use Exportable;

    /**
     * @return SheetWith100Rows[]
     */
    public function sheets(): array
    {
        return [
            new SheetWith100Rows('A'),
            new SheetWith100Rows('B'),
            new SheetWith100Rows('C'),
        ];
    }
}

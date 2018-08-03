<?php

namespace Urbics\Laraexcel\Tests\Data\Stubs;

use Urbics\Laraexcel\Concerns\WithTitle;
use Urbics\Laraexcel\Concerns\Exportable;

class WithTitleExport implements WithTitle
{
    use Exportable;

    /**
     * @return string
     */
    public function title(): string
    {
        return 'given-title';
    }
}

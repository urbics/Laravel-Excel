<?php

namespace Urbics\Laraexcel\Events;

use Urbics\Laraexcel\Writer;

class BeforeExport
{
    /**
     * @var Writer
     */
    public $writer;

    /**
     * @param Writer $writer
     */
    public function __construct(Writer $writer)
    {
        $this->writer = $writer;
    }

    /**
     * @return Writer
     */
    public function getWriter(): Writer
    {
        return $this->writer;
    }
}

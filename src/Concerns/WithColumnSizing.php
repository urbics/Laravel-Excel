<?php

namespace Urbics\Laraexcel\Concerns;

interface WithColumnSizing
{
    /**
     * @return array
     */
    public function columnWidths(): array;
}

<?php

namespace Urbics\Laraexcel\Mixins;

use Illuminate\Support\Collection;
use Urbics\Laraexcel\Concerns\Exportable;
use Urbics\Laraexcel\Concerns\WithHeadings;
use Urbics\Laraexcel\Concerns\FromCollection;

class DownloadCollection
{
    /**
     * @return callable
     */
    public function downloadExcel()
    {
        return function (string $fileName, string $writerType = null, $withHeadings = false) {
            $export = new class($this, $withHeadings) implements FromCollection, WithHeadings {
                use Exportable;

                /**
                 * @var bool
                 */
                private $withHeadings;

                /**
                 * @var Collection
                 */
                private $collection;

                /**
                 * @param Collection $collection
                 * @param bool       $withHeading
                 */
                public function __construct(Collection $collection, bool $withHeading = false)
                {
                    $this->collection   = $collection->toBase();
                    $this->withHeadings = $withHeading;
                }

                /**
                 * @return Collection
                 */
                public function collection()
                {
                    return $this->collection;
                }

                /**
                 * @return array
                 */
                public function headings(): array
                {
                    return $this->withHeadings ? $this->collection->collapse()->keys()->all() : [];
                }
            };

            return $export->download($fileName, $writerType);
        };
    }
}

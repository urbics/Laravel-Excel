<?php

namespace Urbics\Laraexcel\Tests\Data\Stubs;

use Urbics\Laraexcel\Events\AfterSheet;
use Urbics\Laraexcel\Events\BeforeSheet;
use Urbics\Laraexcel\Concerns\Exportable;
use Urbics\Laraexcel\Concerns\WithEvents;
use Urbics\Laraexcel\Events\BeforeExport;
use Urbics\Laraexcel\Events\BeforeWriting;

class ExportWithEvents implements WithEvents
{
    use Exportable;

    /**
     * @var callable
     */
    public $beforeExport;

    /**
     * @var callable
     */
    public $beforeWriting;

    /**
     * @var callable
     */
    public $beforeSheet;

    /**
     * @var callable
     */
    public $afterSheet;

    /**
     * @return array
     */
    public function registerEvents(): array
    {
        return [
            BeforeExport::class  => $this->beforeExport ?? function () {
            },
            BeforeWriting::class => $this->beforeWriting ?? function () {
            },
            BeforeSheet::class   => $this->beforeSheet ?? function () {
            },
            AfterSheet::class    => $this->afterSheet ?? function () {
            },
        ];
    }
}

<?php

namespace Urbics\Laraexcel\Tests\Data\Stubs;

use Illuminate\Database\Query\Builder;
use Urbics\Laraexcel\Concerns\FromQuery;
use Urbics\Laraexcel\Events\BeforeSheet;
use Urbics\Laraexcel\Concerns\Exportable;
use Urbics\Laraexcel\Concerns\WithEvents;
use Urbics\Laraexcel\Concerns\WithMapping;
use Urbics\Laraexcel\Tests\Data\Stubs\Database\User;

class FromUsersQueryExport implements FromQuery, WithMapping, WithEvents
{
    use Exportable;

    /**
     * @return Builder
     */
    public function query()
    {
        return User::query();
    }

    /**
     * @param User $row
     *
     * @return array
     */
    public function map($row): array
    {
        return $row->toArray();
    }

    /**
     * @return array
     */
    public function registerEvents(): array
    {
        return [
            BeforeSheet::class   => function (BeforeSheet $event) {
                $event->sheet->chunkSize(10);
            },
        ];
    }
}

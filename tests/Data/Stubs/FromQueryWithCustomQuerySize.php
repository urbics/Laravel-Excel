<?php

namespace Urbics\Laraexcel\Tests\Data\Stubs;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Query\Builder;
use Urbics\Laraexcel\Concerns\FromQuery;
use Urbics\Laraexcel\Concerns\Exportable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Urbics\Laraexcel\Concerns\WithMapping;
use Urbics\Laraexcel\Concerns\WithCustomQuerySize;
use Urbics\Laraexcel\Tests\Data\Stubs\Database\Group;

class FromQueryWithCustomQuerySize implements FromQuery, WithCustomQuerySize, WithMapping, ShouldQueue
{
    use Exportable;

    /**
     * @return Builder
     */
    public function query()
    {
        $query = Group::with('users')
            ->join('group_user', 'groups.id', '=', 'group_user.group_id')
            ->select('groups.*', DB::raw('count(group_user.user_id) as number_of_users'))
            ->groupBy('groups.id')
            ->orderBy('number_of_users');

        return $query;
    }

    /**
     * @return int
     */
    public function querySize(): int
    {
        return Group::has('users')->count();
    }

    /**
     * @param Group $row
     *
     * @return array
     */
    public function map($row): array
    {
        return [
            $row->id,
            $row->name,
            $row->number_of_users,
        ];
    }
}

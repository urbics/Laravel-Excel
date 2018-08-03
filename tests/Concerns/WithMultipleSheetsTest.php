<?php

namespace Urbics\Laraexcel\Tests\Concerns;

use Illuminate\Support\Collection;
use Urbics\Laraexcel\Tests\TestCase;
use Urbics\Laraexcel\Concerns\Exportable;
use Urbics\Laraexcel\Concerns\WithMultipleSheets;
use Urbics\Laraexcel\Tests\Data\Stubs\Database\User;
use Urbics\Laraexcel\Tests\Data\Stubs\SheetWith100Rows;
use Urbics\Laraexcel\Tests\Data\Stubs\SheetForUsersFromView;

class WithMultipleSheetsTest extends TestCase
{
    /**
     * Setup the test environment.
     */
    protected function setUp()
    {
        parent::setUp();

        $this->withFactories(__DIR__ . '/../Data/Stubs/Database/Factories');
    }

    /**
     * @test
     */
    public function can_export_with_multiple_sheets_using_collections()
    {
        $export = new class implements WithMultipleSheets {
            use Exportable;

            /**
             * @return SheetWith100Rows[]
             */
            public function sheets() : array
            {
                return [
                    new SheetWith100Rows('A'),
                    new SheetWith100Rows('B'),
                    new SheetWith100Rows('C'),
                ];
            }
        };

        $export->store('from-view.xlsx');

        $this->assertCount(100, $this->readAsArray(__DIR__ . '/../Data/Disks/Local/from-view.xlsx', 'Xlsx', 0));
        $this->assertCount(100, $this->readAsArray(__DIR__ . '/../Data/Disks/Local/from-view.xlsx', 'Xlsx', 1));
        $this->assertCount(100, $this->readAsArray(__DIR__ . '/../Data/Disks/Local/from-view.xlsx', 'Xlsx', 2));
    }

    /**
     * @test
     */
    public function can_export_multiple_sheets_from_view()
    {
        /** @var Collection|User[] $users */
        $users = factory(User::class)->times(300)->make();

        $export = new class($users) implements WithMultipleSheets {
            use Exportable;

            /**
             * @var Collection
             */
            protected $users;

            /**
             * @param Collection $users
             */
            public function __construct(Collection $users)
            {
                $this->users = $users;
            }

            /**
             * @return SheetForUsersFromView[]
             */
            public function sheets() : array
            {
                return [
                    new SheetForUsersFromView($this->users->forPage(1, 100)),
                    new SheetForUsersFromView($this->users->forPage(2, 100)),
                    new SheetForUsersFromView($this->users->forPage(3, 100)),
                ];
            }
        };

        $export->store('from-view.xlsx');

        $this->assertCount(101, $this->readAsArray(__DIR__ . '/../Data/Disks/Local/from-view.xlsx', 'Xlsx', 0));
        $this->assertCount(101, $this->readAsArray(__DIR__ . '/../Data/Disks/Local/from-view.xlsx', 'Xlsx', 1));
        $this->assertCount(101, $this->readAsArray(__DIR__ . '/../Data/Disks/Local/from-view.xlsx', 'Xlsx', 2));
    }
}

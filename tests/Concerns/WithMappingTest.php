<?php

namespace Urbics\Laraexcel\Tests\Concerns;

use Urbics\Laraexcel\Tests\TestCase;
use Urbics\Laraexcel\Tests\Data\Stubs\WithMappingExport;

class WithMappingTest extends TestCase
{
    /**
     * @test
     */
    public function can_export_with_heading()
    {
        $export = new WithMappingExport();

        $response = $export->store('with-mapping-store.xlsx');

        $this->assertTrue($response);

        $actual = $this->readAsArray(__DIR__ . '/../Data/Disks/Local/with-mapping-store.xlsx', 'Xlsx');

        $expected = [
            [
                'mapped-A1',
                'mapped-B1',
                'mapped-C1',
            ],
            [
                'mapped-A2',
                'mapped-B2',
                'mapped-C2',
            ],
        ];

        $this->assertEquals($expected, $actual);
    }
}

<?php

namespace Urbics\Laraexcel\Tests\Concerns;

use Illuminate\Http\Request;
use Urbics\Laraexcel\Tests\TestCase;
use Urbics\Laraexcel\Concerns\Exportable;
use Illuminate\Contracts\Support\Responsable;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ExportableTest extends TestCase
{
    /**
     * @test
     * @expectedException \Urbics\Laraexcel\Exceptions\NoFilenameGivenException
     * @expectedExceptionMessage A filename needs to be passed in order to download the export
     */
    public function needs_to_have_a_file_name_when_downloading()
    {
        $export = new class {
            use Exportable;
        };

        $export->download();
    }

    /**
     * @test
     * @expectedException \Urbics\Laraexcel\Exceptions\NoFilePathGivenException
     * @expectedExceptionMessage A filepath needs to be passed in order to store the export
     */
    public function needs_to_have_a_file_name_when_storing()
    {
        $export = new class {
            use Exportable;
        };

        $export->store();
    }

    /**
     * @test
     * @expectedException \Urbics\Laraexcel\Exceptions\NoFilePathGivenException
     * @expectedExceptionMessage A filepath needs to be passed in order to store the export
     */
    public function needs_to_have_a_file_name_when_queuing()
    {
        $export = new class {
            use Exportable;
        };

        $export->queue();
    }

    /**
     * @test
     * @expectedException \Urbics\Laraexcel\Exceptions\NoFilenameGivenException
     * @expectedExceptionMessage A filename needs to be passed in order to download the export
     */
    public function responsable_needs_to_have_file_name_configured_inside_the_export()
    {
        $export = new class implements Responsable {
            use Exportable;
        };

        $export->toResponse(new Request());
    }

    /**
     * @test
     */
    public function is_responsable()
    {
        $export = new class implements Responsable {
            use Exportable;

            protected $fileName = 'export.xlsx';
        };

        $this->assertInstanceOf(Responsable::class, $export);

        $response = $export->toResponse(new Request());

        $this->assertInstanceOf(BinaryFileResponse::class, $response);
    }
}

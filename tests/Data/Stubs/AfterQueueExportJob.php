<?php

namespace Urbics\Laraexcel\Tests\Data\Stubs;

use Illuminate\Bus\Queueable;
use Urbics\Laraexcel\Tests\TestCase;
use Illuminate\Contracts\Queue\ShouldQueue;

class AfterQueueExportJob implements ShouldQueue
{
    use Queueable;

    /**
     * @var string
     */
    private $filePath;

    /**
     * @param string $filePath
     */
    public function __construct(string $filePath)
    {
        $this->filePath = $filePath;
    }

    public function handle()
    {
        TestCase::assertFileExists($this->filePath);
    }
}

<?php

namespace Urbics\Laraexcel;

use PhpOffice\PhpSpreadsheet\IOFactory;
use Urbics\Laraexcel\Concerns\FromView;
use Urbics\Laraexcel\Events\AfterSheet;
use Urbics\Laraexcel\Concerns\FromQuery;
use Urbics\Laraexcel\Concerns\WithTitle;
use Urbics\Laraexcel\Events\BeforeSheet;
use PhpOffice\PhpSpreadsheet\Reader\Html;
use Urbics\Laraexcel\Concerns\WithEvents;
use Illuminate\Contracts\Support\Arrayable;
use Urbics\Laraexcel\Concerns\WithMapping;
use Urbics\Laraexcel\Concerns\WithHeadings;
use Urbics\Laraexcel\Concerns\FromCollection;
use Urbics\Laraexcel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Urbics\Laraexcel\Concerns\WithRangeStyling;
use Urbics\Laraexcel\Concerns\WithColumnFormatting;
use Urbics\Laraexcel\Concerns\WithColumnSizing;
use Urbics\Laraexcel\Concerns\WithStrictNullComparison;
use Urbics\Laraexcel\Exceptions\ConcernConflictException;

class Sheet
{
    use DelegatedMacroable, HasEventBus;

    /**
     * @var int
     */
    protected $chunkSize;

    /**
     * @var string
     */
    protected $tmpPath;

    /**
     * @var Worksheet
     */
    private $worksheet;

    /**
     * @param Worksheet $worksheet
     */
    public function __construct(Worksheet $worksheet)
    {
        $this->worksheet = $worksheet;
        $this->chunkSize = config('excel.exports.chunk_size', 100);
        $this->tmpPath   = config('excel.exports.temp_path', sys_get_temp_dir());
    }

    /**
     * @param object $sheetExport
     *
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    public function open($sheetExport)
    {
        if ($sheetExport instanceof WithEvents) {
            $this->registerListeners($sheetExport->registerEvents());
        }

        $this->raise(new BeforeSheet($this));

        if ($sheetExport instanceof WithTitle) {
            $this->worksheet->setTitle($sheetExport->title());
        }

        if (($sheetExport instanceof FromQuery || $sheetExport instanceof FromCollection) && $sheetExport instanceof FromView) {
            throw ConcernConflictException::queryOrCollectionAndView();
        }

        if (!$sheetExport instanceof FromView && $sheetExport instanceof WithHeadings) {
            $this->append([$sheetExport->headings()], null, $this->hasStrictNullComparison($sheetExport));
        }
    }

    /**
     * @param object $sheetExport
     *
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Reader\Exception
     */
    public function export($sheetExport)
    {
        $this->open($sheetExport);

        if ($sheetExport instanceof FromView) {
            $this->fromView($sheetExport);
        } else {
            if ($sheetExport instanceof FromQuery) {
                $this->fromQuery($sheetExport, $this->worksheet);
            }

            if ($sheetExport instanceof FromCollection) {
                $this->fromCollection($sheetExport, $this->worksheet);
            }
        }

        $this->close($sheetExport);
    }

    /**
     * @param object $sheetExport
     *
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    public function close($sheetExport)
    {
        if ($sheetExport instanceof WithColumnFormatting) {
            foreach ($sheetExport->columnFormats() as $column => $format) {
                $this->formatColumn($column, $format);
            }
        }

        if ($sheetExport instanceof WithRangeStyling) {
            foreach ($sheetExport->rangeStyles() as $range => $style) {
                $this->styleRange($range, $style);
            }
        }

        if ($sheetExport instanceof WithColumnSizing) {
            foreach ($sheetExport->columnWidths() as $column => $width) {
                $this->sizeColumn($column, $width);
            }
        }

        if ($sheetExport instanceof ShouldAutoSize) {
            $this->autoSize();
        }

        $this->raise(new AfterSheet($this));
    }

    /**
     * @param FromView $sheetExport
     *
     * @throws \PhpOffice\PhpSpreadsheet\Reader\Exception
     */
    public function fromView(FromView $sheetExport)
    {
        $tempFile = $this->tempFile();
        file_put_contents($tempFile, $sheetExport->view()->render());

        $spreadsheet = $this->worksheet->getParent();

        /** @var Html $reader */
        $reader = IOFactory::createReader('Html');

        // Insert content into the last sheet
        $reader->setSheetIndex($spreadsheet->getSheetCount() - 1);
        $reader->loadIntoExisting($tempFile, $spreadsheet);
    }

    /**
     * @param FromQuery $sheetExport
     * @param Worksheet $worksheet
     */
    public function fromQuery(FromQuery $sheetExport, Worksheet $worksheet)
    {
        $sheetExport->query()->chunk($this->chunkSize, function ($chunk) use ($sheetExport, $worksheet) {
            foreach ($chunk as $row) {
                $this->appendRow($row, $sheetExport);
            }
        });
    }

    /**
     * @param FromCollection $sheetExport
     * @param Worksheet      $worksheet
     */
    public function fromCollection(FromCollection $sheetExport, Worksheet $worksheet)
    {
        $sheetExport
            ->collection()
            ->each(function ($row) use ($sheetExport, $worksheet) {
                $this->appendRow($row, $sheetExport);
            });
    }

    /**
     * @param array    $rows
     * @param int|null $row
     * @param bool  $strictNullComparison
     *
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    public function append(array $rows, int $row = null, bool $strictNullComparison = false)
    {
        if (!$row) {
            $row = 1;
            if ($this->hasRows()) {
                $row = $this->worksheet->getHighestRow() + 1;
            }
        }

        $this->worksheet->fromArray($rows, null, 'A' . $row, $strictNullComparison);
    }

    /**
     * @return void
     */
    public function autoSize()
    {
        foreach ($this->buildColumnRange('A', $this->worksheet->getHighestDataColumn()) as $col) {
            $this->worksheet->getColumnDimension($col)->setAutoSize(true);
        }
    }

    /**
     * @param string $column
     * @param string $format
     *
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    public function formatColumn(string $column, string $format)
    {
        $this->worksheet
            ->getStyle($column . '1:' . $column . $this->worksheet->getHighestRow())
            ->getNumberFormat()
            ->setFormatCode($format);
    }

    /**
     * @param string $column
     * @param string $format
     *
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    public function sizeColumn(string $column, float $width)
    {
        $this->worksheet
            ->getColumnDimension($column)
            ->setWidth($width);
    }

    /**
     * @param string $range
     * @param string $style
     *
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    public function styleRange(string $range, array $style)
    {
        if (empty($range)) {
            return;
        }
        if (!empty($style['mergeCells'])) {
            $this->worksheet->mergeCells($range);
        }
        if (!empty($style['repeatHeader'])) {
            $bounds = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::rangeBoundaries($range);            
            $this->worksheet->getPageSetup()->setRowsToRepeatAtTopByStartAndEnd(1, $bounds[1][1]);
        }

        $this->worksheet
            ->getStyle($range)
            ->applyFromArray($style);
    }

    /**
     * @param int $chunkSize
     *
     * @return Sheet
     */
    public function chunkSize(int $chunkSize)
    {
        $this->chunkSize = $chunkSize;

        return $this;
    }

    /**
     * @return Worksheet
     */
    public function getDelegate()
    {
        return $this->worksheet;
    }

    /**
     * @param iterable $rows
     * @param object   $sheetExport
     *
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    public function appendRows($rows, $sheetExport)
    {
        $append = [];
        foreach ($rows as $row) {
            if ($sheetExport instanceof WithMapping) {
                $row = $sheetExport->map($row);
            }

            $append[] = $row;
        }

        $this->append($append, null, $this->hasStrictNullComparison($sheetExport));
    }

    /**
     * @param iterable $row
     * @param object   $sheetExport
     *
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    protected function appendRow($row, $sheetExport)
    {
        if ($sheetExport instanceof WithMapping) {
            $row = $sheetExport->map($row);
        }

        if ($row instanceof Arrayable) {
            $row = $row->toArray();
        }

        if (isset($row[0]) && is_array($row[0])) {
            $this->append($row, null, $this->hasStrictNullComparison($sheetExport));
        } else {
            $this->append([$row], null, $this->hasStrictNullComparison($sheetExport));
        }
    }

    /**
     * @param string $lower
     * @param string $upper
     *
     * @return \Generator
     */
    protected function buildColumnRange(string $lower, string $upper)
    {
        $upper++;
        for ($i = $lower; $i !== $upper; $i++) {
            yield $i;
        }
    }

    /**
     * @return string
     */
    protected function tempFile(): string
    {
        return tempnam($this->tmpPath, 'laravel-excel');
    }

    /**
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @return bool
     */
    private function hasRows(): bool
    {
        return $this->worksheet->cellExists('A1');
    }

    /**
     * @param object $sheetExport
     *
     * @return bool
     */
    private function hasStrictNullComparison($sheetExport): bool
    {
        return $sheetExport instanceof WithStrictNullComparison;
    }
}

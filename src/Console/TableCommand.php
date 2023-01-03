<?php

declare(strict_types=1);

namespace PayDay\Console;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use PayDay\View;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\TableSeparator;
use Symfony\Component\Console\Helper\TableStyle;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class TableCommand extends Command
{
    // In this function set the name, description and help hint for the command
    protected function configure(): void
    {
        // Use in-build functions to set name, description and help
        $this->setName('view')
            ->setDescription('Displays all the monthly pay dates for the given year(s)')
            ->setHelp('Pass the year(s) you want to see the monthly pay dates')
            ->addArgument('years', InputArgument::IS_ARRAY, 'Pass the year(s). Either one by one or as range year-year. Or a mix of both.')
            ->addOption('ascii', '-a', InputOption::VALUE_NEGATABLE, 'Do you want to have a ASCII table printed?', false)
            ->addOption('columns', '-c', InputOption::VALUE_REQUIRED, 'How many years (columns) do you wanna display in one table?', 10)
            ->addOption('dayname', '-e', InputOption::VALUE_NEGATABLE, 'Do you want see the day name?', true)
            ->addOption('duplicates', '-d', InputOption::VALUE_NEGATABLE, 'Do you want see duplicates?', false)
            ->addOption('footer', '-f', InputOption::VALUE_NEGATABLE, 'Do you want see the table footer?', true)
            ->addOption('separator', '-s', InputOption::VALUE_NEGATABLE, 'Do you want to have a separator for the rows printed?', true)
            ->addOption('title', '-t', InputOption::VALUE_NEGATABLE, 'Do you wanna see the table title?', true);
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $years = $input->getArgument('years');
        $chunkSize = $input->getOption('columns');
        $duplicates = $input->getOption('duplicates');
        $years[] = ['duplicates' => (bool) $duplicates];
        $data = View::create($years)->list();
        $years = $data->get('years');
        if (!$duplicates) {
            $years = $years->unique();
        }
        $days = $data->get('days');
        $months = $data->get('months');
        $pageData = $years->chunk($chunkSize);
        $pages = $pageData->count();
        $pageData->each(function ($yearsChunk, $index) use ($chunkSize, &$days, $months, $pages, $input, $output) {
            $ascii = $input->getOption('ascii');
            $dayName = $input->getOption('dayname');
            $footer = $input->getOption('footer');
            $duplicates = $input->getOption('duplicates');
            $title = $input->getOption('title');
            $format = "d";
            if ($dayName) {
                $format = "D, d";
            }
            $separator = $input->getOption('separator');
            $rows = Collection::make();
            $rowData = $days->take($chunkSize);
            $days = $days->slice($chunkSize);
            $headers = Collection::make(['Month'])->merge($yearsChunk->flatten());
            // TODO: avoid this formatting hack, due to issues with TableStyle
            $rowData->transform(fn (Collection $y) => $y->map(fn (Carbon $d) => "<fg=green>{$d->format($format)}</>"));
            // generate row data, by zipping the days into months collection
            $months->zip(...$rowData)->each(function ($row, $i) use ($rows, $separator) {
                $rows->push($row);
                if ($separator && $i < 11) {
                    $rows->push(new TableSeparator());
                }
            });

            // Generating the Table for console output
            $table = new Table($output);

            Collection::make()->range(1, $yearsChunk->count())->each(function ($column) use ($table) {
                $table->setColumnStyle($column, $this->columnTableStyle());
            });

            $page = $index + 1;
            if ($footer && $pages > 1) {
                $table->setFooterTitle("Page {$page}/{$pages}");
            }

            if ($title) {
                $table->setHeaderTitle('Pay days');
            }

            $table
                ->setHeaders($headers->toArray())
                ->setRows($rows->toArray())
                ->setStyle($this->tableStyle($ascii))
                ->render();
        });

        return Command::SUCCESS;
    }

    protected function columnTableStyle(): TableStyle
    {
        $tableStyle = new TableStyle();
        $tableStyle->setCellHeaderFormat('<fg=bright-white>%s</>');
        $tableStyle->setPadType(STR_PAD_BOTH);

        return $tableStyle;
    }

    protected function tableStyle(bool $ascii = false): TableStyle
    {
        $tableStyle = new TableStyle();
        $tableStyle->setCellHeaderFormat('<fg=bright-white>%s</>');
        $tableStyle->setCellRowFormat('<fg=cyan>%s</>');

        if (!$ascii) {
            // copied "box" table style from /src/Symfony/Component/Console/Helper/Table.php
            // because if one uses an own TableStyle it is not possible to also use a default one
            $tableStyle
                ->setHorizontalBorderChars('─')
                ->setVerticalBorderChars('│')
                ->setCrossingChars('┼', '┌', '┬', '┐', '┤', '┘', '┴', '└', '├');
        }

        return $tableStyle;
    }
}

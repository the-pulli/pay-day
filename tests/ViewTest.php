<?php

declare(strict_types=1);

namespace Tests;

use Illuminate\Support\Collection;
use PHPUnit\Framework\TestCase;
use PayDay\View;

final class ViewTest extends TestCase
{
    protected function setUp(): void
    {
        $this->expectedOneYear = [
          2023 => [31, 28, 31, 28, 31, 30, 31, 31, 29, 31, 30, 29]
        ];
        $this->expectedOneYearDuplicate = [
          [31, 28, 31, 28, 31, 30, 31, 31, 29, 31, 30, 29],
          [31, 28, 31, 28, 31, 30, 31, 31, 29, 31, 30, 29]
        ];
        $this->expectedTwoYears = [
          2024 => [31, 29, 29, 30, 31, 28, 31, 30, 30, 31, 29, 31],
          2025 => [31, 28, 31, 30, 30, 30, 31, 29, 30, 31, 28, 31]
        ];
        $this->expectedMultipleYears = $this->expectedOneYear + $this->expectedTwoYears;

        Collection::macro('testViews', function (array $expected, TestCase $context) {
            $this->map(fn (View $view) => $view->numberPayDays()->toArray())
                 ->each(fn (array $days) => $context->assertSame($expected, $days));
        });
    }

    public function testOneYearView(): void
    {
        Collection::make([
          View::create(2023),
          View::create([2023]),
          View::create('2023'),
          View::create(['2023']),
          View::create('2023-2023'),
          View::create(['2023-2023']),
          View::create(['2023-2023', 2023])
        ])->testViews($this->expectedOneYear, $this);
    }

    public function testOneYearDuplicateView(): void
    {
        Collection::make(
            [View::create(2023, 2023, ['duplicates' => true])]
        )->testViews($this->expectedOneYearDuplicate, $this);
    }

    public function testTwoYearsView(): void
    {
        Collection::make([
          View::create(2024, 2025),
          View::create([2024, 2025]),
          View::create('2024', '2025'),
          View::create(['2024', '2025']),
          View::create('2024-2025'),
          View::create(['2024-2025'])
        ])->testViews($this->expectedTwoYears, $this);
    }

    public function testMultipleYearsView(): void
    {
        Collection::make([
          View::create(2023, 2024, 2025),
          View::create([2023, 2024, 2025]),
          View::create('2023', '2024', '2025'),
          View::create(['2023', '2024', '2025']),
          View::create('2023-2025'),
          View::create(['2023-2025']),
          View::create(['2023-2025', 2025])
        ])->testViews($this->expectedMultipleYears, $this);
    }
}

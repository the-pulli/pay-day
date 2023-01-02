<?php

declare(strict_types=1);

namespace PayDay;

use Carbon\Carbon;
use Illuminate\Support\Collection;

class View
{
    protected bool $duplicates = false;

    protected Collection $years;

    protected Collection $months;

    protected function __construct(Collection $arguments)
    {
        $this->years = $this->findOptions($this->prepareYears($arguments));
        $this->months = Collection::make();
    }

    protected function findOptions(Collection $arguments): Collection
    {
        $result = $arguments->search(fn ($item) => Collection::make([true, false])->containsStrict($item));

        if ($result === false) {
            return $arguments;
        }

        $this->duplicates = $arguments->get($result);
        $arguments->pop();
        return $arguments;
    }


    protected function prepareYears(Collection $years): Collection
    {
        return $years->flatten()->map(function (mixed $year) {
            if (is_string($year)) {
                $years = Collection::make(explode('-', $year));
                if ($years->count() > 1) {
                    return Collection::make()->range($years->first(), $years->last());
                }
                return $years;
            } else {
                return $year;
            }
        })->flatten();
    }

    protected function prevDay(Carbon $day): Carbon
    {
        if ($day->isWeekday()) {
            return $day;
        }

        return $day->previousWeekday();
    }

    protected function findPayDays(): Collection
    {
        if ($this->duplicates) {
            return $this->years->map(function (int $year, int $i) {
                return Collection::make()->range(1, 12)->map(function (int $month) use ($year, $i) {
                    $lastDayOfMonth = Carbon::parse("{$year}-{$month}-01")->lastOfMonth();
                    if ($i === 0) {
                        $this->months->push($lastDayOfMonth->format('F'));
                    }
                    return $this->prevDay($lastDayOfMonth);
                });
            });
        }

        $payDays = Collection::make();
        $this->years->each(function (int $year, int $i) use ($payDays) {
            $payDays->put($year, Collection::make()->range(1, 12)->map(function (int $month) use ($year, $i) {
                $lastDayOfMonth = Carbon::parse("{$year}-{$month}-01")->lastOfMonth();
                if ($i === 0) {
                    $this->months->push($lastDayOfMonth->format('F'));
                }
                return $this->prevDay($lastDayOfMonth);
            }));
        });

        return $payDays;
    }

    public static function create(mixed ...$years): self
    {
        return new self(Collection::make($years));
    }

    public function numberPayDays(): Collection
    {
        return $this->findPayDays()->map(fn (Collection $y) => $y->map(fn (Carbon $d) => $d->day));
    }

    public function list(): Collection
    {
        return Collection::make([
          'years' => $this->years,
          'months' => $this->months,
          'days' => $this->findPayDays()
        ]);
    }

    public function payDays(): Collection
    {
        return $this->findPayDays();
    }
}

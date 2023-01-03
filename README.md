# PayDay

![PHP 8.1](https://github.com/the-pulli/pay-day/actions/workflows/ci-81.yml/badge.svg) ![PHP 8.2](https://github.com/the-pulli/pay-day/actions/workflows/ci-82.yml/badge.svg)


Provides a class PayDay\View. This class generates the pay days for the given year(s).

## Installation

Install the package and add it to the application's composer.json by executing:

    $ composer require pulli/pay-day

## Usage

```php
<?php
// Get the pay days
PayDay\View::create(2023)->payDays(); // return Illuminate\Support\Collection with the pay days
PayDay\View::create('2023')->payDays(); // accept String as input
PayDay\View::create(2023, 2024)->payDays(); // accept multiple years
PayDay\View::create([2023])->payDays(); // accept Array's as input
PayDay\View::create('2023-2024')->payDays(); // accept String ranges
PayDay\View::create(range(2023, 2024))->payDays(); // accept Range as input
PayDay\View::create(range(2023, 2024), 2025, '2026')->payDays(); // accept a mix of all of them

// Last parameter defines the options if set
// shows 2023 twice in the list of pay_days, default is false
PayDay\View::create(range(2023, 2024), 2023, ['duplicates' => true])->payDays();
```

### pay_day executable supports the following options:

Option | Negated | Shortcut | Default
--- | ---: | ---: | ---:
--ascii | --no-ascii | -a | false
--columns | | -c | 10
--dayname | --no-dayname | -e | true
--duplicates | --no-duplicates | -d | false
--footer | --no-footer | -f | true
--header | --no-header | -h | true
--separator | --no-separator | -s | true

```bash
pay_day view 2023 2024 2025-2027
```

## Fun Fact

Because I had so much fun doing this project, I implemented it in [Ruby](https://github.com/the-pulli/payment_day) too.

## Contributing

Bug reports and pull requests are welcome on GitHub at https://github.com/the-pulli/pay-day.

## License

The package is available as open source under the terms of the [MIT License](https://opensource.org/licenses/MIT).

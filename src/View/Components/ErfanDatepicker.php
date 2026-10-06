<?php

namespace Erfan\Datepicker\View\Components;

use DateTimeImmutable;
use DateTimeInterface;
use DateTimeZone;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Arr;
use Illuminate\View\Component;
use InvalidArgumentException;
use Morilog\Jalali\CalendarUtils;

class ErfanDatepicker extends Component
{
    public array $config;

    public string $inputId;

    public function __construct(
        public string $name,
        public string $label = 'تاریخ و ساعت',
        public string $theme = 'tailwind',
        public string $mode = 'direct',
        string|DateTimeInterface|null $value = null,
        public bool $withTime = true,
        public bool $defaultNow = true,
        public bool $required = false,
        public bool $disabled = false,
        public bool $readonly = false,
        public bool $clearable = true,
        public bool $useOld = true,
        public ?string $errorMessage = null,
        public string $timezone = 'Asia/Tehran',
        public int $minYear = 1300,
        public int $maxYear = 1450,
        string|DateTimeInterface|null $min = null,
        string|DateTimeInterface|null $max = null,
        ?string $id = null,
    ) {
        if (! in_array($theme, ['bootstrap', 'tailwind'], true)) {
            throw new InvalidArgumentException('Erfan datepicker theme must be bootstrap or tailwind.');
        }

        if (! in_array($mode, ['direct', 'calendar'], true)) {
            throw new InvalidArgumentException('Erfan datepicker mode must be direct or calendar.');
        }

        if ($minYear < 1000 || $maxYear > 2999 || $minYear > $maxYear || $maxYear - $minYear > 400) {
            throw new InvalidArgumentException('Erfan datepicker requires an ordered range of at most 401 years between 1000 and 2999.');
        }

        $zone = new DateTimeZone($timezone);
        $this->inputId = $id ?? 'erfan-datepicker-'.bin2hex(random_bytes(6));
        $oldKey = trim(str_replace(['[', ']'], ['.', ''], $name), '.');
        $hasOldValue = $useOld && Arr::has(session()->getOldInput(), $oldKey);
        $initialValue = $hasOldValue ? session()->getOldInput($oldKey) : $value;
        $format = $withTime ? 'Y-m-d\TH:i' : 'Y-m-d';
        $normalize = static function (mixed $date) use ($zone, $format, $withTime): string {
            if ($date instanceof DateTimeInterface) {
                return $withTime
                    ? DateTimeImmutable::createFromInterface($date)->setTimezone($zone)->format($format)
                    : $date->format($format);
            }

            return is_string($date) ? $date : '';
        };

        $yearStarts = [];

        // Civil dates, not timestamps: the browser must not apply its own timezone.
        for ($year = $minYear; $year <= $maxYear + 1; $year++) {
            [$gregorianYear, $month, $day] = CalendarUtils::toGregorian($year, 1, 1);
            $yearStarts[$year] = sprintf('%04d-%02d-%02d', $gregorianYear, $month, $day);
        }

        $this->config = [
            'value' => $normalize($initialValue),
            'mode' => $mode,
            'withTime' => $withTime,
            'defaultNow' => $defaultNow && ! $hasOldValue,
            'disabled' => $disabled,
            'readonly' => $readonly,
            'timezone' => $timezone,
            'minYear' => $minYear,
            'maxYear' => $maxYear,
            'min' => $normalize($min),
            'max' => $normalize($max),
            'yearStarts' => $yearStarts,
        ];
    }

    public function render(): View
    {
        return view('erfan-datepicker::components.index');
    }
}

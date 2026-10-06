<?php

namespace Erfan\Datepicker\Tests;

use DateTimeImmutable;
use Erfan\Datepicker\ErfanDatepickerServiceProvider;
use Erfan\Datepicker\View\Components\ErfanDatepicker;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;

class ErfanDatepickerTest extends TestCase
{
    public function test_provider_registers_both_themes_without_host_components(): void
    {
        $html = Blade::render(<<<'BLADE'
            <x-erfan-datepicker name="scheduled_at" theme="bootstrap" value="2026-09-27T14:32" required />
            <x-erfan-datepicker name="birth_date" theme="tailwind" :with-time="false" :default-now="false" disabled />
            BLADE);

        $this->assertSame(ErfanDatepicker::class, Blade::getClassComponentAliases()['erfan-datepicker']);
        $this->assertStringContainsString('erfan-datepicker--bootstrap', $html);
        $this->assertStringContainsString('erfan-datepicker--tailwind', $html);
        $this->assertStringContainsString('name="scheduled_at"', $html);
        $this->assertStringContainsString('value="2026-09-27T14:32"', $html);
        $this->assertStringContainsString('name="birth_date"', $html);
        $this->assertStringContainsString('required', $html);
        $this->assertStringContainsString('disabled', $html);

        preg_match_all('/id="(erfan-datepicker-[a-f0-9]+)"/', $html, $matches);

        $this->assertCount(2, array_unique($matches[1]));
    }

    public function test_published_asset_and_view_paths_are_scoped_to_the_package(): void
    {
        $assets = ServiceProvider::pathsToPublish(ErfanDatepickerServiceProvider::class, 'erfan-datepicker-assets');
        $views = ServiceProvider::pathsToPublish(ErfanDatepickerServiceProvider::class, 'erfan-datepicker-views');

        $this->assertEqualsCanonicalizing([
            resource_path('js/vendor/erfan-datepicker'),
            resource_path('css/vendor/erfan-datepicker'),
        ], array_values($assets));
        $this->assertSame([resource_path('views/vendor/erfan-datepicker')], array_values($views));

        foreach (array_keys($assets + $views) as $source) {
            $this->assertDirectoryExists($source);
        }
    }

    public function test_old_input_including_explicit_empty_value_wins_over_default_now(): void
    {
        session()->flashInput(['dates' => ['birth' => null]]);
        $picker = new ErfanDatepicker(name: 'dates[birth]', value: '2026-09-27');

        $this->assertSame('', $picker->config['value']);
        $this->assertFalse($picker->config['defaultNow']);

        session()->flashInput(['dates' => ['birth' => '2000-03-20']]);

        $this->assertSame('2000-03-20', (new ErfanDatepicker(name: 'dates[birth]'))->config['value']);
    }

    public function test_old_input_can_be_disabled_for_an_independent_form(): void
    {
        session()->flashInput(['expires_at' => '2027-01-01T10:00']);
        $picker = new ErfanDatepicker(name: 'expires_at', value: '', useOld: false, defaultNow: false);

        $this->assertSame('', $picker->config['value']);
        $this->assertFalse($picker->config['defaultNow']);
    }

    public function test_explicit_error_and_custom_id_render(): void
    {
        $html = Blade::render(<<<'BLADE'
            <x-erfan-datepicker name="at" id="booking-at" error-message="Please choose a valid date." />
            BLADE);

        $this->assertStringContainsString('id="booking-at-trigger"', $html);
        $this->assertStringContainsString('Please choose a valid date.', $html);
    }

    public function test_calendar_boundaries_come_from_morilog_and_datetime_values_use_explicit_timezone(): void
    {
        $picker = new ErfanDatepicker(
            name: 'at',
            value: new DateTimeImmutable('2026-09-27T11:02:00Z'),
            minYear: 1403,
            maxYear: 1405,
        );

        $this->assertSame('2026-09-27T14:32', $picker->config['value']);
        $this->assertSame('2024-03-20', $picker->config['yearStarts'][1403]);
        $this->assertSame('2025-03-21', $picker->config['yearStarts'][1404]);
        $this->assertSame('2026-03-21', $picker->config['yearStarts'][1405]);
        $this->assertArrayHasKey(1406, $picker->config['yearStarts']);
    }

    public function test_date_only_values_preserve_the_civil_date_without_timezone_conversion(): void
    {
        $picker = new ErfanDatepicker(
            name: 'birthday',
            value: new DateTimeImmutable('2000-03-20T23:30:00Z'),
            withTime: false,
        );

        $this->assertSame('2000-03-20', $picker->config['value']);
    }

    #[DataProvider('invalidConfiguration')]
    public function test_invalid_configuration_is_rejected(array $arguments): void
    {
        $this->expectException(InvalidArgumentException::class);

        new ErfanDatepicker(...['name' => 'date', ...$arguments]);
    }

    public static function invalidConfiguration(): array
    {
        return [
            'unknown theme' => [['theme' => '../other']],
            'unknown mode' => [['mode' => 'other']],
            'excessive range' => [['minYear' => 1000, 'maxYear' => 2999]],
            'reversed range' => [['minYear' => 1405, 'maxYear' => 1400]],
            'unsupported lower bound' => [['minYear' => 999]],
            'unsupported upper bound' => [['maxYear' => 3000]],
        ];
    }
}

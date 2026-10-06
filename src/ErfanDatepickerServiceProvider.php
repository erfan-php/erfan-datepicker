<?php

namespace Erfan\Datepicker;

use Erfan\Datepicker\View\Components\ErfanDatepicker;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class ErfanDatepickerServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'erfan-datepicker');

        Blade::component('erfan-datepicker', ErfanDatepicker::class);

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../resources/js' => resource_path('js/vendor/erfan-datepicker'),
                __DIR__.'/../resources/css' => resource_path('css/vendor/erfan-datepicker'),
            ], 'erfan-datepicker-assets');

            $this->publishes([
                __DIR__.'/../resources/views' => resource_path('views/vendor/erfan-datepicker'),
            ], 'erfan-datepicker-views');
        }
    }
}

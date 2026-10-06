<?php

// Build the examples from the actual package views in an isolated Laravel app.
// Testbench is a development dependency; consumers do not need it.
require dirname(__DIR__).'/vendor/autoload.php';

use Erfan\Datepicker\ErfanDatepickerServiceProvider;
use Illuminate\Support\Facades\Blade;
use Orchestra\Testbench\Foundation\Application;

$app = Application::create(options: [
    'extra' => [
        'providers' => [ErfanDatepickerServiceProvider::class],
        'dont-discover' => ['*'],
    ],
]);

$output = __DIR__.'/rendered';

if (! is_dir($output)) {
    mkdir($output, 0755, true);
}

$template = file_get_contents(__DIR__.'/demo.blade.php');

foreach (['bootstrap', 'tailwind'] as $theme) {
    file_put_contents($output.'/'.$theme.'.html', Blade::render($template, ['theme' => $theme]));
}

file_put_contents($output.'/index.html', <<<'HTML'
<!doctype html>
<html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Erfan Datepicker examples</title></head>
<body><h1>erfan-datepicker</h1><p>Choose a standalone skin / پوسته را انتخاب کنید</p><ul><li><a href="bootstrap.html">Bootstrap</a></li><li><a href="tailwind.html">Tailwind</a></li></ul></body></html>
HTML);

echo "Rendered Bootstrap and Tailwind examples.\n";

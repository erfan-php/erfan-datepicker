<?php

namespace Erfan\Datepicker\Tests;

use Erfan\Datepicker\ErfanDatepickerServiceProvider;
use Orchestra\Testbench\TestCase as OrchestraTestCase;

abstract class TestCase extends OrchestraTestCase
{
    protected function getPackageProviders($app): array
    {
        return [ErfanDatepickerServiceProvider::class];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('session.driver', 'array');
        $app['config']->set('app.key', 'base64:AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA=');
    }
}

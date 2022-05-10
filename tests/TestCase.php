<?php

namespace Octopy\DTO\Tests;

use Illuminate\Foundation\Application;
use Octopy\DTO\DTOServiceProvider;

/**
 * Class TestCase
 * @package Octopy\DTO
 */
class TestCase extends \Orchestra\Testbench\TestCase
{
    /**
     * @return void
     */
    protected function setUp() : void
    {
        parent::setUp();
    }

    /**
     * @param  Application $app
     * @return array
     */
    protected function getPackageProviders($app) : array
    {
        return [
            DTOServiceProvider::class,
        ];
    }
}

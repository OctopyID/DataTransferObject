<?php

namespace Octopy\DTO;

use Octopy\DTO\Console\MakeDTOCommand;

class DTOServiceProvider extends \Illuminate\Support\ServiceProvider
{
    /**
     * @return void
     */
    public function register() : void
    {
        //
    }

    /**
     * @return void
     */
    public function boot() : void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                MakeDTOCommand::class,
            ]);
        }
    }
}

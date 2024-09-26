<?php

namespace Nijwel\CrudGenerator;

use Illuminate\Support\ServiceProvider;

class CrudServiceProvider extends ServiceProvider
{
    public function register()
    {
        // Register the command
        $this->commands([
            \Nijwel\CrudGenerator\Console\Commands\MakeCrud::class,
        ]);
    }

    public function boot()
    {
        // Publish stubs so that users can customize them
        $this->publishes([
            __DIR__ . '/../../stubs' => base_path('stubs/nijwel-crud-package'),
        ], 'crud-stubs');
    }
}

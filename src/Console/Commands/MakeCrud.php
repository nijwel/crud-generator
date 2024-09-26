<?php

namespace Nijwel\CrudGenerator\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class MakeCrud extends Command
{
    protected $signature = 'make:crud {name}';
    protected $description = 'Create a controller, model, and migration with CRUD methods';

    public function handle()
    {
        $name = $this->argument('name');
        $segments = explode('/', $name);
        $modelName = array_pop($segments);
        $namespacePath = implode('/', $segments);

        $controllerName = "{$modelName}Controller";
        $tableName = Str::plural(Str::snake($modelName));

        $this->createModel($modelName, $namespacePath);
        $this->createMigration($tableName);
        $this->createController($controllerName, $modelName, $namespacePath);

        $this->info("CRUD for {$name} created successfully.");
    }

    protected function createModel($modelName, $namespacePath)
    {
        $modelPath = app_path("Models/{$namespacePath}/{$modelName}.php");
        $this->ensureDirectoryExists(dirname($modelPath));

        $modelTemplate = $this->getStub('model');
        $this->replacePlaceholders($modelTemplate, [
            '{{ model }}' => $modelName,
        ]);

        File::put($modelPath, $modelTemplate);
        $this->info("Model {$modelName} created.");
    }

    protected function createMigration($tableName)
    {
        $migrationName = "create_{$tableName}_table";
        $migrationPath = database_path('migrations/' . now()->format('Y_m_d_His') . "_{$migrationName}.php");
        $migrationTemplate = $this->getStub('migration');

        $this->replacePlaceholders($migrationTemplate, [
            '{{ table }}' => Str::studly($tableName),
            '{{ tableSnake }}' => $tableName,
        ]);

        File::put($migrationPath, $migrationTemplate);
        $this->info("Migration for table {$tableName} created.");
    }

    protected function createController($controllerName, $modelName, $namespacePath)
    {
        $controllerPath = app_path("Http/Controllers/{$namespacePath}/{$controllerName}.php");
        $this->ensureDirectoryExists(dirname($controllerPath));

        if (File::exists($controllerPath)) {
            $this->error("Controller {$controllerName} already exists!");
            return;
        }

        $modelVariable = lcfirst($modelName);
        $modelPlural = Str::plural($modelVariable);
        $controllerTemplate = $this->getStub('controller');

        $this->replacePlaceholders($controllerTemplate, [
            '{{ controller }}' => $controllerName,
            '{{ model }}' => $modelName,
            '{{ modelVariable }}' => $modelVariable,
            '{{ modelVariablePlural }}' => $modelPlural,
        ]);

        File::put($controllerPath, $controllerTemplate);
        $this->info("Controller {$controllerName} created.");
    }

    protected function getStub($type)
    {
        return File::get(__DIR__ . "/../../../stubs/{$type}.stub");
    }

    protected function replacePlaceholders(&$template, array $replacements)
    {
        foreach ($replacements as $search => $replace) {
            $template = str_replace($search, $replace, $template);
        }
    }

    protected function ensureDirectoryExists($directory)
    {
        if (!File::exists($directory)) {
            File::makeDirectory($directory, 0755, true);
        }
    }
}

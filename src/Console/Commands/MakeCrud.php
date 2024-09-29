<?php

namespace Nijwel\CrudGenerator\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class MakeCrud extends Command
{
    // Add --api option in the command signature
    // Add --api, --db, --model (m) options in the command signature
    protected $signature = 'make:crud {name} {--api} {--db} {--m} {--r}';
    protected $description = 'Create a controller, model, and migration with CRUD methods';

    public function handle()
    {
        $name = $this->argument('name');
        $isApi = $this->option('api'); // Check if the --api option is provided
        $isDB = $this->option('db'); // Check if the --db option is provided
        $isModel = $this->option('m'); // Check if the --model option is provided
        $isRoute = $this->option('r'); // Check if the --model option is provided

        $segments = explode('/', $name);
        $modelName = array_pop($segments);
        $namespacePath = !empty($segments) ? implode('\\', $segments) : ''; // Convert to namespace format

        $controllerName = "{$modelName}Controller";
        $tableName = Str::plural(Str::snake($modelName));

        // If no option is provided, create all components
        if (!$isApi && !$isDB && !$isModel && !$isRoute) {
            $this->createModel($modelName, $namespacePath);
            $this->createMigration($tableName);
            $this->createController($controllerName, $modelName, $namespacePath, $isApi);
            $this->createRoutes($controllerName, $modelName, $namespacePath , $isApi);
        } else {
            // Execute based on provided options
            if ($isModel) {
                $this->createModel($modelName, $namespacePath);
            }
            if ($isDB) {
                $this->createMigration($tableName);
            }
            if ($isApi || !$isApi) {
                $this->createController($controllerName, $modelName, $namespacePath, $isApi);
            }
            if ($isRoute || !$isRoute) {
                $this->createRoutes($controllerName, $modelName, $namespacePath , $isApi);
            }
        }

        // Display success message
        $this->info("CRUD for {$name} created successfully.");
    }

    protected function createModel($modelName, $namespacePath)
    {
        $modelPath = app_path("Models/{$namespacePath}/{$modelName}.php");
        $this->ensureDirectoryExists(dirname($modelPath));

        $modelTemplate = $this->getStub('model');
        $this->replacePlaceholders($modelTemplate, [
            '{{ model }}' => $modelName,
            '{{ namespacePath }}' => $namespacePath ? "\\{$namespacePath}" : '', // Append namespace if it exists
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

    protected function createController($controllerName, $modelName, $namespacePath, $isApi)
    {
        $controllerPath = $isApi ? app_path("Http/Controllers/API/{$namespacePath}/{$controllerName}.php") : app_path("Http/Controllers/{$namespacePath}/{$controllerName}.php");
        $this->ensureDirectoryExists(dirname($controllerPath));

        if (File::exists($controllerPath)) {
            $this->error("Controller {$controllerName} already exists!");
            return;
        }

        $modelVariable = lcfirst($modelName);
        $modelPlural = Str::plural($modelVariable);

        // Select the appropriate stub file based on the --api option
        $controllerTemplate = $isApi ? $this->getStub('controller.api') : $this->getStub('controller');

        $this->replacePlaceholders($controllerTemplate, [
            '{{ controller }}' => $controllerName,
            '{{ model }}' => $modelName,
            '{{ modelVariable }}' => $modelVariable,
            '{{ modelVariablePlural }}' => $modelPlural,
            '{{ namespacePath }}' => $namespacePath ? "\\{$namespacePath}" : '', // Append namespace if it exists
            '{{ api }}' => $isApi ? "\\API" : '', // Append namespace if it's an API
        ]);

        File::put($controllerPath, $controllerTemplate);
        $this->info("Controller {$controllerName} created.");
    }

    // protected function createRoutes($controllerName, $modelName, $isApi)
    // {
    //     // Set the appropriate route path
    //     $routesFilePath = base_path($isApi ? 'routes/api.php' : 'routes/web.php');

    //     // Load the routes stub
    //     $routeTemplate = $this->getStub('routes');

    //     // Replace placeholders in the route template
    //     $this->replacePlaceholders($routeTemplate, [
    //         '{{name}}' => Str::snake($modelName),
    //         '{{controller}}' => $controllerName,
    //     ]);


    //     // Add the route group to the appropriate routes file
    //     File::append($routesFilePath, $routeTemplate);
    //     $this->info("Routes for {$controllerName} added to " . ($isApi ? 'api.php' : 'web.php'));
    // }

    // protected function createRoutes($controllerName, $modelName, $isApi)
    // {
    //     // Set the appropriate route file path (web.php or api.php)
    //     $routesFilePath = base_path($isApi ? 'routes/api.php' : 'routes/web.php');

    //     // Load the routes stub
    //     $routeTemplate = $this->getStub('routes');

    //     // Replace placeholders in the route template
    //     $this->replacePlaceholders($routeTemplate, [
    //         '{{name}}' => Str::snake($modelName),
    //         '{{controller}}' => $controllerName,
    //     ]);

    //     // Check if the routes file exists
    //     if (File::exists($routesFilePath)) {
    //         // Read the file content
    //         $fileContent = File::get($routesFilePath);

    //         // Find the last occurrence of }); or }
    //         $position = strrpos($fileContent, '});');

    //         if ($position === false) {
    //             // If '});' is not found, try finding the last '}'
    //             $position = strrpos($fileContent, '}');
    //         }

    //         if ($position !== false) {
    //             // Insert the new route before the last '});' or '}'
    //             $newContent = substr_replace($fileContent, PHP_EOL . $routeTemplate . PHP_EOL, $position, 0);

    //             // Write the updated content back to the routes file
    //             File::put($routesFilePath, $newContent);
    //             $this->info("Routes for {$controllerName} added to " . ($isApi ? 'api.php' : 'web.php') . " before the last });");
    //         } else {
    //             // If no closing }); or } is found, append the routes to the file
    //             File::append($routesFilePath, PHP_EOL . $routeTemplate);
    //             $this->info("Routes for {$controllerName} appended to " . ($isApi ? 'api.php' : 'web.php'));
    //         }
    //     } else {
    //         $this->error("Routes file not found at {$routesFilePath}");
    //     }
    // }

    // protected function createRoutes($controllerName, $modelName, $namespacePath, $isApi)
    // {
    //     // Set the appropriate route file path (web.php or api.php)
    //     $routesFilePath = base_path($isApi ? 'routes/api.php' : 'routes/web.php');

    //     // Load the routes stub
    //     $routeTemplate = $this->getStub('routes');

    //     // Replace placeholders in the route template
    //     $this->replacePlaceholders($routeTemplate, [
    //         '{{name}}' => Str::snake($modelName),
    //         '{{controller}}' => $controllerName,
    //     ]);

    //     // Check if the routes file exists
    //     if (File::exists($routesFilePath)) {
    //         // Read the file content
    //         $fileContent = File::get($routesFilePath);

    //         // Handle the namespace path for the controller
    //         $namespace = "use App\Http\Controllers";
    //         if (!empty($namespacePath)) {
    //             // If there is a namespace, append it
    //             $namespace .= "\\$namespacePath";
    //         }
    //         $namespace .= "\\$controllerName;\n";

    //         // Check if the namespace is already present at the top of the file
    //         if (strpos($fileContent, $namespace) === false) {
    //             // Find the position right after the `<?php` opening tag
    //             $position = strpos($fileContent, "<?php") + strlen("<?php\n");

    //             // Insert the namespace right after the `<?php` tag
    //             $fileContent = substr_replace($fileContent, $namespace, $position, 0);
    //         }

    //         // Find the last occurrence of '});' or '}'
    //         $position = strrpos($fileContent, '});');
    //         if ($position === false) {
    //             // If '});' is not found, try finding the last '}'
    //             $position = strrpos($fileContent, '}');
    //         }

    //         if ($position !== false) {
    //             // Insert the new route before the last '});' or '}'
    //             $newContent = substr_replace($fileContent, PHP_EOL . $routeTemplate . PHP_EOL, $position, 0);

    //             // Write the updated content back to the routes file
    //             File::put($routesFilePath, $newContent);
    //             $this->info("Routes for {$controllerName} added to " . ($isApi ? 'api.php' : 'web.php') . " before the last });");
    //         } else {
    //             // If no closing '});' or '}' is found, append the routes to the file
    //             File::append($routesFilePath, PHP_EOL . $routeTemplate);
    //             $this->info("Routes for {$controllerName} appended to " . ($isApi ? 'api.php' : 'web.php'));
    //         }
    //     } else {
    //         $this->error("Routes file not found at {$routesFilePath}");
    //     }
    // }

    protected function createRoutes($controllerName, $modelName, $namespacePath, $isApi)
    {
        // Set the appropriate route file path (web.php or api.php)
        $routesFilePath = base_path($isApi ? 'routes/api.php' : 'routes/web.php');

        // Load the routes stub
        $routeTemplate = $this->getStub('routes');

        // Replace placeholders in the route template
        $this->replacePlaceholders($routeTemplate, [
            '{{name}}' => Str::snake($modelName),
            '{{controller}}' => $controllerName,
        ]);

        // Check if the routes file exists
        if (File::exists($routesFilePath)) {
            // Read the file content
            $fileContent = File::get($routesFilePath);

            // Check if the new route already exists
            if (strpos($fileContent, $routeTemplate) !== false) {
                $this->warn("The route for '{$controllerName}' already exists.");
                if (!$this->confirm('Do you want to overwrite it?')) {
                    $this->info('Operation cancelled.');
                    return;
                }
            }

            // Handle the namespace path for the controller
            $namespace = "use App\Http\Controllers";
            if (!empty($namespacePath)) {
                // If there is a namespace, append it
                $namespace .= "\\$namespacePath";
            }
            $namespace .= "\\$controllerName;\n";

            // Check if the namespace is already present at the top of the file
            if (strpos($fileContent, $namespace) === false) {
                // Find the position right after the `<?php` opening tag
                $position = strpos($fileContent, "<?php") + strlen("<?php\n");

                // Insert the namespace right after the `<?php` tag
                $fileContent = substr_replace($fileContent, $namespace, $position, 0);
            }

            // Find the last occurrence of '});' or '}'
            $position = strrpos($fileContent, '});');
            if ($position === false) {
                // If '});' is not found, try finding the last '}'
                $position = strrpos($fileContent, '}');
            }

            if ($position !== false) {
                // Insert the new route before the last '});' or '}'
                $newContent = substr_replace($fileContent, PHP_EOL . $routeTemplate . PHP_EOL, $position, 0);

                // Write the updated content back to the routes file
                File::put($routesFilePath, $newContent);
                $this->info("Routes for {$controllerName} added to " . ($isApi ? 'api.php' : 'web.php') . " before the last });");
            } else {
                // If no closing '});' or '}' is found, append the routes to the file
                File::append($routesFilePath, PHP_EOL . $routeTemplate);
                $this->info("Routes for {$controllerName} appended to " . ($isApi ? 'api.php' : 'web.php'));
            }
        } else {
            $this->error("Routes file not found at {$routesFilePath}");
        }
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

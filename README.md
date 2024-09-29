# Laravel CRUD Generator Package

This Laravel package helps to automate the creation of basic CRUD (Create, Read, Update, Delete) functionality, including models, controllers, and migrations. You can also generate API-specific controllers by passing the `--api` option when running the command.
github : https://github.com/nijwel/crud-generator.git
## Features

- Generate Model, Controller, and Migration files for CRUD functionality.
- Supports both web and API-based CRUD methods.
- Automatic namespace generation based on the provided input.
- Customizable stubs for model, controller, and migration templates.

## Installation

To use this package, follow these steps:

### 1. Require the Package

```bash
composer require nijwel/crud-generator
```

### 2. Publish the Stub Files (optional)
If you want to customize the stub templates for your project, publish the stub files:

```bash
php artisan vendor:publish --tag=crud-stubs
```
This will copy the stub files into your stubs directory, where you can customize them to fit your needs.

## Usage

Once the package is installed, you can generate CRUD operations with the following command:

### 1. Generate Standard CRUD (Model, Controller, Migration)
To generate standard CRUD methods (suitable for web applications with views), use:

```bash
php artisan make:crud {ModelName}
```

Example:

```bash
php artisan make:crud Product
```
This will create:
 * A model Product.php in the app/Models/ directory.
 * A controller ProductController.php in the app/Http/Controllers/ directory.
 * A migration file in the database/migrations/ directory.

### 2. Generate API-Specific CRUD
To generate API-based CRUD methods that return JSON responses, add the --api option:

```bash
php artisan make:crud {ModelName} --api
```
Example:
```bash
php artisan make:crud Product --api --m --db --r
```

This will create:

* A model Product.php in the app/Models/ directory.
* An API-specific controller ProductController.php that returns JSON responses.
* A migration file in the database/migrations/ directory.
* Some route content in the routes/web.

Note:
 * if you need only api controller just call --api
 * if you need only model just call --m
 * if you need only DB just call --db
 * if you need only route content just call --r

### 3. Generate CRUD with Namespace
If you want to generate files within a specific namespace, use forward slashes (/) to define the namespace.

This will create:

 * A model Product.php in app/Models/Admin/
 * A controller ProductController.php in app/Http/Controllers/Admin/
 * A migration file for the products table.

## Customizing Stubs
You can customize the stubs used for generating models, controllers, and migrations by publishing the stub files:

```bash
php artisan vendor:publish --tag=crud-stubs
```

The following stubs will be published:

* controller.stub: For standard controllers.
* controller.api.stub: For API controllers.
* model.stub: For models.
* migration.stub: For migration files.

After publishing, you can find and edit the stubs in the stubs directory to suit your needs.

## License
This package is open-source software licensed under the MIT license.

## Contribution
Feel free to fork this package, open issues, or submit pull requests for improvements and bug fixes.


## Credits

Developed by Nijwel.

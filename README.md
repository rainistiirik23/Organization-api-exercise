## Introduction

This here is only a quick setup to get to get the app going, more details on code usage is on the [wiki](https://github.com/rainistiirik23/Organization-api-exercise/wiki).

## Quick setup
Get dependencies with composer.

    Composer update
    
Run migrations.

    php artisan migrate

Optional but it's recommended to run seeders too.

    php artisan seed:DatabaseSeeder
## Api routes

Route for adding organizations and their daughter organizations.

    {App_Url}/api/organization/add

This route retrieves other organizations related to it.

    {App_Url}/api/organization/show

# Dynamic Approval Permission For For Laravel 5.7 or Higher

A Dynamic package for handling model based approval process in Laravel.

- [Installation](#installation)
    - [Composer](#composer)
    - [Service Provider](#service-provider)
    - [Config File](#config-file)
    - [View File](#view-file)
    - [Migration File](#migration-file)
    - [Approvable Trait](#approvable-trait)
    - [Migrations and Seeds](#migrations-and-seeds)
- [Usage](#usage)
    - [Creating Process](#creating-process)    
    - [Blade Extensions](#blade-extensions)    
    - [Middleware](#middleware)
- [Config File](#config-file)
- [Opening an Issue](#opening-an-issue)
- [License](#license)

---

## Installation

This package is very easy to set up. There are only couple of steps.

### Composer

Pull this package in through Composer
```
composer require exceptio/laravel-approval
```

### Service Provider
* Laravel 5.5 and up
Uses package auto discovery feature, no need to edit the `config/app.php` file.

* Laravel 5.4 and below
Add the package to your application service providers in `config/app.php` file.

```php
'providers' => [

    ...

    /**
     * Third Party Service Providers...
     */
    Exceptio\ApprovalPermission\ApprovalPermissionServiceProvider::class,

],
```

### Config File

Publish the package config file to your application. Run these commands inside your terminal.

    php artisan vendor:publish --provider="Exceptio\ApprovalPermission\ApprovalPermissionServiceProvider" --tag="config"
    you may set `approvalpermission-enable` to `false` to disable the feature.
    you may set `do-migration` to `false` in config file to disable the migration.
    Note: database must have all the necessary tables and columns if you disable migration.

### View Files

Publish the view file to your application. Run these commands inside your terminal.

    php artisan vendor:publish --provider="Exceptio\ApprovalPermission\ApprovalPermissionServiceProvider" --tag="views"

### Migration File

Publish the package migration file to your application. Run these commands inside your terminal.

    php artisan vendor:publish --provider="Exceptio\ApprovalPermission\ApprovalPermissionServiceProvider" --tag="migration"

### Approvable Trait

1. Include `Approvable` trait to a class for approval process. See example below.

Example `Approvable` Trait:

```php

<?php

namespace App\Repositories;

use App\Models\User;
use App\Models\Member;

use Exceptio\ApprovalPermission\Approvable;

class MemberRepository
{
    use Approvable;

    const DRAFT_DATA = 0;   // Draft member
    const PENDING_DATA = 1;   // Pending member

    // rest of your code ...
}

```

### Migrations and seeds
> This uses the default users table which is in Laravel. You should already have the migration file for the users table available and migrated.

1. Setup the needed tables:
```
php artisan migrate
```
2. For seeder publish
```
php artisan vendor:publish --provider="Exceptio\ApprovalPermission\ApprovalPermissionServiceProvider" --tag="seeder"
```
3. Seed an initial set of Approval sample data.
```
composer dump-autoload
```
```
php artisan db:seed --class="ApprovalSeeder"
```

### And that's it!

---

## Usage

### Creating Process

```php
<?php
use App\Models\User;
use App\Models\Member;

use Exceptio\ApprovalPermission\Approvable;
class MemberRepository
{
    use Approvable;

    const DRAFT_DATA = 0;   // Draft member
    const PENDING_DATA = 1;   // Pending member
    
    public function createStepFinal(Request $request)
    {
      $member = new Member::create([
        'name' => 'Test Member',
        //so on
      ]);
      $this->notifyApprovalCreate($member);
    }
```

### Blade Extensions

The Blade extensions.

```php
@approvalMenu() //Show Approval menu in your application
```
## Config File
You can change user model name, primary key and other settings in config. Have a look at config file for more information.


## Opening an Issue
Before opening an issue there are a couple of considerations:
* A **star** on this project shows support and is way to say thank you to all the contributors. If you open an issue without a star, *your issue may be closed without consideration.* Thank you for understanding and the support.
* **Read the instructions** and make sure all steps were *followed correctly*.
* **Check** that the issue is not *specific to your development environment* setup.
* **Provide** *duplication steps*.
* **Attempt to look into the issue**, and if you *have a solution, make a pull request*.
* **Show that you have made an attempt** to *look into the issue*.
* **Check** to see if the issue you are *reporting is a duplicate* of a previous reported issue.
* **Following these instructions show me that you have tried.**
* If you have a questions send me an email to zahid@exceptionsolutions.com
* Please be considerate that this is an open source project that I provide to the community for FREE when opening an issue. 

## License
<p xmlns:cc="http://creativecommons.org/ns#" xmlns:dct="http://purl.org/dc/terms/"><a property="dct:title" rel="cc:attributionURL" href="https://github.com/exception-soluitions/laravel-approval">Dynamic Approval Permission For Laravel Project</a> by <a rel="cc:attributionURL dct:creator" property="cc:attributionName" href="https://github.com/exception-soluitions">Exception Solutions</a> is marked with <a href="http://creativecommons.org/publicdomain/zero/1.0?ref=chooser-v1" target="_blank" rel="license noopener noreferrer" style="display:inline-block;">CC0 1.0 Universal
<img style="height:22px!important;margin-left:3px;vertical-align:text-bottom;" src="https://mirrors.creativecommons.org/presskit/icons/cc.svg?ref=chooser-v1"><img style="height:22px!important;margin-left:3px;vertical-align:text-bottom;" src="https://mirrors.creativecommons.org/presskit/icons/zero.svg?ref=chooser-v1"></a></p>

# Defender
----------

Defender is an Access Control List (ACL) Solution for Laravel 5 / 6 / 7 / 8 / 9 (single auth). **(Not compatible with multi-auth)**  
With security and usability in mind, this project aims to provide you a safe way to control your application access without losing the fun of coding.

> Current Build Status

[![Build Status](https://travis-ci.org/artesaos/defender.svg?branch=master)](https://travis-ci.org/artesaos/defender)
[![Code Climate](https://codeclimate.com/github/artesaos/defender/badges/gpa.svg)](https://codeclimate.com/github/artesaos/defender)
[![StyleCI](https://styleci.io/repos/31179862/shield)](https://styleci.io/repos/31179862)

> Statistics

[![Latest Stable Version](https://poser.pugx.org/artesaos/defender/v/stable.svg)](https://packagist.org/packages/artesaos/defender)
[![Latest Unstable Version](https://poser.pugx.org/artesaos/defender/v/unstable.svg)](https://packagist.org/packages/artesaos/defender) [![License](https://poser.pugx.org/artesaos/defender/license.svg)](https://packagist.org/packages/artesaos/defender)
[![Total Downloads](https://poser.pugx.org/artesaos/defender/downloads.svg)](https://packagist.org/packages/artesaos/defender)
[![Monthly Downloads](https://poser.pugx.org/artesaos/defender/d/monthly.png)](https://packagist.org/packages/artesaos/defender)
[![Daily Downloads](https://poser.pugx.org/artesaos/defender/d/daily.png)](https://packagist.org/packages/artesaos/defender)


## Contribution welcome

Defender is looking for maintainers and contributors.

## Table of contents

  * [Installation](#installation)
    + [1. Dependency](#1-dependency)
    + [2. Provider](#2-provider)
    + [3. User Class](#3-user-class)
      - [4. Publishing configuration file and migrations](#4-publishing-configuration-file-and-migrations)
    + [5. Facade (optional)](#5-facade--optional-)
    + [6. Defender Middlewares (optional)](#6-defender-middlewares--optional-)
      - [6.1 - Create your own middleware](#61---create-your-own-middleware)
  * [Usage](#usage)
    + [Creating roles and permissions](#creating-roles-and-permissions)
      - [With commands](#with-commands)
      - [With the seeder or artisan tinker](#with-the-seeder-or-artisan-tinker)
    + [Using the middleware](#using-the-middleware)
      - [Checking Permissions: needsPermissionMiddleware](#checking-permissions--needspermissionmiddleware)
      - [Checking Roles: needsRoleMiddleware](#checking-roles--needsrolemiddleware)
    + [Using in Views](#using-in-views)
      - [@shield](#-shield)
      - [@is](#-is)
      - [Using javascript helper](#using-javascript-helper)
    + [Using the Facade](#using-the-facade)
        * [`Defender::hasPermission($permission)`:](#-defender--haspermission--permission---)
        * [`Defender::canDo($permission)`:](#-defender--cando--permission---)
        * [`Defender::roleHasPermission($permission)`:](#-defender--rolehaspermission--permission---)
        * [`Defender::hasRole($roleName)`:](#-defender--hasrole--rolename---)
        * [`Defender::roleExists($roleName)`:](#-defender--roleexists--rolename---)
        * [`Defender::permissionExists($permissionName)`:](#-defender--permissionexists--permissionname---)
        * [`Defender::findRole($roleName)`:](#-defender--findrole--rolename---)
        * [`Defender::findRoleById($roleId)`:](#-defender--findrolebyid--roleid---)
        * [`Defender::findPermission($permissionName)`:](#-defender--findpermission--permissionname---)
        * [`Defender::findPermissionById($permissionId)`:](#-defender--findpermissionbyid--permissionid---)
        * [`Defender::createRole($roleName)`:](#-defender--createrole--rolename---)
        * [`Defender::createPermission($permissionName)`:](#-defender--createpermission--permissionname---)
        * [`Defender::is($roleName)`:](#-defender--is--rolename---)
        * [`Defender::javascript()->render()`:](#-defender--javascript----render----)
    + [Using the trait](#using-the-trait)
        * [`public function hasPermission($permission)`:](#-public-function-haspermission--permission---)
        * [`public function roleHasPermission($permission)`:](#-public-function-rolehaspermission--permission---)
        * [`public function attachRole($role)`:](#-public-function-attachrole--role---)
        * [`public function detachRole($role)`:](#-public-function-detachrole--role---)
        * [`public function syncRoles(array $roles = array())`:](#-public-function-syncroles-array--roles---array-----)
        * [`public function attachPermission($permission, array $options = array())`:](#-public-function-attachpermission--permission--array--options---array-----)
        * [`public function detachPermission($permission)`:](#-public-function-detachpermission--permission---)
        * [`public function syncPermissions(array $permissions)`:](#-public-function-syncpermissions-array--permissions---)
        * [`public function revokePermissions()`:](#-public-function-revokepermissions----)
        * [`public function revokeExpiredPermissions()`:](#-public-function-revokeexpiredpermissions----)
    + [Temporary permissions](#temporary-permissions)
      - [For example](#for-example)
  * [Using custom Role and Permission models](#using-custom-role-and-permission-models)


## Installation

### 1. Dependency

Using <a href="https://getcomposer.org/" target="_blank">composer</a>, execute the following command to automatically update your `composer.json`, using the corresponding package version:

| Version Constraint     | Package Version |
| ---------------------- | --------------- |
| >= 5.0.\* && <= 5.3.\* | 0.6.\*          |
| ~5.4, ~5.5             | 0.7.\*          |
| >= 5.6.\*              | 0.8.\*          |
| ^6.0                   | 0.9.\*          |
| ^7.0                   | 0.10.\*         |
| ^8.0                   | 0.11.\*         |
| ^9.0                   | 0.12.\*         |

```shell
composer require artesaos/defender
```

or manually update your `composer.json` file

```json
{
    "require": {
        "artesaos/defender": "~0.10.0"
    }
}
```

### 2. Provider

> If you are using Laravel >= 5.5 skip this section since our package support auto-discovery.

You need to update your application configuration in order to register the package, so it can be loaded by Laravel. Just update your `config/app.php` file adding the following code at the end of your `'providers'` section:

```php
// file START ommited
    'providers' => [
        // other providers ommited
        \Artesaos\Defender\Providers\DefenderServiceProvider::class,
    ],
// file END ommited
```

### 3. User Class

On your User class, add the trait `Artesaos\Defender\Traits\HasDefender` to enable the creation of permissions and roles:

```php
<?php

namespace App;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Artesaos\Defender\Traits\HasDefender;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;

class User extends Model implements AuthenticatableContract, CanResetPasswordContract
{
    use Authenticatable, CanResetPassword, HasDefender;
...
```

If you are using laravel 5.2+, there is a small difference:

```php
<?php

namespace App;

use Artesaos\Defender\Traits\HasDefender;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasDefender;
...
```

#### 4. Publishing configuration file and migrations

To publish the default configuration file and database migrations, execute the following command:

```shell
php artisan vendor:publish
```

Execute the migrations, so that the tables on you database are created:

```shell
php artisan migrate
```

You can also publish only the configuration file or the migrations:

```shell
php artisan vendor:publish --tag=config
```
Or
```shell
php artisan vendor:publish --tag=migrations
```

If you already published defender files, but for some reason you want to override previous published files, add the `--force` flag.

### 5. Facade (optional)
In order to use the `Defender` facade, you need to register it on the `config/app.php` file, you can do that the following way:

```php
// config.php file
// file START ommited
    'aliases' => [
        // other Facades ommited
        'Defender' => \Artesaos\Defender\Facades\Defender::class,
    ],
// file END ommited
```

### 6. Defender Middlewares (optional)
If you have to control the access Defender provides middlewares to protect your routes.
If you have to control the access through the Laravel routes, Defender has some built-in middlewares for the trivial tasks. To use them, just put it in your `app/Http/Kernel.php` file.

```php
protected $routeMiddleware = [
    'auth'            => \App\Http\Middleware\Authenticate::class,
    'auth.basic'      => \Illuminate\Auth\Middleware\AuthenticateWithBasicAuth::class,
    'guest'           => \App\Http\Middleware\RedirectIfAuthenticated::class,

    // Access control using permissions
    'needsPermission' => \Artesaos\Defender\Middlewares\NeedsPermissionMiddleware::class,

    // Simpler access control, uses only the groups
    'needsRole' => \Artesaos\Defender\Middlewares\NeedsRoleMiddleware::class
];
```

You'll see how to use the middlewares below.

#### 6.1 - Create your own middleware

If the built-in middlewares doesn't fit your needs, you can make your own by using [Defender's API](#using-the-facade) to control the access.

## Usage

Defender handles only access control. The authentication is still made by Laravel's `Auth`.

**Note: If you are using a different model for your users or has changed the namespace, please update the user_model key on your defender config file**

### Creating roles and permissions

#### With commands

You can use these commands to create the roles and permissions for you application.

```shell
php artisan defender:make:role admin  # creates the role admin
php artisan defender:make:role admin --user=1 # creates the role admin and attaches this role to the user where id=1
php artisan defender:make:permission users.index "List all the users" # creates the permission
php artisan defender:make:permission users.create "Create user" --user=1 # creates the permission and attaches it to user where id=1
php artisan defender:make:permission users.destroy "Delete user" --role=admin # creates the permission and attaches it to the role admin
```

#### With the seeder or artisan tinker

You can also use the Defender's API. You can create a Laravel Seeder or use `php artisan tinker`.

```php

use App\User;

$roleAdmin = Defender::createRole('admin');

// The first parameter is the permission name
// The second is the "friendly" version of the name. (usually for you to show it in your application).
$permission =  Defender::createPermission('user.create', 'Create Users');

// You can assign permission directly to a user.
$user = User::find(1);
$user->attachPermission($permission);

// or you can add the user to a group and that group has the power to rule create users.
$roleAdmin->attachPermission($permission);

// Now this user is in the Administrators group.
$user->attachRole($roleAdmin);
```

### Using the middleware

To protect your routes, you can use the built-in middlewares.

> Defender requires Laravel's Auth, so, use the `auth` middleware before the Defender's middleware that you intend to use.

#### Checking Permissions: needsPermissionMiddleware

```php
Route::get('foo', ['middleware' => ['auth', 'needsPermission'], 'shield' => 'user.create', function()
{
    return 'Yes I can!';
}]);
```

If you're using Laravel 5.1+ it's possible to use Middleware Parameters.

```php
Route::get('foo', ['middleware' => ['auth', 'needsPermission:user.index'], function() {
    return 'Yes I can!';
}]);
```

With this syntax it's also possible to use the middleware within your controllers.

```php
$this->middleware('needsPermission:user.index');
```

You can pass an array of permissions to check on.

```php
Route::get('foo', ['middleware' => ['auth', 'needsPermission'], 'shield' => ['user.index', 'user.create'], function()
{
    return 'Yes I can!';
}]);
```

When using middleware parameters, use a `|` to separate multiple permissions.

```php
Route::get('foo', ['middleware' => ['auth', 'needsPermission:user.index|user.create'], function() {
    return 'Yes I can!';
}]);
```

Or within controllers:

```php
$this->middleware('needsPermission:user.index|user.create');
```

When you pass an array of permissions, the route will be fired only if the user has all the permissions. However, if you want to allow the access to the route when the user has at least one of the permissions, just add `'any' => true`.

```php
Route::get('foo', ['middleware' => ['auth', 'needsPermission'], 'shield' => ['user.index', 'user.create'], 'any' => true, function()
{
    return 'Yes I can!';
}]);
```

Or, with middleware parameters, pass it as the 2nd parameter

```php
Route::get('foo', ['middleware' => ['auth', 'needsPermission:user.index|user.create,true'], function() {
    return 'Yes I can!';
}]);
```

Or within controllers:

```php
$this->middleware('needsPermission:user.index|user.create,true');
```

----------

#### Checking Roles: needsRoleMiddleware

This is similar to the previous middleware, but only the roles are checked, it means that it doesn't check the permissions.

```php
Route::get('foo', ['middleware' => ['auth', 'needsRole'], 'is' => 'admin', function()
{
    return 'Yes I am!';
}]);
```

If you're using Laravel 5.1 it's possible to use Middleware Parameters.

```php
Route::get('foo', ['middleware' => ['auth', 'needsRole:admin'], function() {
    return 'Yes I am!';
}]);
```

With this syntax it's also possible to use the middleware within your controllers.

```php
$this->middleware('needsRole:admin');
```

You can pass an array of permissions to check on.

```php
Route::get('foo', ['middleware' => ['auth', 'needsRole'], 'shield' => ['admin', 'member'], function()
{
    return 'Yes I am!';
}]);
```

When using middleware parameters, use a `|` to separate multiple roles.

```php
Route::get('foo', ['middleware' => ['auth', 'needsRole:admin|editor'], function() {
    return 'Yes I am!';
}]);
```

Or within controllers:

```php
$this->middleware('needsRole:admin|editor');
```

When you pass an array of permissions, the route will be fired only if the user has all the permissions. However, if you want to allow the access to the route when the user has at least one of the permissions, just add `'any' => true`.

```php
Route::get('foo', ['middleware' => ['auth', 'needsRole'], 'is' => ['admin', 'member'], 'any' => true, function()
{
    return 'Yes I am!';
}]);
```

Or, with middleware parameters, pass it as the 2nd parameter

```php
Route::get('foo', ['middleware' => ['auth', 'needsRole:admin|editor,true'], function() {
    return 'Yes I am!';
}]);
```

Or within controllers:

```php
$this->middleware('needsRole:admin|editor,true');
```

----------

### Using in Views

Laravel's Blade extension for using Defender.

#### @shield

```
@shield('user.index')
    shows your protected stuff
@endshield
```

```
@shield('user.index')
    shows your protected stuff
@else
    shows the data for those who doesn't have the user.index permission
@endshield
```

You can also use wildcard(*)

```
@shield('user.*')
    shows your protected stuff
@else
    shows the data for those who doesn't have the any permission with 'user' prefix
@endshield
```

#### @is

```
@is('admin')
    Shows data for the logged user and that belongs to the admin role
@endis
```

```
@is('admin')
    Shows data for the logged user and that belongs to the admin role
@else
    shows the data for those who doesn't have the admin permission
@endis
```

```
@is(['role1', 'role2'])
    Shows data for the logged user and that belongs to the admin role
@else
    shows the data for those who doesn't have the admin permission
@endis
```

#### Using javascript helper

The stand provides helper for when you need to interact with the user permissions on the front-end.

```php
echo Defender::javascript()->render();
// or
echo app('defender')->javascript()->render();
// or
echo app('defender.javascript')->render();
```

This helper injects a javascript code with all permissions and roles of the current user.

----------

### Using the Facade

With the Defender's Facade you can access the API and use it at any part of your application.

----------

##### `Defender::hasPermission($permission)`:

Check if the logged user has the `$permission`.

----------

##### `Defender::canDo($permission)`:

Check if the logged user has the `$permission`. If the role `superuser` returns true

----------

##### `Defender::roleHasPermission($permission)`:

Check if the logged user has the `$permission` checking only the role permissions.

----------

##### `Defender::hasRole($roleName)`:

Check if the logged user belongs to the role `$roleName`.

----------

##### `Defender::roleExists($roleName)`:

Check if the role `$roleName` exists in the database.

----------

##### `Defender::permissionExists($permissionName)`:

Check if the permission `$permissionName` exists in the database.

----------

##### `Defender::findRole($roleName)`:

Find the role in the database by the name `$roleName`.

----------

##### `Defender::findRoleById($roleId)`:

Find the role in the database by the role ID `roleId`.

----------

##### `Defender::findPermission($permissionName)`:

Find the permission in the database by the name `$permissionName`.

----------

##### `Defender::findPermissionById($permissionId)`:

Find the permission in the database by the ID `$permissionId`.

----------

##### `Defender::createRole($roleName)`:

Create a new role in the database.

----------

##### `Defender::createPermission($permissionName)`:

Create a new permission in the database.

##### `Defender::is($roleName)`:

Check whether the current user belongs to the role.

##### `Defender::javascript()->render()`:

Returns a javascript script with a list of all roles and permissions of the current user.
The variable name can be modified.

----------

### Using the trait

To add the Defender's features, you need to add the trait `HasDefender` in you User model (usually `App\User`).

```php
<?php namespace App;

// Declaration of other omitted namespaces
use Artesaos\Defender\Traits\HasDefender;

class User extends Model implements AuthenticatableContract, CanResetPasswordContract {

    use Authenticatable, CanResetPassword, HasDefender;

    // Rest of the class
}
```

This trait, beyond configuring the relationships, will add the following methods to your object `App\User`:

##### `public function hasPermission($permission)`:

This method checks if the logged user has the permission `$permission`

In Defender, there are 2 kind of permissions: `User permissions` and `Role permissions`. By default, the permissions that the user inherits, are permissions of the roles that it belongs to. However, always that a user pemission is set, it will take precedence of role permission.

```php
public function foo(Authenticable $user)
{
    if ($user->hasPermission('user.create'));
}
```

----------

##### `public function roleHasPermission($permission)`:

This method works the same way the previous one, the only diference is that the user permissions are not considered, however, only the role's permissions that the user belongs are used to check the access.

```php
public function foo(Authenticable $user)
{
    if ($user->roleHasPermission('user.create');
}
```

----------

##### `public function attachRole($role)`:

Attach the user to the role `$role`. The `$role` variable might be an object of the type `Artesaos\Defender\Role` or an array containing the `ids` of the roles.

```php
public function foo(Authenticable $user)
{
    $role = Defender::findRole('admin'); // Returns an Artesao\Defender\Role
    $user->attachRole($role);

    // or

    $roles = [1, 2, 3]; // Using an array of ids
    $user->attachRole($roles);
}
```

----------


##### `public function detachRole($role)`:

Detach the role `$role` from the user (inverse to `attachRole()`).

```php
public function foo(Authenticable $user)
{
    $role = Defender::findRole('admin'); // Returns an Artesao\Defender\Role
    $user->detachRole($role);

    // ou

    $roles = [1, 2, 3]; // Using an array of ids
    $user->detachRole($roles);
}
```

----------

##### `public function syncRoles(array $roles = array())`:

This is like the `attachRole()` method, but only the roles in the array `$roles` will be on the relationship after the method runs. `$roles` is an array of `ids` for the needed roles.

```php
public function foo(Authenticable $user)
{
    $roles = [1, 2, 3]; // Using an array of ids

    $user->syncRoles($roles);
}
```

----------

##### `public function attachPermission($permission, array $options = array())`:

Attach the user to the permission `$permission`. The `$permission` variable is an instance of the `Artesaos\Defender\Permission` class.

```php
public function foo(Authenticable $user)
{
    $permission = Defender::findPermission('user.create');

    $user->attachPermission($permission, [
        'value' => true // true = has the permission, false = doesn't have the permission,
    ]);
}
```

----------

##### `public function detachPermission($permission)`:

Remove the permission `$permission` from the user. The `$permission` variable might be an instance of the `Artesaos\Defender\Permission` class or an array of `ids` with the ids of the permissions to be removed.

```php
public function foo(Authenticable $user)
{
    $permission = Defender::findPermission('user.create');
    $user->detachPermission($permission);

    // or

    $permissions = [1, 3];
    $user->detachPermission($permissions);
}
```

----------

##### `public function syncPermissions(array $permissions)`:

This is like the method `syncRoles`, but only the roles in the array `$permissions` be on the relationship after the method runs.

```php
public function foo(Authenticable $user)
{
    $permissions = [
        1 => ['value' => false],
        2 => ['value' => true,
        3 => ['value' => true]
    ];

    $user->syncPermissions($permissions);
}
```

----------

##### `public function revokePermissions()`:

Remove all the user permissions.

```php
public function foo(Authenticable $user)
{
    $user->revokePermissions();
}
```

----------

##### `public function revokeExpiredPermissions()`:

Remove all the temporary expired pemissions from the user. More about temporary permissions below.

```php
public function foo(Authenticable $user)
{
    $user->revokeExpiredPermissions();
}
```

----------

### Temporary permissions

One of Defender's coolest features is to add temporary permissions to a group or an user.

#### For example

> *The user John belongs to the role 'admins', however I want to temporaly remove the John's permission to create new users*

In this case we need to attach an permission with the value equal to `false`, explicitly prohibiting the user to perform that action. You must add this permission, with the `false` value, since by default, the user permissions are inherited of the permissions of their roles. When you assign a user permission, this will always take precedence.

For instance. Below we revoke the permission `user.create` for the user during 7 days.

```php
public function foo()
{
    $userX = App\User::find(3);
    $permission = Defender::findPermission('user.create');


    $userX->attachPermission($permission, [
        'value' => false, // false means that he will not have the permission,
        'expires' => \Carbon\Carbon::now()->addDays(7) // Set the permission's expiration date
    ]);

}
```

After 7 days, the user will take the permission again.

----------

> *Allow that a user can perform some action by a period of time.*

To allow that a user have temporary access to perform a given action, just set the `expires` key. The `value` key will be `true` by default.

```php
public function foo()
{
    $user = App\User::find(1);
    $permission = Defender::findPermission('user.create');

    $user->attachPermission($permission, [
        'expires' => \Carbon\Carbon::now()->addDays(7)
    ];
}
```

It's also possible to extend an existing temporary:
Just use the `$user->extendPermission($permissionName, array $options)` method.

## Using custom Role and Permission models

To use your own classes for Role and Permission models, first set the `role_model` and `permission_model` keys at `defender.php` config.

Following are two examples of how Role and Permission models must be implemented for MongoDB using [jenssegers/laravel-mongodb](https://github.com/jenssegers/laravel-mongodb) driver:

```php
    <?php
    
    // Role model
    
    namespace App;
    
    use Jenssegers\Mongodb\Eloquent\Model;
    use Artesaos\Defender\Traits\Models\Role;
    use Artesaos\Defender\Contracts\Role as RoleInterface;
    
    /**
     * Class Role.
     */
    class Role extends Model implements RoleInterface {
        use Role;
    }
```

```php
    <?php
    
    // Permission model
    
    namespace App;
    
    use Jenssegers\Mongodb\Eloquent\Model;
    use Artesaos\Defender\Traits\Models\Permission;
    use Artesaos\Defender\Contracts\Permission as PermissionInterface;
    
    /**
     * Class Permission.
     */
    class Permission extends Model implements PermissionInterface
    {
        use Permission;    
    }
```

You must use the correct traits and each class has to implemet the corresponding interface contract.

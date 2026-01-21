# Laravel Enum Permissions

[![PHP Version](https://img.shields.io/badge/PHP-8.3%2B-blue.svg)]()
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](LICENSE.txt)

Type-safe permission management system for Laravel

## Installation

```bash
composer require din9xtr/laravel-enum-permissions
```

## Quick Start

### Create Migration

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->json('permissions')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('permissions');
        });
    }
};

```

### Create Permission Enum

```php
<?php

namespace Din9xtr\LaravelEnumPermissions\Example\Enums;

use Din9xtr\LaravelEnumPermissions\Contracts\PackagePermissionInterface;
use Din9xtr\LaravelEnumPermissions\Contracts\PermissionInterface;

enum PermissionEnum: string implements PermissionInterface
{
    case CREATE = 'create';
    case READ = 'read';
    case UPDATE = 'update';
    case DELETE = 'delete';
    case EXPORT = 'export';
    case IMPORT = 'import';

    public static function all(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function getPackagePermissions(PackagePermissionInterface $package): array
    {
        return match ($package) {
            PackageEnum::ADMIN => [
                self::CREATE,
                self::READ,
                self::UPDATE,
                self::DELETE,
                self::EXPORT,
                self::IMPORT,
            ],
            PackageEnum::EDITOR => [
                self::CREATE,
                self::READ,
                self::UPDATE,
                self::EXPORT,
            ],
            PackageEnum::VIEWER => [
                self::READ,
                self::EXPORT,
            ],
            PackageEnum::CUSTOM => [],
        };
    }
}
```

### Create Package Enum (optional)

```php
<?php

namespace Din9xtr\LaravelEnumPermissions\Example\Enums;

use Din9xtr\LaravelEnumPermissions\Contracts\PackagePermissionInterface;

enum PackageEnum: string implements PackagePermissionInterface
{
    case ADMIN = 'admin';
    case EDITOR = 'editor';
    case VIEWER = 'viewer';
    case CUSTOM = 'custom';
}
```

### Configure model

```php
<?php

declare(strict_types=1);

namespace Din9xtr\LaravelEnumPermissions\Example;

use Din9xtr\LaravelEnumPermissions\Casts\PermissionsCast;
use Din9xtr\LaravelEnumPermissions\Contracts\HasPackagePermissionEnum;
use Din9xtr\LaravelEnumPermissions\Contracts\HasPermissionEnum;
use Din9xtr\LaravelEnumPermissions\Example\Enums\PackageEnum;
use Din9xtr\LaravelEnumPermissions\Example\Enums\PermissionEnum;
use Din9xtr\LaravelEnumPermissions\Traits\HasPermissionsTrait;
use Illuminate\Database\Eloquent\Model;

class User extends Model implements HasPermissionEnum, HasPackagePermissionEnum
{
    use HasPermissionsTrait;

    protected string $permissionsAttribute = 'permissions';

    protected $casts = [
        'permissions' => PermissionsCast::class,
    ];

    public static function permissionEnum(): string
    {
        return PermissionEnum::class;
    }

    public static function packageEnum(): string
    {
        return PackageEnum::class;
    }
}
```

### Usage Examples

```php
$user = User::find(1);
// Check if user has specific permission
if ($user->hasPermission(PermissionEnum::CREATE)) {
    // User can create
}
// Apply a package
$user->applyPackage(PackageEnum::ADMIN)->save();
// Detect which package user currently has
$package = $user->detectPackage(); // Returns PackageEnum enum
```

## Requirements

PHP 8.3 or higher

Laravel 11.x

Database with JSON column support

## License

This project is open-source and available under the **[MIT License](LICENSE)**.

Copyright © 2026 Din9xtr


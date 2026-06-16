# Laravel Enum Permissions

[![PHP Version](https://img.shields.io/badge/PHP-8.3%2B-blue.svg)]()
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](LICENSE)

Type-safe permission management system for Laravel models using PHP backed enums.

## Installation

```bash
composer require din9xtr/laravel-enum-permissions
```

## Storage formats

The package supports two storage formats:

- JSON: default and backward compatible with `PermissionsCast`.
- HEX: compact bitmask storage with `HexPermissionsCast`.

Existing users can keep `PermissionsCast::class` unchanged. New projects may choose HEX explicitly in the model cast.

## Migrations

### JSON

Use JSON when readability and direct database inspection matter.

```php
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

JSON is stored as an object keyed by permission enum values:

```json
{
    "create": true,
    "read": true,
    "update": false
}
```

### HEX

Use HEX when compact storage is preferred.

```php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('permissions', 64)->nullable();
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

HEX is stored as a deterministic bitmask. Bit `0` maps to the first `PermissionEnum::cases()` item, bit `1` maps to the second item, and so on. For example, if `CREATE`, `READ`, and `EXPORT` are enabled, the stored value is `13`.

For HEX storage, the order of permission enum cases is part of the persisted data format. Append new permissions to the enum when possible. Reordering or inserting cases in the middle changes the meaning of existing stored HEX values and requires a data migration.

## Enums

```php
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

```php
use Din9xtr\LaravelEnumPermissions\Contracts\PackagePermissionInterface;

enum PackageEnum: string implements PackagePermissionInterface
{
    case ADMIN = 'admin';
    case EDITOR = 'editor';
    case VIEWER = 'viewer';
    case CUSTOM = 'custom';
}
```

`PackageEnum` is optional unless you use `applyPackage()` or `detectPackage()`. When used with the trait, it must define a `CUSTOM` case.

## Model configuration

### JSON cast

```php
use Din9xtr\LaravelEnumPermissions\Casts\PermissionsCast;
use Din9xtr\LaravelEnumPermissions\Contracts\HasPackagePermissionEnum;
use Din9xtr\LaravelEnumPermissions\Contracts\HasPermissionEnum;
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

### HEX cast

```php
use Din9xtr\LaravelEnumPermissions\Casts\HexPermissionsCast;

protected $casts = [
    'permissions' => HexPermissionsCast::class,
];
```

## Usage

```php
$user = User::find(1);

if ($user->hasPermission(PermissionEnum::CREATE)) {
    // User can create.
}

$user->applyPackage(PackageEnum::ADMIN)->save();

$package = $user->detectPackage();
```

The cast returns a `PermissionsCollection`:

```php
$permissions = $user->permissions;

$permissions->has(PermissionEnum::READ);
$permissions->enabled();
$permissions->toArray();
```

## Compatibility and edge cases

- `PermissionsCast` keeps the original JSON behavior and remains the default recommendation for existing projects.
- `HexPermissionsCast` is opt-in and does not read JSON values automatically.
- `null`, an empty JSON value, an empty HEX value, `0`, and `0x0` are treated as all permissions disabled.
- Unknown JSON permission keys are ignored on read and are not written back.
- HEX bits beyond the current enum cases are ignored on read and are not written back.
- Invalid HEX strings are treated as `0`.
- Missing known permissions are normalized to `false`.

## Requirements

- PHP 8.3 or higher
- Laravel 11.x components

## Development

```bash
composer install
vendor/bin/pint
vendor/bin/phpstan analyse
vendor/bin/phpunit
composer validate
composer archive --format=zip
```

The Composer archive excludes tests and development tooling, so production installs receive only the library files and package metadata.

## License

This project is open-source and available under the [MIT License](LICENSE).

Copyright © 2026 Din9xtr

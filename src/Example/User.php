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
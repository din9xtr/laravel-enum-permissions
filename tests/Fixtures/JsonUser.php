<?php

declare(strict_types=1);

namespace Din9xtr\LaravelEnumPermissions\Tests\Fixtures;

use Din9xtr\LaravelEnumPermissions\Casts\PermissionsCast;
use Din9xtr\LaravelEnumPermissions\Contracts\HasPackagePermissionEnum;
use Din9xtr\LaravelEnumPermissions\Contracts\HasPermissionEnum;
use Din9xtr\LaravelEnumPermissions\Traits\HasPermissionsTrait;
use Illuminate\Database\Eloquent\Model;

final class JsonUser extends Model implements HasPackagePermissionEnum, HasPermissionEnum
{
    use HasPermissionsTrait;

    public $timestamps = false;

    /** @var array<string, string> */
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

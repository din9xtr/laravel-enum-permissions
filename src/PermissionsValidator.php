<?php
declare(strict_types=1);

namespace Din9xtr\LaravelEnumPermissions;

use BackedEnum;
use Din9xtr\LaravelEnumPermissions\Contracts\PackagePermissionInterface;
use Din9xtr\LaravelEnumPermissions\Contracts\PermissionInterface;
use LogicException;

final class PermissionsValidator
{
    public static function validatePermissionEnum(string $enum): void
    {
        self::assertEnum($enum);
        self::assertBackedEnum($enum);
        self::assertImplements($enum, PermissionInterface::class);
    }

    public static function validatePackageEnum(string $enum): void
    {
        self::assertEnum($enum);
        self::assertBackedEnum($enum);
        self::assertImplements($enum, PackagePermissionInterface::class);

        if (!defined("$enum::CUSTOM")) {
            throw new LogicException("$enum must define a CUSTOM case");
        }
    }

    private static function assertEnum(string $enum): void
    {
        if (!enum_exists($enum)) {
            throw new LogicException("$enum is not an enum");
        }
    }

    private static function assertBackedEnum(string $enum): void
    {
        if (!is_subclass_of($enum, BackedEnum::class)) {
            throw new LogicException("$enum must be a BackedEnum");
        }
    }

    private static function assertImplements(string $enum, string $interface): void
    {
        if (!in_array($interface, class_implements($enum), true)) {
            throw new LogicException(
                "$enum must implement $interface"
            );
        }
    }
}
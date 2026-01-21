<?php

namespace Din9xtr\LaravelEnumPermissions\Example;

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
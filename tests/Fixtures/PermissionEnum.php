<?php

declare(strict_types=1);

namespace Din9xtr\LaravelEnumPermissions\Tests\Fixtures;

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

    /**
     * @return list<string>
     */
    public static function all(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * @return list<self>
     */
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
            default => [],
        };
    }
}

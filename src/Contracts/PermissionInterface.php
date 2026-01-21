<?php

namespace Din9xtr\LaravelEnumPermissions\Contracts;

use BackedEnum;

interface PermissionInterface extends BackedEnum
{
    /**
     * @return string[]
     */
    public static function all(): array;

    /**
     * @return array<PackagePermissionInterface>
     */
    public static function getPackagePermissions(
        PackagePermissionInterface $package
    ): array;

}

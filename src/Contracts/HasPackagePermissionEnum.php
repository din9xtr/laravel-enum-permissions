<?php


namespace Din9xtr\LaravelEnumPermissions\Contracts;

interface HasPackagePermissionEnum
{
    /**
     * @return class-string<PackagePermissionInterface>
     */
    public static function packageEnum(): string;
}

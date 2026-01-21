<?php

namespace Din9xtr\LaravelEnumPermissions\Contracts;

interface HasPermissionEnum
{
    /**
     * @return class-string<PermissionInterface>
     */
    public static function permissionEnum(): string;
}

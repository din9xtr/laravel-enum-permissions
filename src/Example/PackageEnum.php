<?php

namespace Din9xtr\LaravelEnumPermissions\Example;

use Din9xtr\LaravelEnumPermissions\Contracts\PackagePermissionInterface;

enum PackageEnum: string implements PackagePermissionInterface
{
    case ADMIN = 'admin';
    case EDITOR = 'editor';
    case VIEWER = 'viewer';
    case CUSTOM = 'custom';
}
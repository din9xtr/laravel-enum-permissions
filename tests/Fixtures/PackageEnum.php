<?php

declare(strict_types=1);

namespace Din9xtr\LaravelEnumPermissions\Tests\Fixtures;

use Din9xtr\LaravelEnumPermissions\Contracts\PackagePermissionInterface;

enum PackageEnum: string implements PackagePermissionInterface
{
    case ADMIN = 'admin';
    case EDITOR = 'editor';
    case VIEWER = 'viewer';
    case CUSTOM = 'custom';
}

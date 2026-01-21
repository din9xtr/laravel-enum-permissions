<?php

declare(strict_types=1);

namespace Din9xtr\LaravelEnumPermissions\Traits;

use Din9xtr\LaravelEnumPermissions\Collections\PermissionsCollection;
use Din9xtr\LaravelEnumPermissions\Contracts\PackagePermissionInterface;
use Din9xtr\LaravelEnumPermissions\Contracts\PermissionInterface;
use Din9xtr\LaravelEnumPermissions\PermissionsValidator;

trait HasPermissionsTrait
{
    protected function getPermissionsAttributeName(): string
    {
        if (property_exists($this, 'permissionsAttribute')) {
            return $this->permissionsAttribute;
        }

        return 'permissions';
    }

    private function getPackageEnum(): string
    {
        $enum = static::packageEnum();

        PermissionsValidator::validatePackageEnum($enum);

        return $enum;
    }

    private function getPermissionEnum(): string
    {
        $enum = static::permissionEnum();

        PermissionsValidator::validatePermissionEnum($enum);

        return $enum;
    }

    public function hasPermission(PermissionInterface $permission): bool
    {
        /** @var PermissionsCollection $permissions */
        $permissions = $this->getAttribute($this->getPermissionsAttributeName());

        return $permissions->has($permission);
    }

    public function applyPackage(PackagePermissionInterface $package): static
    {
        /** @var class-string<PermissionInterface> $enum */
        $enum = $this->getPermissionEnum();

        $permissions = array_fill_keys(
            array_map(fn($e) => $e->value, $enum::cases()),
            false
        );

        foreach ($enum::getPackagePermissions($package) as $permission) {
            $permissions[$permission->value] = true;
        }

        $this->setAttribute($this->getPermissionsAttributeName(), $permissions);
        return $this;
    }

    public function detectPackage(): PackagePermissionInterface
    {
        /** @var class-string<PackagePermissionInterface> $packageEnum */
        $packageEnum = $this->getPackageEnum();
        $enabled = $this->getAttribute($this->getPermissionsAttributeName())->enabled()->all();

        if ($enabled === []) {
            return $packageEnum::CUSTOM;
        }

        foreach ($packageEnum::cases() as $package) {
            if ($this->matchesPackage($package, $enabled)) {
                return $package;
            }
        }

        return $packageEnum::CUSTOM;
    }

    private function matchesPackage(
        PackagePermissionInterface $package,
        array $enabled
    ): bool {
        $enabledValues = [];
        foreach ($enabled as $permission) {
            $enabledValues[$permission->value] = true;
        }
        /** @var class-string<PermissionInterface> $permissionEnum */
        $permissionEnum = $this->getPermissionEnum();

        $packagePermissions = $permissionEnum::getPackagePermissions($package);

        if (count($enabledValues) !== count($packagePermissions)) {
            return false;
        }

        foreach ($packagePermissions as $permission) {
            if (!isset($enabledValues[$permission->value])) {
                return false;
            }
        }

        return true;
    }
}

<?php

declare(strict_types=1);

namespace Din9xtr\LaravelEnumPermissions\Traits;

use Din9xtr\LaravelEnumPermissions\Collections\PermissionsCollection;
use Din9xtr\LaravelEnumPermissions\Contracts\PackagePermissionInterface;
use Din9xtr\LaravelEnumPermissions\Contracts\PermissionInterface;
use Din9xtr\LaravelEnumPermissions\PermissionsValidator;
use Din9xtr\LaravelEnumPermissions\Support\PermissionsStorage;

trait HasPermissionsTrait
{
    protected function getPermissionsAttributeName(): string
    {
        if (property_exists($this, 'permissionsAttribute')) {
            $attribute = $this->permissionsAttribute;

            return is_string($attribute) ? $attribute : 'permissions';
        }

        return 'permissions';
    }

    /**
     * @return class-string<PackagePermissionInterface>
     */
    private function getPackageEnum(): string
    {
        $enum = static::packageEnum();

        PermissionsValidator::validatePackageEnum($enum);

        /** @var class-string<PackagePermissionInterface> $enum */
        return $enum;
    }

    /**
     * @return class-string<PermissionInterface>
     */
    private function getPermissionEnum(): string
    {
        $enum = static::permissionEnum();

        PermissionsValidator::validatePermissionEnum($enum);

        /** @var class-string<PermissionInterface> $enum */
        return $enum;
    }

    public function hasPermission(PermissionInterface $permission): bool
    {
        return $this->getPermissionsCollection()->has($permission);
    }

    public function applyPackage(PackagePermissionInterface $package): static
    {
        $enum = $this->getPermissionEnum();

        $permissions = array_fill_keys(
            array_map(fn (PermissionInterface $permission) => $permission->value, $enum::cases()),
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
        $packageEnum = $this->getPackageEnum();
        $enabled = $this->getPermissionsCollection()->enabled()->all();

        if ($enabled === []) {
            return $this->customPackage($packageEnum);
        }

        foreach ($packageEnum::cases() as $package) {
            if ($this->matchesPackage($package, array_values($enabled))) {
                return $package;
            }
        }

        return $this->customPackage($packageEnum);
    }

    private function getPermissionsCollection(): PermissionsCollection
    {
        $permissions = $this->getAttribute($this->getPermissionsAttributeName());

        if ($permissions instanceof PermissionsCollection) {
            return $permissions;
        }

        $enum = $this->getPermissionEnum();

        return new PermissionsCollection(
            PermissionsStorage::normalizeValue($permissions, $enum),
            $enum
        );
    }

    /**
     * @param  class-string<PackagePermissionInterface>  $packageEnum
     */
    private function customPackage(string $packageEnum): PackagePermissionInterface
    {
        $custom = constant($packageEnum.'::CUSTOM');

        if (! $custom instanceof PackagePermissionInterface) {
            throw new \LogicException("$packageEnum::CUSTOM must be a package permission");
        }

        return $custom;
    }

    /**
     * @param  list<PermissionInterface>  $enabled
     */
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
            if (! isset($enabledValues[$permission->value])) {
                return false;
            }
        }

        return true;
    }
}

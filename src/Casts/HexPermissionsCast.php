<?php

declare(strict_types=1);

namespace Din9xtr\LaravelEnumPermissions\Casts;

use Din9xtr\LaravelEnumPermissions\Collections\PermissionsCollection;
use Din9xtr\LaravelEnumPermissions\Support\PermissionsStorage;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;

/**
 * @implements CastsAttributes<PermissionsCollection, array<string, bool>|PermissionsCollection|null>
 */
final class HexPermissionsCast implements CastsAttributes
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function get($model, string $key, $value, array $attributes): PermissionsCollection
    {
        $enum = PermissionsStorage::permissionEnum($model);

        return PermissionsStorage::collectionFromHex($value, $enum);
    }

    /**
     * @param  array<string, mixed>  $attributes
     * @return array<string, string>
     */
    public function set($model, string $key, $value, array $attributes): array
    {
        $enum = PermissionsStorage::permissionEnum($model);
        $normalized = PermissionsStorage::normalizeValue($value, $enum);

        return [
            $key => PermissionsStorage::encodeHex($normalized, $enum),
        ];
    }
}

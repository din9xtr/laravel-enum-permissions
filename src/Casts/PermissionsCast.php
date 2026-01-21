<?php
declare(strict_types=1);

namespace Din9xtr\LaravelEnumPermissions\Casts;

use Din9xtr\LaravelEnumPermissions\Collections\PermissionsCollection;
use BackedEnum;
use Din9xtr\LaravelEnumPermissions\Contracts\HasPermissionEnum;
use Din9xtr\LaravelEnumPermissions\Contracts\PermissionInterface;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use LogicException;

final class PermissionsCast implements CastsAttributes
{
    private function permissionEnum($model): string
    {
        if (!$model instanceof HasPermissionEnum) {
            throw new LogicException(
                sprintf(
                    '%s must implement %s',
                    $model::class,
                    HasPermissionEnum::class
                )
            );
        }

        $enum = $model::permissionEnum();

        $this->validatePermissionEnum($enum);

        return $enum;
    }

    private function validatePermissionEnum(string $enum): void
    {
        if (!enum_exists($enum)) {
            throw new LogicException("$enum is not an enum");
        }

        if (!is_subclass_of($enum, BackedEnum::class)) {
            throw new LogicException("$enum must be a BackedEnum");
        }

        if (!in_array(
            PermissionInterface::class,
            class_implements($enum),
            true
        )) {
            throw new LogicException(
                "$enum must implement " . PermissionInterface::class
            );
        }
    }

    public function get($model, string $key, $value, array $attributes): PermissionsCollection
    {

        $enum = $this->permissionEnum($model);

        $decoded = is_string($value) ? json_decode($value, true) : $value;
        $decoded = is_array($decoded) ? $decoded : [];

        $normalized = [];

        /** @var PermissionInterface $enum */
        foreach ($enum::cases() as $permission) {
            $normalized[$permission->value] = (bool)($decoded[$permission->value] ?? false);
        }

        return new PermissionsCollection($normalized, $enum);
    }

    public function set($model, string $key, $value, array $attributes): array
    {
        /** @var PermissionInterface $enum */
        $enum = $this->permissionEnum($model);

        if ($value instanceof PermissionsCollection) {
            $value = $value->toArray();
        }

        $normalized = [];
        foreach ($enum::cases() as $permission) {
            $normalized[$permission->value] = (bool)($value[$permission->value] ?? false);
        }

        return [
            $key => json_encode($normalized, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT),
        ];
    }
}

<?php

declare(strict_types=1);

namespace Din9xtr\LaravelEnumPermissions\Support;

use BackedEnum;
use Din9xtr\LaravelEnumPermissions\Collections\PermissionsCollection;
use Din9xtr\LaravelEnumPermissions\Contracts\HasPermissionEnum;
use Din9xtr\LaravelEnumPermissions\Contracts\PermissionInterface;
use Din9xtr\LaravelEnumPermissions\PermissionsValidator;
use LogicException;
use Stringable;

final class PermissionsStorage
{
    /**
     * @return class-string<PermissionInterface>
     */
    public static function permissionEnum(object $model): string
    {
        if (! $model instanceof HasPermissionEnum) {
            throw new LogicException(
                sprintf(
                    '%s must implement %s',
                    $model::class,
                    HasPermissionEnum::class,
                ),
            );
        }

        $enum = $model::permissionEnum();

        PermissionsValidator::validatePermissionEnum($enum);

        /** @var class-string<PermissionInterface> $enum */
        return $enum;
    }

    /**
     * @param  class-string<PermissionInterface>  $enum
     */
    public static function collectionFromJson(mixed $value, string $enum): PermissionsCollection
    {
        $decoded = is_string($value) ? json_decode($value, true) : $value;
        $decoded = is_array($decoded) ? $decoded : [];

        return new PermissionsCollection(self::normalizeArray($decoded, $enum), $enum);
    }

    /**
     * @param  class-string<PermissionInterface>  $enum
     */
    public static function collectionFromHex(mixed $value, string $enum): PermissionsCollection
    {
        $hex = self::normalizeHex($value);
        $normalized = [];

        foreach (self::permissionCases($enum) as $index => $permission) {
            $normalized[(string) $permission->value] = self::isHexBitSet($hex, $index);
        }

        return new PermissionsCollection($normalized, $enum);
    }

    /**
     * @param  class-string<PermissionInterface>  $enum
     * @return array<string, bool>
     */
    public static function normalizeValue(mixed $value, string $enum): array
    {
        if ($value instanceof PermissionsCollection) {
            $value = $value->toArray();
        }

        return self::normalizeArray(is_array($value) ? $value : [], $enum);
    }

    /**
     * @param  array<array-key, mixed>  $value
     * @param  class-string<PermissionInterface>  $enum
     * @return array<string, bool>
     */
    public static function normalizeArray(array $value, string $enum): array
    {
        $normalized = [];

        foreach (self::permissionCases($enum) as $permission) {
            $normalized[(string) $permission->value] = (bool) ($value[$permission->value] ?? false);
        }

        return $normalized;
    }

    /**
     * @param  array<string, bool>  $permissions
     */
    public static function encodeJson(array $permissions): string
    {
        $json = json_encode($permissions, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

        return $json === false ? '{}' : $json;
    }

    /**
     * @param  array<string, bool>  $permissions
     * @param  class-string<PermissionInterface>  $enum
     */
    public static function encodeHex(array $permissions, string $enum): string
    {
        $cases = self::permissionCases($enum);

        if ($cases === []) {
            return '0';
        }

        $nibbles = array_fill(0, intdiv(count($cases) - 1, 4) + 1, 0);

        foreach ($cases as $index => $permission) {
            if (! ($permissions[(string) $permission->value] ?? false)) {
                continue;
            }

            $nibbleIndex = intdiv($index, 4);
            $nibbles[$nibbleIndex] |= 1 << ($index % 4);
        }

        $hex = '';
        for ($i = count($nibbles) - 1; $i >= 0; $i--) {
            $hex .= dechex($nibbles[$i]);
        }

        return ltrim($hex, '0') ?: '0';
    }

    public static function normalizeHex(mixed $value): string
    {
        if ($value === null || $value === '') {
            return '0';
        }

        if (! is_scalar($value) && ! $value instanceof Stringable) {
            return '0';
        }

        $hex = strtolower(trim((string) $value));

        if (str_starts_with($hex, '0x')) {
            $hex = substr($hex, 2);
        }

        if ($hex === '' || ! ctype_xdigit($hex)) {
            return '0';
        }

        return ltrim($hex, '0') ?: '0';
    }

    private static function isHexBitSet(string $hex, int $bit): bool
    {
        $reversed = strrev($hex);
        $nibble = $reversed[intdiv($bit, 4)] ?? '0';

        return (hexdec($nibble) & (1 << ($bit % 4))) !== 0;
    }

    /**
     * @param  class-string<PermissionInterface>  $enum
     * @return list<PermissionInterface&BackedEnum>
     */
    private static function permissionCases(string $enum): array
    {
        /** @var list<PermissionInterface&BackedEnum> $cases */
        $cases = $enum::cases();

        return $cases;
    }
}

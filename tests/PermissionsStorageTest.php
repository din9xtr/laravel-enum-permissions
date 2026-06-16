<?php

declare(strict_types=1);

namespace Din9xtr\LaravelEnumPermissions\Tests;

use Din9xtr\LaravelEnumPermissions\Collections\PermissionsCollection;
use Din9xtr\LaravelEnumPermissions\Tests\Fixtures\HexUser;
use Din9xtr\LaravelEnumPermissions\Tests\Fixtures\JsonUser;
use Din9xtr\LaravelEnumPermissions\Tests\Fixtures\PackageEnum;
use Din9xtr\LaravelEnumPermissions\Tests\Fixtures\PermissionEnum;
use PHPUnit\Framework\TestCase;

final class PermissionsStorageTest extends TestCase
{
    public function test_it_reads_json_permissions(): void
    {
        $user = new JsonUser;
        $user->setRawAttributes([
            'permissions' => '{"create":true,"read":false,"unknown":true}',
        ]);

        $permissions = $user->getAttribute('permissions');

        self::assertInstanceOf(PermissionsCollection::class, $permissions);
        self::assertTrue($permissions->has(PermissionEnum::CREATE));
        self::assertFalse($permissions->has(PermissionEnum::READ));
        self::assertFalse($permissions->has(PermissionEnum::UPDATE));
    }

    public function test_it_writes_json_permissions(): void
    {
        $user = new JsonUser;

        $user->setAttribute('permissions', [
            'create' => true,
            'read' => true,
            'unknown' => true,
        ]);

        $storedPermissions = $user->getAttributes()['permissions'];
        self::assertIsString($storedPermissions);

        self::assertSame([
            'create' => true,
            'read' => true,
            'update' => false,
            'delete' => false,
            'export' => false,
            'import' => false,
        ], json_decode($storedPermissions, true));
    }

    public function test_it_reads_hex_permissions(): void
    {
        $user = new HexUser;
        $user->setRawAttributes([
            'permissions' => '0x13',
        ]);

        $permissions = $user->getAttribute('permissions');

        self::assertInstanceOf(PermissionsCollection::class, $permissions);
        self::assertTrue($permissions->has(PermissionEnum::CREATE));
        self::assertTrue($permissions->has(PermissionEnum::READ));
        self::assertFalse($permissions->has(PermissionEnum::UPDATE));
        self::assertFalse($permissions->has(PermissionEnum::DELETE));
        self::assertTrue($permissions->has(PermissionEnum::EXPORT));
        self::assertFalse($permissions->has(PermissionEnum::IMPORT));
    }

    public function test_it_writes_hex_permissions(): void
    {
        $user = new HexUser;

        $user->setAttribute('permissions', [
            'create' => true,
            'read' => true,
            'export' => true,
            'unknown' => true,
        ]);

        self::assertSame('13', $user->getAttributes()['permissions']);
    }

    public function test_has_permission_uses_cast_collection(): void
    {
        $user = new HexUser;
        $user->setRawAttributes([
            'permissions' => '13',
        ]);

        self::assertTrue($user->hasPermission(PermissionEnum::CREATE));
        self::assertFalse($user->hasPermission(PermissionEnum::UPDATE));
    }

    public function test_apply_package_sets_package_permissions(): void
    {
        $user = new HexUser;

        $user->applyPackage(PackageEnum::VIEWER);

        self::assertSame('12', $user->getAttributes()['permissions']);
        self::assertTrue($user->hasPermission(PermissionEnum::READ));
        self::assertTrue($user->hasPermission(PermissionEnum::EXPORT));
        self::assertFalse($user->hasPermission(PermissionEnum::CREATE));
    }

    public function test_detect_package_returns_matching_package(): void
    {
        $user = new JsonUser;
        $user->setAttribute('permissions', [
            'read' => true,
            'export' => true,
        ]);

        self::assertSame(PackageEnum::VIEWER, $user->detectPackage());
    }

    public function test_empty_and_null_permissions_are_all_disabled(): void
    {
        foreach ([new JsonUser, new HexUser] as $user) {
            $user->setRawAttributes(['permissions' => null]);

            self::assertFalse($user->hasPermission(PermissionEnum::CREATE));
            self::assertFalse($user->hasPermission(PermissionEnum::IMPORT));
            self::assertSame(PackageEnum::CUSTOM, $user->detectPackage());
        }

        $user = new HexUser;
        $user->setRawAttributes(['permissions' => '0']);

        self::assertFalse($user->hasPermission(PermissionEnum::CREATE));
    }

    public function test_invalid_and_unknown_permissions_are_ignored(): void
    {
        $jsonUser = new JsonUser;
        $jsonUser->setRawAttributes([
            'permissions' => '{"create":true,"removed_permission":true}',
        ]);

        self::assertTrue($jsonUser->hasPermission(PermissionEnum::CREATE));
        self::assertFalse($jsonUser->hasPermission(PermissionEnum::IMPORT));

        $hexUser = new HexUser;
        $hexUser->setRawAttributes([
            'permissions' => 'not-a-hex-value',
        ]);

        self::assertFalse($hexUser->hasPermission(PermissionEnum::CREATE));
    }

    public function test_json_cast_remains_backward_compatible(): void
    {
        $user = new JsonUser;
        $user->setRawAttributes([
            'permissions' => [
                'create' => 1,
                'read' => 0,
            ],
        ]);

        self::assertTrue($user->hasPermission(PermissionEnum::CREATE));
        self::assertFalse($user->hasPermission(PermissionEnum::READ));
        $permissions = $user->getAttribute('permissions');
        self::assertInstanceOf(PermissionsCollection::class, $permissions);

        self::assertSame('{
    "create": true,
    "read": false,
    "update": false,
    "delete": false,
    "export": false,
    "import": false
}', $permissions->toJson(JSON_PRETTY_PRINT));
    }
}

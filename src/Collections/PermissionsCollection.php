<?php
declare(strict_types=1);

namespace Din9xtr\LaravelEnumPermissions\Collections;

use Din9xtr\LaravelEnumPermissions\Contracts\PermissionInterface;
use Illuminate\Support\Collection;
use InvalidArgumentException;
use JsonSerializable;

final class PermissionsCollection implements JsonSerializable
{
    /** @var array<string,bool> */
    private array $permissions;

    /** @var class-string<PermissionInterface> */
    private string $enumClass;

    /**
     * @param array<string,bool> $permissions
     * @param class-string<PermissionInterface> $enumClass
     */
    public function __construct(array $permissions, string $enumClass)
    {
        $this->permissions = $permissions;
        $this->enumClass = $enumClass;
    }

    private function validatePermission(PermissionInterface $permission): void
    {
        if (!($permission instanceof $this->enumClass)) {
            throw new InvalidArgumentException(
                sprintf(
                    'Permission must be instance of %s, %s given',
                    $this->enumClass,
                    get_class($permission)
                )
            );
        }
    }

    public function has(PermissionInterface $permission): bool
    {
        $this->validatePermission($permission);
        return $this->permissions[$permission->value] ?? false;
    }

    /** @return Collection<PermissionInterface> */
    public function enabled(): Collection
    {
        return collect($this->permissions)
            ->filter(fn(bool $enabled) => $enabled)
            ->map(fn($_, string $value) => $this->enumClass::from($value));
    }

    /** @return array<string,bool> */
    public function toArray(): array
    {
        return $this->permissions;
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    public function toJson(int $options = 0): string
    {
        return json_encode($this->jsonSerialize(), $options);
    }

    public function __toString(): string
    {
        return $this->toJson();
    }
}

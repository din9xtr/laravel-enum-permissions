<?php

declare(strict_types=1);

namespace Din9xtr\LaravelEnumPermissions\Exceptions;

use Din9xtr\LaravelEnumPermissions\Contracts\PermissionInterface;
use Exception;

final class PermissionDeniedException extends Exception
{
    public function __construct(
        private readonly ?PermissionInterface $permission = null,
        private readonly ?string $resourceType = null,
        private readonly int|string|null $resourceId = null,
        private readonly ?string $action = null,
        ?string $message = null,
        int $code = 403
    ) {
        if ($message === null) {
            $message = $this->generateMessage();
        }

        parent::__construct($message, $code);
    }

    private function generateMessage(): string
    {
        $parts = ['Permission denied'];

        $permission = $this->permission?->value;
        if ($permission) {
            $parts[] = "[$permission]";
        }

        if ($this->action) {
            $parts[] = "for action: $this->action";
        }

        if ($this->resourceType) {
            $resource = $this->resourceType;
            if ($this->resourceId !== null) {
                $resource .= " #$this->resourceId";
            }
            $parts[] = "on resource: $resource";
        }

        return implode(' ', $parts);
    }

    public static function forPermission(
        PermissionInterface $permission,
        ?string $resourceType = null,
        $resourceId = null,
        ?string $action = null
    ): self {
        return new self($permission, $resourceType, $resourceId, $action);
    }
}
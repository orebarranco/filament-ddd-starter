<?php

declare(strict_types=1);

namespace Domain\Identity\DataTransferObjects;

final readonly class UserData
{
    public function __construct(
        public string $name,
        public string $email,
        public ?string $password = null,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'],
            email: $data['email'],
            password: isset($data['password']) && filled($data['password']) ? $data['password'] : null,
        );
    }
}

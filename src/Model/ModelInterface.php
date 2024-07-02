<?php

declare(strict_types=1);

namespace Src\Model;

interface ModelInterface
{
    public function fromArray(array $data, array $options = []): ModelInterface;
    public function toArray(bool $times = true ): array;
    public function getId(): ?int;
}

<?php

declare(strict_types=1);

namespace Src\Model;

interface ModelInterface
{
    public function fromArray(array $data): ModelInterface;
}

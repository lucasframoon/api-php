<?php

declare(strict_types=1);

namespace Src\Model;

abstract class Model implements ModelInterface
{
    /**
     * Creates an instance of the called class from an associative array
     *
     * @param array $data The associative array containing the data
     * @return ModelInterface The newly created instance
     */
    public function fromArray(array $data): ModelInterface
    {
        foreach ($data as $key => $value) {
            $method = 'set' . ucfirst($key);
            if (method_exists($this, $method)) {
                $this->$method($value);
            }
        }

        return $this;
    }
}

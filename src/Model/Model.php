<?php

declare(strict_types=1);

namespace Src\Model;

use ReflectionClass;

abstract class Model implements ModelInterface
{
    /**
     * Creates an instance of the called class from an associative array
     *
     * @param array $data The associative array containing the data
     * @return ModelInterface The newly created instance
     */
    public function fromArray(array $data, array $options = []): ModelInterface
    {
        foreach ($data as $key => $value) {
            $method = $this->convertToAccessors($key, 'set');
            if (method_exists($this, $method)) {
                $this->$method($value);
            }
        }

        return $this;
    }

    /**
     * Converts the object to an associative array
     *
     * @param bool $times Whether to include timestamps in the array. Default is true
     * @return array
     */
    public function toArray(bool $times = true): array
    { 
        $array = [];
        $reflectionClass = new ReflectionClass($this);
        $properties = $reflectionClass->getProperties();
        
        foreach ($properties as $property) {
            $property->setAccessible(true);
            if (!$property->isInitialized($this) || $times && in_array($property->getName(), ['created_at', 'updated_at'])) {
                continue;
            }

            $array[$property->getName()] = $property->getValue($this);
        }
       
        return $array;
    }

    /**
     * Convert column name to accessor method name
     *
     * @param string $columnName 
     * @param string $type Acessor method type. Default get
     * @return string
     */
    private function convertToAccessors(string $columnName, string $type = 'get'): string
    {
        $methodName = str_replace('_', ' ', $columnName);
        $methodName = str_replace(' ', '', ucwords($methodName));

        return $type . $methodName;
    }
}

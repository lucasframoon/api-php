<?php

declare(strict_types=1);

namespace Src\Model;

class User implements ModelInterface
{
    private string $id;
    private string $name;
    private string $email;
    private string $password;

    /**
     * Creates an instance of the called class from an associative array
     *
     * @param array $data The associative array containing the data
     * @param bool $setPassword Whether to set the password
     * @return User The newly created instance
     */
    public function fromArray(array $data, bool $setPassword = true): User
    {
        if (!$setPassword) {
            unset($data['password']);
        }

        foreach ($data as $key => $value) {
            $method = 'set' . ucfirst($key);
            if (method_exists($this, $method)) {
                $this->$method($value);
            }
        }

        return $this;
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setId(string $id): void
    {
        $this->id = $id;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    public function setPassword(string $password): void
    {
        $this->password = $password;
    }
}

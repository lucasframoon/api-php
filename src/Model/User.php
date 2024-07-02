<?php

declare(strict_types=1);

namespace Src\Model;

class User extends Model
{
    private int $id = 0;
    private string $name;
    private string $email;
    private string $password;
    private string $created_at;
    private string $updated_at;

    public function __construct(?array $fields = [])
    {
        $this->fromArray($fields);
    }

    /**
     * Creates an instance of the called class from an associative array
     *
     * @param array $data The associative array containing the data
     * @param bool $setPassword Whether to set the password
     * @return User The newly created instance
     */
    public function fromArray(array $data, array $options = ['setPassword' => true]): User
    {
        if (!$options['setPassword']) {
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

    public function getId(): ?int
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

    public function getCreatedAt(): ?string
    {
        return $this->created_at;
    }

    public function getUpdatedAt(): ?string
    {
        return $this->updated_at;
    }

    public function setId(string|int $id): void
    {
        $this->id = (int)$id;
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

    public function setCreatedAt(string $created_at): void
    {
        $this->created_at = $created_at;
    }

    public function setUpdatededAt(string $updated_at): void
    {
        $this->updated_at = $updated_at;
    }
}

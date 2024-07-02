<?php

declare(strict_types=1);

namespace Src\Model;

class Address extends Model
{
    private int $id = 0;
    private int $user_id;
    private string $street;
    private string $city;
    private string $state;
    private string $postal_code;
    private string $country;
    private string $created_at;
    private string $updated_at;

    public function __construct(?array $fields = [])
    {
        $this->fromArray($fields);
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUserId(): ?int
    {
        return $this->user_id;
    }

    public function getStreet(): ?string
    {
        return $this->street;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function getState(): ?string
    {
        return $this->state;
    }

    public function getPostalCode(): ?string
    {
        return $this->postal_code;
    }

    public function getCountry(): ?string
    {
        return $this->country;
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

    public function setUserId(string|int $user_id): void
    {
        $this->user_id = (int)$user_id;
    }

    public function setStreet(string $street): void
    {
        $this->street = $street;
    }

    public function setCity(string $city): void
    {
        $this->city = $city;
    }

    public function setState(string $state): void
    {
        $this->state = $state;
    }

    public function setPostalCode(string $postal_code): void
    {
        $this->postal_code = $postal_code;
    }

    public function setCountry(string $country): void
    {
        $this->country = $country;
    }

    public function setCreatedAt(string $created_at): void
    {
        $this->created_at = $created_at;
    }

    public function setUpdatedAt(string $updated_at): void
    {
        $this->updated_at = $updated_at;
    }
}

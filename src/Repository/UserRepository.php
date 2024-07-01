<?php

declare(strict_types=1);

namespace Src\Repository;

use PDO;
use Src\Model\{User, ModelInterface};

class UserRepository extends BaseRepository
{
    protected string $tableName = 'users';

    public function __construct(
        protected PDO $db,
        protected User $model
    ) {
        parent::__construct($db);
    }

    public function getTable(): string
    {
        return $this->tableName;
    }

    /**
     * Return user data
     *
     * @param int $id
     * @param bool $getAdresses (optional) Whether to include addresses in the result. Default is false.
     * @return array|null
     */
    public function getData(int $id, bool $getAdresses = false): ?array
    {
        $sql = "SELECT * 
                FROM " . $this->tableName . " 
                WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);

        if ($stmt->rowCount() > 0) {
            $userInfo = $stmt->fetch();
            $this->model->fromArray($userInfo, false);
            unset($userInfo['password']);

            $userAddressesInfo = [];
            if ($getAdresses) {
                $userAddressesInfo = $this->getUserAddress($id);
            }
            return ['user' => $userInfo, 'addresses' => $userAddressesInfo];
        }

        return null;
    }

    public function save(array $data, ?int $id = null): array
    {
        if ($id > 0) {
            $this->update($data, $id);
            $user = $this->findById($id);

            if (!$user) {
                return ['status' => 'NOT_FOUND', 'message' => 'User not found'];
            }

            return ['status' => 'SUCCESS', 'message' => 'User updated successfully', 'id' => $user['id']];
        } else {
            if ($this->findUserByEmail($data['email'])) {
                return ['status' => 'ALREADY_EXISTS', 'message' => 'Email already exists'];
            }

            if ($id = $this->create($data)) {
                return ['status' => 'SUCCESS', 'message' => 'User created successfully', 'id' => $id];
            }

            return ['status' => 'ERROR', 'message' => 'Failed to create user'];
        }
    }

    /**
     * Find user by email
     *
     * @param string $email
     * @return ModelInterface
     */
    public function findUserByEmail(string $email): ?ModelInterface
    {
        $sql = "SELECT * 
                FROM " . $this->tableName . " 
                WHERE email LIKE :email";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':email', $email);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            return $this->model->fromArray($stmt->fetch());
        }

        return null;
    }

    public function getUserAddress(int $userId): array
    {
        $sql = "SELECT * 
                FROM addresses 
                WHERE user_id = :user_id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':user_id', $userId);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            return $stmt->fetchAll();
        }

        return [];
    }
}

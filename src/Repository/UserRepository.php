<?php

declare(strict_types=1);

namespace Src\Repository;

use PDO;
use Src\Model\User;

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
        return 'users';
    }

    /**
     * Return user data
     *
     * @param integer $id
     * @return array|null
     */
    public function getData(int $id): ?array
    {
        $sql = 'SELECT * FROM ' . $this->tableName . ' WHERE id = :id';
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);

        if ($stmt->rowCount() > 0) {
            $result = $stmt->fetch();
            $this->model->fromArray($result, false);
            unset($result['password']);
            return $result;
        }

        return null;
    }

    public function save(array $data, ?int $id = null): array
    {
        if ($id > 0) {
            $result = $this->update($data, $id);
            $user = $this->findById($id);

            if (!$user) {
                return ['status' => 'error', 'message' => 'User not found'];
            } elseif (!$result) {
                return ['status' => 'error', 'message' => 'Failed to update user'];
            }

            return ['status' => 'success', 'message' => 'User updated successfully', 'user' => $user];
        } else {
            if ($this->findByColumn('email', $data['email'])) {
                return ['status' => 'error', 'message' => 'Email already exists'];
            }

            if ($id = $this->create($data)) {
                return ['status' => 'success', 'message' => 'User created successfully', 'id' => $id];
            }

            return ['status' => 'error', 'message' => 'Failed to create user'];
        }
    }

    /**
     * Find user by email
     *
     * @param string $email
     * @return User
     */
    public function findUserByEmail(string $email): ?User
    {
        $sql = "SELECT * FROM " . $this->tableName . " WHERE email LIKE :email";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':email', $email);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            return $this->model->fromArray($stmt->fetch());
        }

        return null;
    }

    //TODO
    // public function getAddressRelations(int $id) {
    // }
}

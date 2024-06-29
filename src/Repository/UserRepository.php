<?php

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
     * @return array
     */
    public function getData(int $id): ?array
    {
        $sql = 'SELECT * FROM ' . $this->tableName . ' WHERE id = :id';
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);

        if ($user = $stmt->fetch(PDO::FETCH_ASSOC)) {
            return $user;
        }

        return null;
    }

    public function save(array $data, ?int $id = null): array
    {
        if ($id > 0) {
            if ($this->update($data, $id)) {
                $user = $this->findById($id);
                return ['status' => 'success', 'message' => 'User updated successfully', 'user' => $user];
            }

            return ['status' => 'error', 'message' => 'Failed to update user'];
        } else {
            if (!empty($this->findByColumn('email', $data['email']))) {
                return ['status' => 'error', 'message' => 'Email already exists'];
            }

            if ($id = $this->create($data)) {
                return ['status' => 'success', 'message' => 'User created successfully', 'id' => $id];
            }

            return ['status' => 'error', 'message' => 'Failed to create user'];
        }
    }

    //TODO
    // public function getAddressRelations(int $id) {
    // }
}

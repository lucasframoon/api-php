<?php

declare(strict_types=1);

namespace Src\Repository;

use PDO;
use Src\Model\{User, ModelInterface};

class UserRepository extends BaseRepository
{
    protected string $tableName = 'users';
    protected User|ModelInterface $model;

    public function __construct(
        protected PDO $db
    ) {
        $this->model = new User();
        parent::__construct($db);
    }

    public function getModel(int $id, bool $setPassword = false): User|ModelInterface
    {
        $model = $this->findById($id);

        if ($model) {
            $this->model = $model;
            if (!$setPassword) {
                $this->model->setPassword('');
            }
        }

        return $this->model;
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
            $this->model->fromArray($userInfo, ['setPassword' => false]);
            unset($userInfo['password']);

            $userAddressesInfo = [];
            if ($getAdresses) {
                $userAddressesInfo = $this->getUserAddress($id);
            }
            return ['user' => $userInfo, 'addresses' => $userAddressesInfo];
        }

        return null;
    }

    /**
     * @inheritDoc
     */
    public function save(ModelInterface|User $model): bool
    {
        if ($model->getId() > 0) {
            return $this->update($model);
        } elseif ($this->findUserByEmail($model->getEmail())) {
            throw new \Exception('ALREADY_EXISTS');
        } elseif ($this->create($model)) {
            return true;
        }

        throw new \Exception('ERROR');
    }

    /**
     * @inheritDoc
     */
    public function update(ModelInterface $model): bool
    {
        $data = $model->toArray();
        unset($data['password']);

        $fields = '';
        foreach ($data as $key => $value) {
            $fields .= $key . ' = :' . $key . ', ';
        }

        $fields = rtrim($fields, ', ');

        $sql = "UPDATE " . $this->tableName . " SET " . $fields . " WHERE id = :id";

        $stmt = $this->db->prepare($sql);
        foreach ($data as $key => $value) {
            $stmt->bindValue(':' . $key, $value);
        }

        $stmt->bindValue(':id', $data['id']);
        $stmt->execute();

        return $stmt->rowCount() > 0;
    }

    /**
     * Find user by email
     *
     * @param string $email
     * @param bool $setPassword
     * @return ModelInterface
     */
    public function findUserByEmail(string $email, bool $setPassword = false): ?ModelInterface
    {
        $sql = "SELECT * 
                FROM " . $this->tableName . " 
                WHERE email LIKE :email";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':email', $email);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            return $this->model->fromArray($stmt->fetch(), ['setPassword' => $setPassword]);
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

<?php

declare(strict_types=1);

namespace Src\Repository;

use PDO;
use Src\Model\{Address, ModelInterface};

class AddressRepository extends BaseRepository
{
    protected string $tableName = 'addresses';
    protected Address|ModelInterface $model;

    public function __construct(
        protected PDO $db
    ) {
        $this->model = new Address();
        parent::__construct($db);
    }

    public function getTable(): string
    {
        return $this->tableName;
    }

    /**
     * Return address data
     *
     * @param int $id
     * @param int $userId
     * @return array|null
     */
    public function getData(int $id, int $userId): ?array
    {
        $sql = 'SELECT * 
                FROM ' . $this->tableName . ' 
                WHERE id = :id 
                    AND user_id = :user_id';
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':user_id', $userId);
        $stmt->bindValue(':id', $id);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $result = $stmt->fetch();
            $this->model->fromArray($result);
            return $result;
        }

        return null;
    }

    public function save(ModelInterface|Address $model): bool
    {
        if (($model->getId() > 0 && $this->update($model)) || $this->create($model)) {
            return true;
        }

        throw new \Exception('ERROR');
    }

    /**
     * Find by user_id and filter by specific columns like '%%'
     *
     * @param int $userId
     * @param array $where An associative array containing the search criteria
     * @return array|null An array of records matching the search criteria, or null if no records are found
     */
    public function findByUserId(int $userId, array $where): ?array
    {
        $sql = "SELECT * 
                FROM " . $this->tableName . " 
                WHERE user_id = :user_id ";

        foreach ($where as $key => $value) {
            $sql .= " AND {$key} LIKE :{$key}";
        }

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':user_id', $userId);
        foreach ($where as $key => $value) {
            $stmt->bindValue(':' . $key, '%' . $value . '%');
        }
        $stmt->execute();

        return $stmt->rowCount() > 0 ? $stmt->fetchAll() : null;
    }

    public function checkUserId(int $userId): bool
    {
        $sql = "SELECT * 
                FROM users 
                WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $userId);
        $stmt->execute();
        return $stmt->rowCount() > 0;
    }
}

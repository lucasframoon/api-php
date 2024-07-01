<?php

declare(strict_types=1);

namespace Src\Repository;

use PDO;
use Src\Model\Address;

class AddressRepository extends BaseRepository
{
    protected string $tableName = 'addresses';

    public function __construct(
        protected PDO $db,
        protected Address $model
    ) {
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

    public function save(array $data, ?int $id = null): array
    {
        if ($id > 0) {
            $this->update($data, $id);
            $address = $this->getData($id, (int)$data['user_id']);

            if (!$address) {
                return ['status' => 'NOT_FOUND', 'message' => 'Address not found'];
            }

            return ['status' => 'SUCCESS', 'message' => 'Address updated successfully', 'id' => $address['id']];
        } else {
            if ($id = $this->create($data)) {
                return ['status' => 'SUCCESS', 'message' => 'Address created successfully', 'id' => $id];
            }

            return ['status' => 'ERROR', 'message' => 'Failed to create address'];
        }
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
}

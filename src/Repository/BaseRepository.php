<?php

declare(strict_types=1);

namespace Src\Repository;

use PDO;
use Src\Model\ModelInterface;

abstract class BaseRepository
{
    protected string $tableName;
    protected ModelInterface $model;

    public function __construct(protected PDO $db)
    {
    }

    abstract public function getTable(): string;

    /**
     * Saves a model to the database
     *
     * @param ModelInterface $model
     * @throws \Exception If the already exists on create or there is an error during the save operation
     * @return bool True if the save operation was successful, false otherwise
     */
    abstract public function save(ModelInterface $model): bool;

    public function getModel(int $id): ModelInterface
    {
        if ($model = $this->findById($id)) {
            $this->model = $model;
        }

        return $this->model;
    }

    /**
     * Find a record in the database by its ID
     *
     * @param int $id The ID of the record to find
     * @return ModelInterface|null Model or null if no record is found
     */
    public function findById(int $id): ?ModelInterface
    {
        $sql = "SELECT * 
                FROM " . $this->tableName . " 
                WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);

        return $stmt->rowCount() > 0 ? $this->model->fromArray($stmt->fetch()) : null;
    }

    /**
     * Find a record by specific column using LIKE '%%'
     *
     * @param string $colunmName The name of the column to search
     * @param string $value The value to search for
     * @return array|null An array of records matching the search criteria, or null if no records are found
     */
    public function findByColumn(string $colunmName, string $value): ?array
    {
        $sql = "SELECT * 
                FROM " . $this->tableName . " 
                WHERE {$colunmName} LIKE :value";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':value', '%' . $value . '%');
        $stmt->execute();

        return $stmt->rowCount() > 0 ? $stmt->fetchAll() : null;
    }

    /**
     * Retrieves all records from the table
     *
     * @return array|null An array of records or null if no records are found
     */
    public function findAll(): ?array
    {
        $sql = "SELECT * 
                FROM " . $this->tableName;
        $stmt = $this->db->prepare($sql);
        $stmt->execute();

        return $stmt->rowCount() > 0 ? $stmt->fetchAll() : null;
    }

    /**
     * Inserts a new record into the table
     *
     * @param ModelInterface $model
     * @return int|null The ID of the inserted record, or null if the insertion failed
     */
    public function create(ModelInterface $model): ?int
    {
        $data = $model->toArray();

        $fields = implode(', ', array_keys($data));
        $placeholders = ':' . implode(', :', array_keys($data));

        $sql = "INSERT INTO " . $this->tableName . " (" . $fields . ") VALUES (" . $placeholders . ")";
        $stmt = $this->db->prepare($sql);
        
        foreach ($data as $key => $value) {
            $stmt->bindValue(':' . $key, $value);
        }
     
        $result = $stmt->execute();
     
        return $result ? (int)$this->db->lastInsertId() : null;
    }

    /**
     * Updates a record in the database with the provided data with the specified ID
     *
     * @param ModelInterface $model
     * @return bool True if the update was successful, false otherwise
     */
    public function update(ModelInterface $model): bool
    {
        $data = $model->toArray(false);

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
     * Deletes a record from the database with the specified ID
     *
     * @param int $id The ID of the record to delete
     * @return bool Returns true if the record was successfully deleted, false otherwise
     */
    public function delete(int $id): bool
    {
        $sql = "DELETE 
                FROM " . $this->tableName . " 
                WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);

        return $stmt->rowCount() > 0;
    }
}

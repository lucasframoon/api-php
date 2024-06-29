<?php

namespace Src\Repository;

use PDO;

abstract class BaseRepository
{
    protected string $tableName;

    public function __construct(protected PDO $db)
    {
    }

    abstract public function getTable(): string;
    abstract public function save(array $data, ?int $id = null): array;

    public function findById(int $id): array
    {
        $sql = "SELECT * FROM " . $this->tableName . " WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }

    /**
     * Find by specific column like '%%'
     *
     * @param string $colunmName
     * @param string $value
     * @return array
     */
    public function findByColumn(string $colunmName, string $value): array
    {
        $sql = "SELECT * FROM " . $this->tableName . " WHERE {$colunmName} LIKE :value";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':value', $value);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function findAll(): array
    {
        $sql = "SELECT * FROM " . $this->tableName;
        $stmt = $this->db->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll();
    }



    public function create(array $data): ?int
    {
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

    public function update(array $data, int $id): bool
    {
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

        $stmt->bindValue(':id', $id);
        $stmt->execute();

        return $stmt->rowCount() > 0;
    }

    public function delete(int $id): bool
    {
        $sql = "DELETE FROM " . $this->tableName . " WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return (bool)$stmt->execute(['id' => $id]);
    }
}

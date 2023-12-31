<?php

declare(strict_types = 1);

namespace FireHead996\PdoHelper;

use PDO;
use PDOStatement;

abstract class PdoRepository
{
    protected string $entityName = 'Entity';
    protected string $table = 'Entities';

    public function __construct(
        private PDO $db
    ) {
    }

    protected function fetch(string $id): object|false
    {
        $statement = $this->where('id = :id', [
            ':id' => $id,
        ]);

        return $statement->fetchObject($this->entityName);
    }

    protected function fetchAll(): array|false
    {
        return $this->where()->fetchAll(PDO::FETCH_CLASS, $this->entityName);
    }

    protected function where(string $where = '', array $parameters = []): PDOStatement
    {
        $query = "SELECT * FROM {$this->table}";

        if (strlen($where) > 0) {
            $query .= " WHERE $where";
        }

        $statement = $this->db->prepare($query);
        $statement->execute($parameters);

        return $statement;
    }

    protected function exists(string $where = '', array $parameters = []): bool
    {
        return $this->count($where, $parameters) === 1;
    }

    protected function count(string $where = '', array $parameters = []): int
    {
        $query = "SELECT count(*) FROM $this->table";

        if (strlen($where) > 0 && count($parameters) > 0) {
            $query .= " WHERE $where";
        }

        $statement = $this->db->prepare($query);
        $statement->execute($parameters);

        return (int)$statement->fetchColumn();
    }

    protected function insertOrUpdate(object $entity): void
    {
        $params = new PdoParams($entity);

        $exists = $this->exists('id = :id', $params->getBindableParameter('id'));

        if ($exists) {
            $this->update($params);

            return;
        }

        $this->insert($params);
    }

    private function getType(mixed $value): int
    {
        switch (true) {
            case is_int($value):
                return PDO::PARAM_INT;
            case is_bool($value):
                return PDO::PARAM_BOOL;
            case is_null($value):
                return PDO::PARAM_NULL;
            default:
                return PDO::PARAM_STR;
        }
    }

    protected function insert(PdoParams $params): void
    {
        $table = $this->table;
        $schema = implode(', ', $params->getKeys());
        $values = $params->getAllFieldsToInsert();
        $sql = "INSERT INTO $table ($schema) VALUES ($values)";
        $stmt = $this->db->prepare($sql);

        foreach ($params->getAllBindableParameters() as $key => $value) {
            $stmt->bindValue($key, $value, $this->getType($value));
        }

        $stmt->execute();
    }

    protected function update(PdoParams $params): void
    {
        $table = $this->table;
        $values = $params->getAllFieldsToUpdate();
        $sql = "UPDATE $table SET $values WHERE id = :id";
        $stmt = $this->db->prepare($sql);

        foreach ($params->getAllBindableParameters() as $key => $value) {
            $stmt->bindValue($key, $value, $this->getType($value));
        }

        $stmt->execute();
    }
}

<?php

declare(strict_types = 1);

namespace FireHead996\PdoHelper;

use ReflectionClass;

class PDOParams
{
    private string $className;
    private ReflectionClass $ref;
    private array $props;

    public function __construct(
        private object $entity
    ) {
        $this->className = $this->entity::class;
        $this->ref = new ReflectionClass($this->entity);
        $this->fetchProperties();
    }

    private function fetchProperties(): void
    {
        $this->props = array_filter(
            $this->ref->getProperties(),
            fn ($prop) => $prop->class == $this->className
        );
    }

    public function getKeys(): array
    {
        return array_map(
            fn ($prop) => $prop->name,
            $this->props
        );
    }

    public function getFieldToInsert(string $key): string
    {
        return ":$key";
    }

    public function getAllFieldsToInsert(): string
    {
        $fields = [];

        foreach ($this->getKeys() as $key) {
            $fields[] = $this->getFieldToInsert($key);
        }

        return implode(
            ', ',
            $fields
        );
    }

    public function getFieldToUpdate(string $key): string
    {
        return "$key = :$key";
    }

    public function getAllFieldsToUpdate(): string
    {
        $fields = [];

        foreach ($this->getKeys() as $key) {
            $fields[] = $this->getFieldToUpdate($key);
        }

        return implode(
            ', ',
            $fields
        );
    }

    public function getBindableParameter(string $key): array
    {
        $prop = $this->ref->getProperty($key);

        return [
            ':' . $key => $prop->getValue($this->entity),
        ];
    }

    public function getAllBindableParameters(): array
    {
        $params = [];

        foreach ($this->getKeys() as $propName) {
            $prop = $this->ref->getProperty($propName);
            $params[':' . $propName] = $prop->getValue($this->entity);
        }

        return $params;
    }
}

<?php

declare(strict_types = 1);

namespace FireHead996\PdoHelper\Tests;

use FireHead996\PdoHelper\PDOParams;
use PHPUnit\Framework\TestCase;

class PDOParamsTest extends TestCase
{
    public function testCanBeCreatedFromValidObject(): void
    {
        // Arrange
        $testClass = new TestClass();

        $testClass->setId(5);
        $testClass->setName('Test');
        $testClass->setActive(true);

        $expected = [
            ':id' => 5,
            ':name' => 'Test',
            ':active' => true,
        ];

        // Act
        $params = new PDOParams($testClass);

        // Assert
        $this->assertSame($params->getAllBindableParameters(), $expected);
    }
}

class TestClass
{
    private int $id;
    private string $name;
    private bool $active;

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function setActive(bool $active): void
    {
        $this->active = $active;
    }
}

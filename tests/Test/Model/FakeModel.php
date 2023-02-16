<?php

declare(strict_types=1);

namespace ParadiseSecurity\Component\DataSentry\Test\Model;

use ParadiseSecurity\Component\DataSentry\Attribute\Encrypted;
use ParadiseSecurity\Component\DataSentry\Attribute\MigrateEncryption;
use ParadiseSecurity\Component\DataSentry\Attribute\BlindIndex;
use ParadiseSecurity\Component\DataSentry\Model\EncryptableInterface;

final class FakeModel implements EncryptableInterface
{
    #[Encrypted(encryptor: "supersweet_v1.0.1")]
    #[MigrateEncryption(new Encrypted(encryptor: "supersweet_v1.0.0"))]
    private string $name;

    #[Encrypted(encryptor: "supersweet_v1.0.1")]
    private string $accountNumber;

    #[BlindIndex(indexOf: "accountNumber")]
    private ?string $accountNumberBi = null;

    private int $secretNumber;

    #[Encrypted(encryptor: "supersweet_v1.0.1", mappedTypedProperty: "secretNumber")]
    private ?string $secretNumberEncrypted = null;

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getAccountNumber(): string
    {
        return $this->accountNumber;
    }

    public function setAccountNumber(string $accountNumber): void
    {
        $this->accountNumber = $accountNumber;
    }

    public function getAccountNumberBi(): ?string
    {
        return $this->accountNumberBi;
    }

    public function setAccountNumberBi(?string $accountNumberBi): void
    {
        $this->accountNumberBi = $accountNumberBi;
    }

    public function getSecretNumber(): int
    {
        return $this->secretNumber;
    }

    public function setSecretNumber(int $secretNumber): void
    {
        $this->secretNumber = $secretNumber;
    }

    public function getSecretNumberEncrypted(): ?string
    {
        return $this->secretNumberEncrypted;
    }

    public function setSecretNumberEncrypted(?string $secretNumberEncrypted): void
    {
        $this->secretNumberEncrypted = $secretNumberEncrypted;
    }
}

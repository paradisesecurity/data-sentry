<?php

declare(strict_types=1);

namespace ParadiseSecurity\Component\DataSentry\Test\Processor;

use PHPUnit\Framework\TestCase;
use ParadiseSecurity\Component\DataSentry\Test\EntityProcessorTrait;
use ParadiseSecurity\Component\DataSentry\Test\FakeModelTrait;

final class EntityProcessorTest extends TestCase
{
    use EntityProcessorTrait;
    use FakeModelTrait;

    public function testEntityCanBeDecrypted()
    {
        $encryptedConfig = $this->getEncryptedFakeModelConfig();

        $fake = $this->createFakeModel($encryptedConfig);

        $processor = $this->getDefaultTestEntityProcessor();

        $processor->decrypt($fake);

        $decryptedConfig = $this->getDecryptedFakeModelConfig();

        $this->assertSame($decryptedConfig['name'], $fake->getName());
        $this->assertSame($decryptedConfig['account_number'], $fake->getAccountNumber());
        $this->assertSame($decryptedConfig['account_number_bi'], $fake->getAccountNumberBi());
        $this->assertSame($decryptedConfig['secret_number'], $fake->getSecretNumber());
        $this->assertSame($decryptedConfig['secret_number_encrypted'], $fake->getSecretNumberEncrypted());
    }

    public function testEntityCanBeEncrypted()
    {
        $decryptedConfig = $this->getDecryptedFakeModelConfig();
        unset($decryptedConfig['account_number_bi']);

        $fake = $this->createFakeModel($decryptedConfig);

        $processor = $this->getDefaultTestEntityProcessor();

        $processor->encrypt($fake);

        $encryptedConfig = $this->getEncryptedFakeModelConfig();

        $this->assertStringStartsWith('brng:', $fake->getName());
        $this->assertStringStartsWith('brng:', $fake->getAccountNumber());
        $this->assertSame($encryptedConfig['account_number_bi'], $fake->getAccountNumberBi());
        $this->assertSame($encryptedConfig['secret_number'], $fake->getSecretNumber());
        $this->assertStringStartsWith('brng:', $fake->getSecretNumberEncrypted());
    }

    public function testEntityCannotBeDecrypted()
    {
        $encryptedConfig = $this->getEncryptedFakeModelConfig();

        $fake = $this->createFakeModel($encryptedConfig);

        $resolver = $this->createTransformationResolver();

        $boringEngine = $this->createBoringEngine();
        $boringEncryptor = $this->createEncryptor($boringEngine, $resolver);

        $registry = $this->createEncryptorServiceRegistry([
            'supersweet_v2.0.0' => $boringEncryptor,
        ]);

        $encryptorResolver = $this->createEncryptorResolver($registry);

        $processor = $this->createEntityProcessor($encryptorResolver);

        $processor->decrypt($fake);

        $this->assertSame($encryptedConfig['name'], $fake->getName());
        $this->assertSame($encryptedConfig['account_number'], $fake->getAccountNumber());
        $this->assertSame($encryptedConfig['account_number_bi'], $fake->getAccountNumberBi());
        $this->assertSame($encryptedConfig['secret_number'], $fake->getSecretNumber());
        $this->assertSame($encryptedConfig['secret_number_encrypted'], $fake->getSecretNumberEncrypted());
    }
}

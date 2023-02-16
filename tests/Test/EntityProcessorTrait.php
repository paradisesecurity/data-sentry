<?php

declare(strict_types=1);

namespace ParadiseSecurity\Component\DataSentry\Test;

use ParadiseSecurity\Component\DataSentry\Encryptor\CipherSweet\CipherSweetAdapter;
use ParadiseSecurity\Component\DataSentry\Encryptor\CipherSweet\Resolver\BlindIndexTransformationResolver;
use ParadiseSecurity\Component\DataSentry\Encryptor\CipherSweet\Resolver\BlindIndexTransformationResolverInterface;
use ParadiseSecurity\Component\DataSentry\Encryptor\CipherSweet\ServiceRegistry\DefaultTransformationServiceRegistry;
use ParadiseSecurity\Component\DataSentry\Encryptor\Encryptor;
use ParadiseSecurity\Component\DataSentry\Encryptor\EncryptorInterface;
use ParadiseSecurity\Component\DataSentry\Factory\RequestFactory;
use ParadiseSecurity\Component\DataSentry\Factory\SubRequestFactory;
use ParadiseSecurity\Component\DataSentry\Generator\DelegatingGenerator;
use ParadiseSecurity\Component\DataSentry\Generator\GeneratorInterface;
use ParadiseSecurity\Component\DataSentry\Generator\ValueEndingByGenerator;
use ParadiseSecurity\Component\DataSentry\Generator\ValueStartingByGenerator;
use ParadiseSecurity\Component\DataSentry\Handler\IndexingHandler;
use ParadiseSecurity\Component\DataSentry\Hydrator\PropertyHydrator;
use ParadiseSecurity\Component\DataSentry\Metadata\MetadataCollector;
use ParadiseSecurity\Component\DataSentry\Processor\EntityProcessor;
use ParadiseSecurity\Component\DataSentry\Processor\EntityProcessorInterface;
use ParadiseSecurity\Component\DataSentry\Reader\AttributeReader;
use ParadiseSecurity\Component\DataSentry\Request\RequestStack;
use ParadiseSecurity\Component\DataSentry\Resolver\EncryptorResolver;
use ParadiseSecurity\Component\DataSentry\Resolver\EncryptorResolverInterface;
use ParadiseSecurity\Component\ServiceRegistry\Registry\ServiceRegistry;
use ParadiseSecurity\Component\ServiceRegistry\Registry\ServiceRegistryInterface;
use ParagonIE\CipherSweet\Backend\BoringCrypto;
use ParagonIE\CipherSweet\Backend\FIPSCrypto;
use ParagonIE\CipherSweet\CipherSweet;
use ParagonIE\CipherSweet\KeyProvider\StringProvider;
use ParagonIE\ConstantTime\Hex;

trait EntityProcessorTrait
{
    public const BORING_CRYPTO_HEX_KEY = '8174f2b78ad4be9d512e28047b0ea67f618d1726e6f61b96394b02290c8e6570';

    public const FIPS_CRYPTO_HEX_KEY = 'e339e68dd4c0590cee28c153f608eda1f7e9fef0540cd694f7ec20a35ae6d062';

    protected function getDefaultTestEntityProcessor(): EntityProcessorInterface
    {
        $resolver = $this->createTransformationResolver();

        $boringEngine = $this->createBoringEngine();
        $boringEncryptor = $this->createEncryptor($boringEngine, $resolver);

        $fipsEngine = $this->createFipsEngine();
        $fipsEncryptor = $this->createEncryptor($fipsEngine, $resolver);

        $registry = $this->createEncryptorServiceRegistry([
            'supersweet_v1.0.1' => $boringEncryptor,
            'supersweet_v1.0.0' => $fipsEncryptor,
        ]);

        $encryptorResolver = $this->createEncryptorResolver($registry);

        return $this->createEntityProcessor($encryptorResolver);
    }

    protected function createEntityProcessor(EncryptorResolverInterface $resolver): EntityProcessorInterface
    {
        $factory = new RequestFactory($resolver);

        $registry = $this->createIndexingGeneratorsServiceRegistry([
            'value_ending_by' => new ValueEndingByGenerator(),
            'value_starting_by' => new ValueStartingByGenerator(),
        ]);

        $handler = new IndexingHandler(new DelegatingGenerator($registry));

        return new EntityProcessor(new MetadataCollector(new AttributeReader()), new RequestStack(), $factory, new SubRequestFactory(), $handler, new PropertyHydrator());
    }

    protected function createEncryptorResolver(ServiceRegistryInterface $registry): EncryptorResolverInterface
    {
        return new EncryptorResolver($registry);
    }

    protected function createIndexingGeneratorsServiceRegistry(array $generators): ServiceRegistryInterface
    {
        $registry = new ServiceRegistry(GeneratorInterface::class);

        foreach ($generators as $name => $generator) {
            $registry->register($name, $generator);
        }

        return $registry;
    }

    protected function createEncryptorServiceRegistry(array $encryptors): ServiceRegistryInterface
    {
        $registry = new ServiceRegistry(EncryptorInterface::class);

        foreach ($encryptors as $name => $encryptor) {
            $registry->register($name, $encryptor);
        }

        return $registry;
    }

    protected function createEncryptor(CipherSweet $engine, BlindIndexTransformationResolverInterface $resolver): EncryptorInterface
    {
        return new Encryptor(
            new CipherSweetAdapter($engine, $resolver)
        );
    }

    protected function createTransformationResolver($transformations = []): BlindIndexTransformationResolverInterface
    {
        $default = new DefaultTransformationServiceRegistry();

        return new BlindIndexTransformationResolver($default($transformations));
    }

    protected function createFipsEngine($key = null): CipherSweet
    {
        return new CipherSweet(
            new StringProvider($key ? Hex::decode($key) : Hex::decode(self::FIPS_CRYPTO_HEX_KEY)),
            new FIPSCrypto()
        );
    }

    protected function createBoringEngine($key = null): CipherSweet
    {
        return new CipherSweet(
            new StringProvider($key ? Hex::decode($key) : Hex::decode(self::BORING_CRYPTO_HEX_KEY)),
            new BoringCrypto()
        );
    }
}

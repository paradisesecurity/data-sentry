# Data Sentry

An encryption component for entity objects that can be used for all types of php applications.

## Installation

```bash
composer require paradisesecurity/data-sentry
```

## Basic Usage

Data Sentry allows you to encrypt and decrypt any property of an object using a chosen encryptor adapter.

### Encryptor Setup

Data Sentry requires a `Encryptor` be defined before you can begin processing encryption and decryption requests. You can use the `Encryptor` provided, or you can create your own implementing the `EncryptorInterface`. This example will use the `Encryptor` provided.

The first dependency is the encryptor adapter, which must implement the `EncryptorAdapterInterface`. An adapter is a single class that bridges the gap between an already developed encryptor and Data Sentry's encryptor.

Data Sentry comes with adapters ready to use. However, if you decide to create your own adapter, you can use Data Sentry's `AbstractEncryptorAdapter` as a guide. Let's set up one of Data Sentry's out of the box adapters, [CipherSweet](https://github.com/paragonie/ciphersweet).

The `CipherSweetAdapter` has two dependencies, `CipherSweet` itself and the `BlindIndexTransformationResolver`. Let's see how to set up both of these services.

First setup CipherSweet:

```php
use ParagonIE\CipherSweet\Backend\BoringCrypto;
use ParagonIE\CipherSweet\CipherSweet;
use ParagonIE\CipherSweet\KeyProvider\StringProvider;

$random = \random_bytes(32);
$provider = new StringProvider($random);
$crypto = new BoringCrypto();
$engine = new CipherSweet($provider, $crypto);
```

Next, in order to set up the `BlindIndexTransformationResolver`, you need to first register some transformations in a [ServiceRegistry](https://github.com/paradisesecurity/service-registry).

You can do this manually for every transformation you want to register:

```php
use ParadiseSecurity\Component\ServiceRegistry\Registry\ServiceRegistry;
use ParagonIE\CipherSweet\Contract\TransformationInterface;
use ParagonIE\CipherSweet\Transformation\AlphaCharactersOnly;

$registry = new ServiceRegistry(TransformationInterface::class);
$registry->register('alpha_characters_only', new AlphaCharactersOnly());
```

You can also use Data Sentry's `DefaultTransformationServiceRegistry` which invokes the default transformations and returns a `ServiceRegistry`. Pass an array of white listed transformations you want to enable or pass nothing to enable them all. You can see a list of all the default transformations by using the constant `DefaultTransformationServiceRegistryInterface::DEFAULT_TRANSFORMATIONS`.

```php
use ParadiseSecurity\Component\DataSentry\Encryptor\CipherSweet\ServiceRegistry\DefaultTransformationServiceRegistry;

$default = new DefaultTransformationServiceRegistry();
$registry = $default(['alpha_characters_only']);
```

Now that you have a `ServiceRegistry`, you can register any transformations you have forgotten or even some you created yourself. So long as they implement the `TransformationInterface` they will be registered and available to you in the `CipherSweetAdapter`.

Put everything together and you have your `Encryptor`:

```php
use ParadiseSecurity\Component\DataSentry\Encryptor\CipherSweet\CipherSweetAdapter;
use ParadiseSecurity\Component\DataSentry\Encryptor\CipherSweet\Resolver\BlindIndexTransformationResolver;
use ParadiseSecurity\Component\DataSentry\Encryptor\Encryptor;

$resolver = new BlindIndexTransformationResolver($registry);
$adapter = new CipherSweetAdapter($engine, $resolver);
$encryptor = new Encryptor($adapter);
```

### Entity Processor Setup

The `EntityProcessor` is what makes an encryption / decryption request for a given object. That request is then passed along to the appropriate encryptor. You can register more than one encryptor and Data Sentry's `RequestFactory` will choose the correct encryptor for the job.

Create your encryptors, register them in a `ServiceRegistry` and then assign the registry to the `EncryptorResolver`. Inject the resolver into the `RequestFactory`, and you're ready to use the `EntityProcessor`.

```php
use ParadiseSecurity\Component\DataSentry\Encryptor\EncryptorInterface;
use ParadiseSecurity\Component\DataSentry\Factory\RequestFactory;
use ParadiseSecurity\Component\DataSentry\Factory\SubRequestFactory;
use ParadiseSecurity\Component\DataSentry\Hydrator\PropertyHydrator;
use ParadiseSecurity\Component\DataSentry\Processor\EntityProcessor;
use ParadiseSecurity\Component\DataSentry\Reader\AttributeReader;
use ParadiseSecurity\Component\DataSentry\Request\RequestStack;
use ParadiseSecurity\Component\DataSentry\Resolver\EncryptorResolver;
use ParadiseSecurity\Component\ServiceRegistry\Registry\ServiceRegistry;

$registry = new ServiceRegistry(EncryptorInterface::class);
$registry->register('supersweet_v1.0.0', $encryptorA);
$registry->register('supersweet_v1.0.1', $encryptorB);

$resolver = new EncryptorResolver($registry);
$factory = new RequestFactory($resolver);

$processor = new EntityProcessor(new AttributeReader(), new RequestStack(), $factory, new SubRequestFactory(), new PropertyHydrator());
```

## Encrypting / Decrypting An Object

Data Sentry uses PHP Attributes to process an object's properties.

### Attributes

Data Sentry allows you to define `Encrypted`, `BlindIndex`, `Indexed` and `MigrateEncryption` attributes, but you can add more by implementing the `AttributeInterface`.

## TODO!
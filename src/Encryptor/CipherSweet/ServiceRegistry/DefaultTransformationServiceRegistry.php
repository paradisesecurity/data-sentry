<?php

declare(strict_types=1);

namespace ParadiseSecurity\Component\DataSentry\Encryptor\CipherSweet\ServiceRegistry;

use ParadiseSecurity\Component\ServiceRegistry\Registry\ServiceRegistryInterface;
use ParadiseSecurity\Component\ServiceRegistry\Registry\ServiceRegistry;
use ParagonIE\CipherSweet\Contract\TransformationInterface;

class DefaultTransformationServiceRegistry implements DefaultTransformationServiceRegistryInterface
{
    private $registry;

    private $defaults;

    public function __construct()
    {
        $this->registry = new ServiceRegistry(TransformationInterface::class);

        $this->defaults = DefaultTransformationServiceRegistryInterface::DEFAULT_TRANSFORMATIONS;
    }

    public function __invoke(array $transformations = []): ServiceRegistryInterface
    {
        if (empty($transformations)) {
            return $this->registerAll();
        }

        return $this->registerFiltered($transformations);
    }

    private function registerFiltered(array $transformations): ServiceRegistryInterface
    {
        foreach ($this->defaults as $name => $service) {
            if (in_array($name, $transformations)) {
                $this->registry->register($name, new $service());
            }
        }

        return $this->registry;
    }

    private function registerAll(): ServiceRegistryInterface
    {
        foreach ($this->defaults as $name => $service) {
            $this->registry->register($name, new $service());
        }

        return $this->registry;
    }
}

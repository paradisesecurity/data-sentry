<?php

declare(strict_types=1);

namespace ParadiseSecurity\Component\DataSentry\Request;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use ParadiseSecurity\Component\DataSentry\Trait\EntityPropertyTrait;
use ParadiseSecurity\Component\DataSentry\Trait\TypeTrait;
use ParadiseSecurity\Component\DataSentry\Attribute\AttributeInterface;
use ParadiseSecurity\Component\DataSentry\Encryptor\EncryptorInterface;

abstract class AbstractRequest implements RequestInterface
{
    use TypeTrait;
    use EntityPropertyTrait;
    use AttributesAwareTrait {
        addAttribute as traitAddAttribute;
    }

    /**
     * @var Collection|EncryptorInterface[]
     */
    private Collection $encryptors;

    /**
     * @var Collection|SubRequestInterface[]
     */
    private Collection $subRequests;

    protected bool $mappedTypedProperty = false;

    protected bool $migrateEncryption = false;

    protected array $encryptionMap = [];

    protected bool $forceEncrypt = false;

    protected bool $createBlindIndex = false;

    public function __construct()
    {
        $this->attributes = new ArrayCollection();
        $this->encryptors = new ArrayCollection();
        $this->subRequests = new ArrayCollection();
    }

    public function __clone()
    {
        $this->attributes = new ArrayCollection();
        $this->encryptors = new ArrayCollection();
        $this->subRequests = new ArrayCollection();
    }


    public function doMappedTypedProperty(): bool
    {
        return $this->mappedTypedProperty;
    }

    public function getMappedTypedProperty(): ?string
    {
        if ($this->mappedTypedProperty === false) {
            return null;
        }

        $className = AttributeInterface::ENCRYPTED_ATTRIBUTE;

        $attribute = $this->getAttributeByClassName($className);

        if (!is_null($attribute)) {
            return $attribute->mappedTypedProperty;
        }

        return null;
    }

    public function doForceEncrypt(): bool
    {
        return $this->forceEncrypt;
    }

    public function doMigrateEncryption(): bool
    {
        return $this->migrateEncryption;
    }

    public function getEncryptor(string $key): ?EncryptorInterface
    {
        if (isset($this->encryptionMap[$key])) {
            $key = $this->encryptionMap[$key];
        }

        if ($this->encryptors->containsKey($key)) {
            return $this->encryptors->get($key);
        }

        return null;
    }

    public function addEncryptor(EncryptorInterface $encryptor, string $key, string $type = RequestInterface::MAIN_ENCRYPTOR_TYPE): void
    {
        $this->encryptors->set($key, $encryptor);

        $this->encryptionMap[$type] = $key;
    }

    public function removeEncryptor(string $key): void
    {
        if ($this->encryptors->containsKey($key)) {
            $this->encryptors->remove($key);

            $type = array_search($key, $this->encryptionMap);

            unset($this->encryptionMap[$type]);
        }
    }

    public function getSubRequests(): Collection
    {
        return $this->subRequests;
    }

    public function addSubRequest(SubRequestInterface $subRequest): void
    {
        if (!$this->hasSubRequest($subRequest)) {
            $this->subRequests->add($subRequest);
            $subRequest->setParent($this);
            $this->processSubRequest($subRequest);
        }
    }

    public function removeSubRequest(SubRequestInterface $subRequest): void
    {
        if ($this->hasRequest($subRequest)) {
            $this->subRequests->removeElement($subRequest);
            $subRequest->setParent(null);
        }
    }

    public function hasSubRequest(SubRequestInterface $subRequest): bool
    {
        return $this->subRequests->contains($subRequest);
    }

    public function getSubRequest(string $type): ?SubRequestInterface
    {
        foreach ($this->subRequests->toArray() as $subRequest) {
            if ($subRequest->getType() === $type) {
                return $subRequest;
            }
        }

        return null;
    }

    public function doCreateBlindIndex(): bool
    {
        return $this->createBlindIndex;
    }

    public function getPropertyMapping(): array
    {
        return [
            'entity_hash' => $this->getEntityHash(),
            'name' => $this->getPropertyName(),
            'type' => $this->getType(),
            'original_value' => $this->getPropertyValue(),
        ];
    }

    public function addAttribute(AttributeInterface $attribute): void
    {
        $this->processAttribute($attribute);

        $this->traitAddAttribute($attribute);
    }

    protected function processAttribute(AttributeInterface $attribute): void
    {
        if (isset($attribute->mappedTypedProperty)) {
            $this->mappedTypedProperty = true;
        }

        if (isset($attribute->migration)) {
            $this->migrateEncryption = true;
        }

        if (isset($attribute->forceEncrypt)) {
            $this->forceEncrypt = $attribute->forceEncrypt;
        }

        if (isset($attribute->indexOf)) {
            $this->createBlindIndex = true;
        }
    }

    protected function processSubRequest(SubRequestInterface $request): void
    {
        if (!$request->hasAttributes()) {
            return;
        }

        foreach ($request->getAttributes()->toArray() as $attribute) {
            $this->processAttribute($attribute);
        }
    }
}

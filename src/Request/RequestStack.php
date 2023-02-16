<?php

declare(strict_types=1);

namespace ParadiseSecurity\Component\DataSentry\Request;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

class RequestStack implements RequestStackInterface
{
    /**
     * @var Collection|RequestInterface[]
     */
    private Collection $requests;

    public function __construct()
    {
        $this->requests = new ArrayCollection();
    }

    public function push(RequestInterface $request)
    {
        $this->addRequest($request);
    }

    public function contains(RequestInterface $request): bool
    {
        return $this->hasRequest($request);
    }

    public function clear(): void
    {
        $this->requests->clear();
    }

    public function pop(): ?RequestInterface
    {
        $request = $this->getCurrentRequest();

        $this->removeRequest($request);

        return $request;
    }

    public function count(): int
    {
        return $this->requests->count();
    }

    public function find(string $key): ?RequestInterface
    {
        foreach ($this->requests->toArray() as $request) {
            $name = $request->getPropertyName();
            if ($name === $key) {
                return $request;
            }
        }

        return null;
    }

    public function all(): Collection
    {
        return $this->requests;
    }

    private function getCurrentRequest(): ?RequestInterface
    {
        if ($this->requests->isEmpty()) {
            return null;
        }

        return $this->requests->first();
    }

    private function addRequest(RequestInterface $request): void
    {
        if (!$this->hasRequest($request)) {
            $this->requests->add($request);
        }
    }

    private function removeRequest(RequestInterface $request): void
    {
        if ($this->hasRequest($request)) {
            $this->requests->removeElement($request);
        }
    }

    private function hasRequest(RequestInterface $request): bool
    {
        return $this->requests->contains($request);
    }
}

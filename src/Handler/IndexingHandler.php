<?php

declare(strict_types=1);

namespace ParadiseSecurity\Component\DataSentry\Handler;

use ParadiseSecurity\Component\DataSentry\Generator\DelegatingGeneratorInterface;
use ParadiseSecurity\Component\DataSentry\Handler\IndexingHandlerInterface;
use ParadiseSecurity\Component\DataSentry\Model\IndexableInterface;
use ParadiseSecurity\Component\DataSentry\Request\RequestInterface;
use ReflectionClass;

class IndexingHandler implements IndexingHandlerInterface
{
    public function __construct(
        private DelegatingGeneratorInterface $indexesGenerator
    ) {
    }

    /**
     * @param RequestInterface $request
     *
     * @throws \ParadiseSecurity\Component\DataSentry\Exception\UndefinedGeneratorException
     * @throws \ReflectionException
     */
    public function handle(RequestInterface $request): void
    {
        $searchIndexes = $this->generateIndexableValuesForEntity($request);

        $refProperty = $request->getReflectionProperty();
        $indexableAnnotationConfig = $request->getIndexableFieldConfig();

        if (!isset($searchIndexes[$refProperty->getName()])) {
            return;
        }

        $indexesToEncrypt = $searchIndexes[$refProperty->getName()];

        $indexes = $this->generateBlindIndexesFromPossibleValues($request, $indexesToEncrypt);

        // We create the filter object instances and associate them with the parent entity
        $indexEntities = [];
        $indexEntityClass = $indexableAnnotationConfig->indexesEntityClass;

        $refClass = new ReflectionClass($indexEntityClass);

        foreach ($indexes as $index) {
            $indexEntity = $refClass->newInstance();
            if ($indexEntity instanceof IndexableInterface) {
                $indexEntity->setIndexBi($index);
                $indexEntity->setFieldname($refProperty->getName());
                $indexEntity->setTargetEntity(($entity = $request->getEntity()));
                $indexEntities [] = $indexEntity;
            }
        }

        $setter = 'set' . $refClass->getShortName();
        $entity->$setter($indexEntities);
    }

    private function generateBlindIndexesFromPossibleValues(
        RequestInterface $request,
        array $possibleValues,
    ): array {
        $possibleValues = array_unique($possibleValues);

        $indexes = [];
        foreach ($possibleValues as $pvalue) {
            if ($pvalue === '' || $pvalue === null) {
                continue;
            }
            $indexRequest = clone $request;
            $indexRequest->setOriginalFieldValue($pvalue);
            $indexes[] = $request->getMainEncryptor()->getBlindIndex($indexRequest);
        }

        return $indexes;
    }

    /**
     * Allows to generate the indexable values for an entity and a given context.
     *
     * @param RequestInterface $request
     * @return array
     * @throws \ParadiseSecurity\Component\DataSentry\Exception\UndefinedGeneratorException
     */
    private function generateIndexableValuesForEntity(RequestInterface $request): array
    {
        $searchIndexes = [];

        $refProperty = $request->getReflectionProperty();
        $indexableAnnotationConfig = $request->getIndexableFieldConfig();

        $value = $refProperty->getValue(($entity = $request->getEntity()));
        if ($value === null || $value === '') {
            return $searchIndexes;
        }

        $cleanValue = $value;
        $valueCleanerMethod = $indexableAnnotationConfig->valuePreprocessMethod ?? null;
        if ($valueCleanerMethod !== null && (method_exists($entity, $valueCleanerMethod) || method_exists(get_class($entity), $valueCleanerMethod))) {
            $cleanValue = $entity->$valueCleanerMethod($value);
        }

        // We call the filter index generation service which will create the collection of possible patterns depending on the method(s) entered in the annotation then retrieve each associated "blind_index" to be saved in base.
        $indexesMethods = $indexableAnnotationConfig->indexesGenerationMethods ?? [];

        $indexesToEncrypt = $this->indexesGenerator->generate($entity, $cleanValue, $indexesMethods);
        $indexesToEncrypt [] = $value;
        $indexesToEncrypt = array_unique($indexesToEncrypt);

        $searchIndexes[$refProperty->getName()] = $indexesToEncrypt;

        return $searchIndexes;
    }
}

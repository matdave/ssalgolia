<?php

namespace SSAlgolia\v3\Services;

use Algolia\AlgoliaSearch\Exceptions\MissingObjectId;
use Algolia\AlgoliaSearch\SearchClient;
use Algolia\AlgoliaSearch\SearchIndex;
use MODX\Revolution\modX;

class Algolia
{
    private $modx;

    private $algolia;

    private SearchIndex $index;

    public function __construct($modx)
    {
        $this->modx =& $modx;
    }

    public function initialize()
    {
        $this->algolia = SearchClient::create(
            $this->modx->getOption('ssalgolia.id'),
            $this->modx->getOption('ssalgolia.key')
        );
        $this->index = $this->algolia->initIndex(
            $this->modx->getOption('ssalgolia.index')
        );
    }

    public function saveObject($object, $key = 'id'): bool
    {
        $this->initialize();
        try {
            $this->index->saveObjects([$object], ['objectIDKey' => $key]);
            return true;
        } catch (\Exception $e) {
            $this->modx->log(modX::LOG_LEVEL_ERROR, 'Algolia Index Exception: ' . $e->getMessage());
            return false;
        }
    }

    public function saveObjects(array $objects, $key = 'id'): bool
    {
        $this->initialize();
        try {
            $this->index->saveObjects($objects, ['objectIDKey' => $key]);
            return true;
        } catch (\Exception $e) {
            $this->modx->log(modX::LOG_LEVEL_ERROR, 'Algolia Index Exception: ' . $e->getMessage());
            return false;
        }
    }

    public function removeObject($id): bool
    {
        $this->initialize();
        if ($this->index->deleteObjects([$id])) {
            return true;
        } else {
            return false;
        }
    }

    public function removeObjects(array $ids): bool
    {
        $this->initialize();
        if ($this->index->deleteObjects($ids)) {
            return true;
        } else {
            return false;
        }
    }

    public function searchString($string, $requestParams = [])
    {
        $this->initialize();
        $results = $this->index->search($string, $requestParams);
        return $results;
    }
}

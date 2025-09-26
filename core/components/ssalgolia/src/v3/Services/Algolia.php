<?php

namespace SSAlgolia\v3\Services;

use Algolia\AlgoliaSearch\Exceptions\MissingObjectId;
use Algolia\AlgoliaSearch\InsightsClient;
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

    public function trackClick($objectID, $queryID): void
    {
        $insights = InsightsClient::create(
            $this->modx->getOption('ssalgolia.id'),
            $this->modx->getOption('ssalgolia.key')
        );

        $event = [
            "eventName"=>"ssalgoliaClick",
            "eventType"=>"click",
            "index"=> $this->modx->getOption('ssalgolia.index'),
            "userToken"=> $this->getUserToken(),
            "objectIDs" => [$objectID],
            "queryID" => $queryID,
        ];

        $insights->sendEvent($event);
    }

    public function getUserToken(): string
    {
        $token = "0";
        if ($this->modx->user) {
            $token = (string) $this->modx->user->id;
        }
        if ($token === "0") {
            $token = $this->getUserIp();
        }
        return $token;
    }

    private function getUserIp(): string
    {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        //Check for multiple IPs from WAF
        $ip = explode(',', $ip);
        if (is_array($ip)) {
            return $ip[0];
        }
        return $ip;
    }
}

<?php

namespace SSAlgolia\v2\Driver;

use SSAlgolia\v2\Services\Algolia;

class AlgoliaDriver extends Driver
{
    private Algolia $algolia;
    public function initialize()
    {
        $this->algolia = new Algolia($this->modx);
    }

    public function search($string, array $scriptProperties = array())
    {
        $perPage = (int) $this->modx->getOption('perPage', $this->config, 10);
        $offset      = $this->modx->getOption('start', $this->config, 0);
        $offsetIndex = $this->modx->getOption('offsetIndex', $this->config, 'simplesearch_offset');

        if (isset($_REQUEST[$offsetIndex])) {
            $offset = (int) $_REQUEST[$offsetIndex];
        }

        $search = $this->algolia->searchString($string, $offset, $perPage);
        return [
            'results' => $search['hits'] ?? [],
            'total' => $search['nbHits'] ?? 0,
        ];
    }

    public function index(array $fields)
    {
        return true;
    }

    public function removeIndex($id)
    {
        return true;
    }
}

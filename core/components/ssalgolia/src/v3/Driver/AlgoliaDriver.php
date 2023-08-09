<?php

namespace SSAlgolia\v3\Driver;

use SimpleSearch\Driver\SimpleSearchDriverBasic;
use SSAlgolia\v3\Services\Algolia;

class AlgoliaDriver extends SimpleSearchDriverBasic
{
    private Algolia $algolia;
    private string $cacheKey = 'ssalgolia';
    public function initialize(): bool
    {
        $this->algolia = new Algolia($this->modx);
        return true;
    }

    public function search($string, array $scriptProperties = array())
    {
        $perPage     = (int) $this->modx->getOption('perPage', $this->config, 10);
        $offset      = $this->modx->getOption('start', $this->config, 0);
        $offsetIndex = $this->modx->getOption('offsetIndex', $this->config, 'simplesearch_offset');
        $docFields   = $this->modx->getOption('docFields', $scriptProperties, 'id,pagetitle,longtitle,alias,description,introtext,content');
        $context     = $this->modx->getOption('context', $scriptProperties, $this->modx->context->key);

        if (isset($_REQUEST[$offsetIndex])) {
            $offset = (int) $_REQUEST[$offsetIndex];
        }

        if ($perPage < 1) {
            $perPage = 10;
        }

        $contexts = explode(',', $context);
        $contextFilters = [];
        foreach ($contexts as $context) {
            $contextFilters[] = 'context_key:' . $context;
        }

        // make sure we always have the id field
        $docFields = explode(',', $docFields);
        if (!in_array('id', $docFields)) {
            $docFields[] = 'id';
        }
        $docFields = implode(',', $docFields);

        $requestParams = [
            'hitsPerPage' => $perPage,
            'page' => ($offset / $perPage),
            'attributesToRetrieve' => $docFields,
            'attributesToSnippet' => $docFields,
            'facetFilters' => $contextFilters,
            'restrictHighlightAndSnippetArrays' => true,
        ];
        $this->cacheKey = $this->cacheKey . md5(serialize($requestParams)) . md5($string);
        $cache = $this->modx->cacheManager->get($this->cacheKey);
        if ($cache) {
            return $cache;
        }
        $search = $this->algolia->searchString($string, $requestParams);

        $hits = $this->formatHits($search['hits'] ?? []);
        $results =  [
            'results' => $hits,
            'total' => $search['nbHits'] ?? 0,
        ];
        $this->modx->cacheManager->set($this->cacheKey, $results, 3600);
        return $results;
    }

    public function index(array $fields): bool
    {
        return true;
    }

    public function removeIndex($id): bool
    {
        return true;
    }

    private function formatHits($hits = []): array
    {
        $formatted = [];
        foreach ($hits as $hit) {
            $extract = '';
            if (isset($hit['_snippetResult'])) {
                foreach ($hit['_snippetResult'] as $field => $snippet) {
                    if ($field === 'id' || $field === 'alias') {
                        continue;
                    }
                    $extract .= $snippet['value'] . ' ';
                }
            }
            $hit['snippetResult'] = str_replace('… …', '…', trim($extract));
            $formatted[] = $hit;
        }
        return $formatted;
    }
}

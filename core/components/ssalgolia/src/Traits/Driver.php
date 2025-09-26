<?php

namespace SSAlgolia\Traits;

trait Driver
{
    public function search($string, array $scriptProperties = array())
    {
        $perPage     = (int) $this->modx->getOption('perPage', $this->config, 10);
        $offset      = $this->modx->getOption('start', $this->config, 0);
        $offsetIndex = $this->modx->getOption('offsetIndex', $this->config, 'simplesearch_offset');
        $docFields   = $this->modx->getOption('docFields', $scriptProperties, 'id,pagetitle,longtitle,alias,description,introtext,content');
        $context     = $this->modx->getOption('context', $scriptProperties, $this->modx->context->key);
        $facet     = $this->modx->getOption('facets', $scriptProperties, null);
        $analytics = $this->modx->getOption('analytics', $scriptProperties, true);
        $clickAnalytics = $this->modx->getOption('clickAnalytics', $scriptProperties, false);

        if (isset($_REQUEST[$offsetIndex])) {
            $offset = (int) $_REQUEST[$offsetIndex];
        }

        if ($perPage < 1) {
            $perPage = 10;
        }

        $contexts = explode(',', $context);
        $facetFilters = [];
        foreach ($contexts as $context) {
            $facetFilters[] = 'context_key:' . $context;
        }
        $facets = explode(',', $facet);
        foreach ($facets as $facet) {
            $facetFilters[] = $facet;
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
            'facetFilters' => $facetFilters,
            'restrictHighlightAndSnippetArrays' => true,
        ];
        if (!$analytics || $analytics === "false") {
            $requestParams['analytics'] = false;
        } else {
            $requestParams['userToken'] = $this->algolia->getUserToken();
        }
        if ($clickAnalytics) {
            $requestParams['clickAnalytics'] = true;
        }
        $this->cacheKey = $this->cacheKey . md5(serialize($requestParams)) . md5($string);
        $cache = $this->modx->cacheManager->get($this->cacheKey);
        if ($cache) {
            return $cache;
        }
        $search = $this->algolia->searchString($string, $requestParams);
        $queryId = null;
        if ($clickAnalytics) {
            $queryId = $search['queryID'] ?? null;
        }
        $hits = $this->formatHits($search['hits'] ?? [], $queryId);
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

    private function formatHits($hits = [], $queryId = null): array
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
            $hit['queryId'] = $queryId;
            $hit['snippetResult'] = str_replace('… …', '…', trim($extract));
            $formatted[] = $hit;
        }
        return $formatted;
    }
}
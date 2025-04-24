<?php

namespace SSAlgolia\v2\Traits;

use SSAlgolia\v2\AlgoliaRequest;
use SSAlgolia\v2\AlgoliaResponse;

trait Resource
{
    /**
     * A reference to the modX object.
     * @var \modX $modx
     */
    public $modx = null;
    public function getObject(int $id): array
    {
        $object = $this->modx->getObject('modResource', $id);
        if ($object) {
            $arr = $object->toArray();
            unset($arr['properties']);
            // only run if it's going to be indexed
            if ($arr['published'] && $arr['searchable'] && !$arr['deleted']) {
                $arr['link'] = $this->modx->makeUrl($id, $arr['context_key'], '', 'full');
                $arr['template_variables'] = $this->getTemplateVariables($arr['template'], $object);
                $arr['introtext'] = $this->cleanUpText($arr['introtext']);
                $arr['description'] = $this->cleanUpText($arr['description']);
                $arr['content'] = $this->getResourceContent($object);
            }
            return $arr;
        }
        return [];
    }

    private function getTemplateVariables(int $template, $object): array
    {
        $tvs = [];
        $TVTs = $this->modx->getCollection('modTemplateVarTemplate', ['templateid' => $template]);
        foreach ($TVTs as $TVT) {
            $tv = $TVT->getOne('TemplateVar');
            $tvs[$tv->get('name')] =  $this->cleanUpText($object->getTVValue($tv->id));
        }
        return $tvs;
    }

    private function getResourceContent($resource): string
    {
        $skipScan = $this->modx->getOption('ssalgolia.skip_scan_resources', [], '');
        $skipScan = explode(',', $skipScan);
        if ($resource->get('contentType') === 'text/html' &&
            !in_array($resource->id, $skipScan)) {
            $this->modx->switchContext($resource->get('context_key'));
            $content = $resource->getContent();
            $maxIterations = 10;
            $this->modx->resource = $resource;
            $this->modx->resourceIdentifier = $resource->get('id');
            $this->modx->elementCache = [];
            $this->modx->config['modResponse.class'] = AlgoliaResponse::class;
            $this->modx->response = new AlgoliaResponse($this->modx);
            $this->modx->request = new AlgoliaRequest($this->modx, [], [], [], []);
            $this->modx->parser->processElementTags('', $content, false, false, '[[', ']]', [], $maxIterations);
            $this->modx->parser->processElementTags('', $content, true, false, '[[', ']]', [], $maxIterations);
            $this->modx->parser->processElementTags('', $content, true, true, '[[', ']]', [], $maxIterations);
            $this->modx->config['modResponse.class'] = 'modResponse';
            $this->modx->response = new \modResponse($this->modx);
            return $this->cleanUpText($content);
        }
        return '';
    }

    private function cleanUpText($string): string
    {
        $removeCommonWords = $this->modx->getOption('ssalgolia.remove_common_words', [], false);
        $maxContentLength = $this->modx->getOption('ssalgolia.max_content_length', [], 0);
        $commonWords = explode(',', $this->modx->getOption('ssalgolia.common_words', [], ''));
        // Remove Tags
        $string = preg_replace('@<(script|style)[^>]*?>.*?</\\1>@si', '', $string);
        $string = strip_tags($string);
        // Remove Common Words
        if ($removeCommonWords) {
            foreach ($commonWords as &$word) {
                $word = '/\b' . preg_quote($word, '/') . '\b/iu';
            }
            $string = preg_replace($commonWords, '', $string);
        }
        // Remove Line Breaks
        $string = preg_replace('/[\r\n\t ]+/', ' ', $string);
        // Remove Double Spaces
        $string = preg_replace('/\s+/', ' ', $string);
        $string = trim($string);
        if ($maxContentLength > 1) {
            $string = substr($string, 0, $maxContentLength - 1);
        }
        $string = trim($string);
        return mb_convert_encoding($string, 'UTF-8', $this->modx->getOption('modx_charset', [], 'UTF-8'));
    }
}

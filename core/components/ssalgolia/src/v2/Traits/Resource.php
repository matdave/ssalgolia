<?php

namespace SSAlgolia\v2\Traits;

trait Resource
{
    private $modx;
    public function getObject(int $id): array
    {
        $object = $this->modx->getObject('modResource', $id);
        if ($object) {
            $arr = $object->toArray();
            $arr['url'] = $this->modx->makeUrl($id, $arr['context_key'], '', 'full');
            $arr['template_variables'] = $this->getTemplateVariables($arr['template'], $object);
            $arr['content'] = $this->getResourceContent($object);
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
            $tvs[$tv->get('name')] = $object->getTVValue($tv->id);
        }
        return $tvs;
    }

    private function getResourceContent($resource): string
    {
        if ($resource->get('contentType') === 'text/html') {
            $this->modx->switchContext($resource->get('context_key'));
            $content = $resource->getContent();
            $maxIterations = 10;
            $this->modx->resource = $resource;
            $this->modx->resourceIdentifier = $resource->get('id');
            $this->modx->elementCache = [];
            $this->modx->parser->processElementTags('', $content, false, false, '[[', ']]', [], $maxIterations);
            $this->modx->parser->processElementTags('', $content, true, false, '[[', ']]', [], $maxIterations);
            $this->modx->parser->processElementTags('', $content, true, true, '[[', ']]', [], $maxIterations);
            return $content;
        }
        return '';
    }
}

<?php

namespace SSAlgolia\v2\Elements\Event;

class OnHandleRequest extends Event
{
    public function run()
    {
        $objectKey = $this->getOption('ssalgolia.insights_object_key');
        $queryKey = $this->getOption('ssalgolia.insights.query_key');
        if (empty($objectKey) || empty($queryKey)) {
            return;
        }
        if (empty($_REQUEST[$objectKey]) || empty($_REQUEST[$queryKey])) {
            return;
        }

        $this->algolia->trackClick($_REQUEST[$objectKey], $_REQUEST[$queryKey]);
    }
}
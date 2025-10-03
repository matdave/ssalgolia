<?php

namespace SSAlgolia\v2\Elements\Event;

class OnHandleRequest extends Event
{
    public function run()
    {
        $objectKey = $this->getOption('ssalgolia.insights_object_key');
        $queryKey = $this->getOption('ssalgolia.insights_query_key');
        if (empty($objectKey) || empty($queryKey)) {
            return;
        }
        if (empty($_REQUEST[$objectKey]) || empty($_REQUEST[$queryKey])) {
            return;
        }

        $position = 1;
        $positionKey = $this->getOption('ssalgolia.insights_position_key');
        if (!empty($positionKey) && !empty($_REQUEST[$positionKey])) {
            $position = (int) $_REQUEST[$positionKey];
        }

        $this->algolia->trackClick($_REQUEST[$objectKey], $_REQUEST[$queryKey], $position);
    }
}
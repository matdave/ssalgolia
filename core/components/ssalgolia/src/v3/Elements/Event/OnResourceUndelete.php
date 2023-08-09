<?php

namespace SSAlgolia\v3\Elements\Event;

use SSAlgolia\v3\Traits\Resource;

class OnResourceUndelete extends Event
{
    use Resource;
    public function run()
    {
        $id = $this->getOption('id');
        $object = $this->getObject($id);
        if ($object) {
            if ($object['published'] && $object['searchable']) {
                $this->algolia->saveObject($object);
            }
        }
    }
}

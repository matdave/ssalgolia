<?php

namespace SSAlgolia\v2\Elements\Event;

use SSAlgolia\v2\Traits\Resource;

class OnDocPublished extends Event
{
    use Resource;
    public function run()
    {
        $id = $this->getOption('id');
        $object = $this->getObject($id);
        if ($object) {
            if (!$object['deleted']) {
                $this->algolia->saveObject($object);
            } else {
                $this->algolia->removeObject($id);
            }
        }
    }
}

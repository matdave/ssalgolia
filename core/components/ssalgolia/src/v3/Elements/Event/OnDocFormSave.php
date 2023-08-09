<?php

namespace SSAlgolia\v3\Elements\Event;

use SSAlgolia\v3\Traits\Resource;

class OnDocFormSave extends Event
{
    use Resource;
    public function run()
    {
        $id = $this->getOption('id');
        $mode = $this->getOption('mode', 'upd');
        $object = $this->getObject($id);
        if ($object) {
            if ($object['published'] && $object['searchable'] && !$object['deleted']) {
                $this->algolia->saveObject($object);
            } elseif ($mode == 'upd') {
                $this->algolia->removeObject($id);
            }
        }
    }
}

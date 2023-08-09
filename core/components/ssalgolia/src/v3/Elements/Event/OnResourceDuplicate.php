<?php

namespace SSAlgolia\v3\Elements\Event;

use SSAlgolia\v3\Traits\Resource;

class OnResourceDuplicate extends Event
{
    use Resource;
    public function run()
    {
        $newResource = $this->getOption('newResource');
        $duplicateChildren = $this->getOption('duplicateChildren');
        $publishedMode = $this->getOption('publishedMode');
        $id = $newResource->get('id');
        $object = $this->getObject($id);
        if ($object) {
            if ($object['published'] && $object['searchable'] && !$object['deleted']) {
                $this->algolia->saveObject($object);
            }
        }
        if ($duplicateChildren && $publishedMode !== 'unpublish') {
            $children = $this->modx->getChildIds($id);
            foreach ($children as $child) {
                $object = $this->getObject($child);
                if ($object) {
                    if ($object['published'] && $object['searchable'] && !$object['deleted']) {
                        $this->algolia->saveObject($object);
                    }
                }
            }
        }
    }
}

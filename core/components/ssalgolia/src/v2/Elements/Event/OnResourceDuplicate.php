<?php

namespace SSAlgolia\v2\Elements\Event;

use SSAlgolia\v2\Traits\Resource;

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
            if ($object['published'] && !$object['deleted']) {
                $this->algolia->saveObject($object);
            } else {
                $this->algolia->removeObject($id);
            }
        }
        if ($duplicateChildren && $publishedMode !== 'unpublish') {
            $children = $this->modx->getChildIds($id);
            foreach ($children as $child) {
                $object = $this->getObject($child);
                if ($object) {
                    if ($object['published'] && !$object['deleted']) {
                        $this->algolia->saveObject($object);
                    }
                }
            }
        }
    }
}

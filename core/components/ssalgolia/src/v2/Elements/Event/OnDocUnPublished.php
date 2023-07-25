<?php

namespace SSAlgolia\v2\Elements\Event;

class OnDocUnPublished extends Event
{
    public function run()
    {
        $id = $this->getOption('id');
        $this->algolia->removeObject($id);
    }
}

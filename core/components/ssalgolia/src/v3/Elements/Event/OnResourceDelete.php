<?php

namespace SSAlgolia\v3\Elements\Event;

class OnResourceDelete extends Event
{
    public function run()
    {
        $id = $this->getOption('id');
        $this->algolia->removeObject($id);
    }
}

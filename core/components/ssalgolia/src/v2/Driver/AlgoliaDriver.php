<?php

namespace SSAlgolia\v2\Driver;

use SSAlgolia\v2\Services\Algolia;

class AlgoliaDriver extends Driver
{
    use \SSAlgolia\Traits\Driver;
    private Algolia $algolia;
    private string $cacheKey = 'ssalgolia';
    public function initialize()
    {
        $this->algolia = new Algolia($this->modx);
    }
}

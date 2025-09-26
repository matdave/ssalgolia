<?php

namespace SSAlgolia\v3\Driver;

use SimpleSearch\Driver\SimpleSearchDriverBasic;
use SSAlgolia\Traits\Driver;
use SSAlgolia\v3\Services\Algolia;

class AlgoliaDriver extends SimpleSearchDriverBasic
{
    use Driver;
    private Algolia $algolia;
    private string $cacheKey = 'ssalgolia';
    public function initialize(): bool
    {
        $this->algolia = new Algolia($this->modx);
        return true;
    }
}

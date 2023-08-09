<?php

namespace SSAlgolia\v3\Cron;

use MODX\Revolution\modX;
use SSAlgolia\v3\Services\Algolia;
use SSAlgolia\v3\SSAlgolia;
use SSAlgolia\v3\Traits\Resource;

class Index
{
    use Resource;

    /**
     * A reference to the modX object.
     * @var modX $modx
     */
    public modX $modx;

    /**
     * reference to the main SSAlgolia service
     * @var SSAlgolia
     */
    protected SSAlgolia $ssa;

    protected Algolia $algolia;

    /** @var array */
    protected $sp = [];

    public function __construct($ssa, array $scriptProperties)
    {
        $this->ssa =& $ssa;
        $this->modx =& $this->ssa->modx;
        $this->sp = $scriptProperties;
        $this->algolia = new Algolia($this->modx);
    }

    public function run($ids = [])
    {
        $resources = [];
        $removeResources = [];
        foreach ($ids as $id) {
            $object = $this->getObject($id);
            if ($object) {
                if (!$object['deleted'] && $object['searchable'] && $object['published']) {
                    $resources[] = $object;
                } else {
                    $removeResources[] = $id;
                }
            }
        }
        if (!empty($resources)) {
            $this->algolia->saveObjects($resources);
        }
        if (!empty($removeResources)) {
            $this->algolia->removeObjects($removeResources);
        }
    }
}

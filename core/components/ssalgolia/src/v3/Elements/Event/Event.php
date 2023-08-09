<?php

namespace SSAlgolia\v3\Elements\Event;

use MODX\Revolution\modX;
use SSAlgolia\v3\Services\Algolia;
use SSAlgolia\v3\SSAlgolia;

abstract class Event
{
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

    abstract public function run();

    protected function getOption($key, $default = null, $skipEmpty = false)
    {
        return $this->modx->getOption($key, $this->sp, $default, $skipEmpty);
    }
}

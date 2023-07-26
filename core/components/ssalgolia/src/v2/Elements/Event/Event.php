<?php

namespace SSAlgolia\v2\Elements\Event;

use SSAlgolia\v2\Services\Algolia;

abstract class Event
{
    /**
     * A reference to the modX object.
     * @var \modX $modx
     */
    public $modx = null;

    /**
     * reference to the main SSAlgolia service
     * @var \SSAlgolia
     */
    protected $ssa;

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

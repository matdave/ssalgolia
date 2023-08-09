<?php
$fp = fopen('/tmp/ssalgolia_cron.txt', 'w+');
if(!flock($fp, LOCK_EX | LOCK_NB)) {
    return;
}

$tStart= microtime(true);
// Core Path
define('MODX_API_MODE', true);
@include(dirname(dirname(dirname(dirname(dirname(__FILE__))))) . '/config.core.php');

if (!@require_once(MODX_CORE_PATH . "vendor/autoload.php")) {
    exit();
}

ob_start();

$modx = new \MODX\Revolution\modX();
if (!is_object($modx) || !($modx instanceof \MODX\Revolution\modX)) {
    ob_get_level() && @ob_end_flush();
    exit();
}
$modx->startTime= $tStart;
$modx->initialize('web');
require_once MODX_CORE_PATH . 'model/modx/modrequest.class.php';
$modx->request = new \MODX\Revolution\modRequest($modx);

/** @var SSAlgolia\v3\SSAlgolia $ssalgolia */
$ssalgolia = $modx->services->get('ssalgolia');

$c = $modx->newQuery(\MODX\Revolution\modResource::class);
$c->select('id');
$collection = $modx->getIterator(\MODX\Revolution\modResource::class, $c);

$index = new \SSAlgolia\v3\Cron\Index($ssalgolia, []);

$ids = [];
foreach ($collection as $resource) {
    $ids[] = $resource->get('id');
}
$index->run($ids);

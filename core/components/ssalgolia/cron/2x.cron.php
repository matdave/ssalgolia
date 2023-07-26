<?php
$fp = fopen('/tmp/ssalgolia_cron.txt', 'w+');
if(!flock($fp, LOCK_EX | LOCK_NB)) {
    return;
}

$tStart= microtime(true);
// Core Path
$coreConfig = dirname(__FILE__, 5) . '/config.core.php';

require_once $coreConfig;
if (!defined('MODX_CORE_PATH')) {
    define('MODX_CORE_PATH', dirname(__FILE__, 5) . '/core/');
}
/* include the modX class */
if (!@include(MODX_CORE_PATH . "model/modx/modx.class.php")) {
    exit();
}

/* start output buffering */
ob_start();

/* Create an instance of the modX class */
$modx= new modX();
if (!is_object($modx) || !($modx instanceof modX)) {
    ob_get_level() && @ob_end_flush();
    exit();
}
$modx->startTime= $tStart;
$modx->initialize('web');
require_once MODX_CORE_PATH . 'model/modx/modrequest.class.php';
$modx->request = new \modRequest($modx);

$corePath = $modx->getOption('ssalgolia.core_path', null, $modx->getOption('core_path', null, MODX_CORE_PATH) . 'components/ssalgolia/');
/** @var SSAlgolia $ssalgolia */
$ssalgolia = $modx->getService(
    'ssalgolia',
    'SSAlgolia',
    $corePath . 'model/ssalgolia/',
    [
        'core_path' => $corePath
    ]
);

if (!($ssalgolia instanceof SSAlgolia)) return '';

$c = $modx->newQuery('modResource');
$c->select('id');
$collection = $modx->getIterator('modResource', $c);

$index = new \SSAlgolia\v2\Cron\Index($ssalgolia, []);

$ids = [];
foreach ($collection as $resource) {
    $ids[] = $resource->get('id');
}
$index->run($ids);

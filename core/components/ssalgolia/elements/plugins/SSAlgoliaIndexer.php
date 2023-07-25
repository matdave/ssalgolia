<?php

$corePath = $modx->getOption('ssalgolia.core_path', null, $modx->getOption('core_path', null, MODX_CORE_PATH) . 'components/ssalgolia/');
$ssa = $modx->getService(
    'ssalgolia',
    'SSAlgolia',
    $corePath . 'model/ssalgolia/',
    array(
        'core_path' => $corePath
    )
);

$className = "\\SSAlgolia\\v2\\Elements\\Event\\{$modx->event->name}";

if (class_exists($className)) {
    $event = new $className($ssa, $scriptProperties);
    $event->run();
} else {
    $modx->log(\xPDO::LOG_LEVEL_ERROR, "Class {$className} not found");
}

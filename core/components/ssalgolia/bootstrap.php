<?php
/**
 * @var \MODX\Revolution\modX $modx
 * @var array $namespace
 */

require_once $namespace['path'] . 'vendor/autoload.php';

$modx->services->add('ssalgolia', function($c) use ($modx) {
    return new \SSAlgolia\v3\SSAlgolia($modx);
});

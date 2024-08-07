<?php
/**
 * Copy this content to your module index to access core_facades components
 */
require_once(__DIR__ . '/../../../config.php');
require_once(__DIR__ . '/../../core_facades/autoload.php');
require_once(__DIR__ . '/../app.php');
use local_core_facades\Http\Routing\Request;

$request = Request::instance();
$router->target($request);

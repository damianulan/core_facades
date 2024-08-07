<?php
require_once(__DIR__.'/../core_facades/routes/router.php');
require_once(__DIR__.'/../core_facades/classes/helpers.php');

use local_core_facades\Http\Routing\Router;

$router = $GLOBALS['router'] ?? new Router(load_routes());
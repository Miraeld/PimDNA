<?php

/**
 * Front controller
 *
 * PHP version 7.0
 * @author: Gaël Robin
 */

/**
 * Composer
 */
require dirname(__DIR__) . '/vendor/autoload.php';

/**
 * Error and Exception handling
 */
error_reporting(E_ALL);
set_error_handler('Core\Error::errorHandler');
set_exception_handler('Core\Error::exceptionHandler');


/**
 * Routing
 */
$router = new Core\Router();

// Add the routes
$router->add('home', ['controller' => 'Home', 'action' => 'index']);
$router->add('checklogin', ['controller' => 'Login', 'action' => 'checklogin']);
$router->add('', ['controller' => 'Login', 'action' => 'login']);
$router->add('phpinfo', ['controller' => 'Phpinfo', 'action' => 'index']);
$router->add('md5', ['controller' => 'Md5', 'action' => 'index']);
$router->add('md5/results', ['controller' => 'Md5', 'action' => 'results']);
$router->add('md5/generate', ['controller' => 'Md5', 'action' => 'generate']);
$router->add('md5/compare', ['controller' => 'Md5', 'action' => 'compare']);
$router->add('md5/cron_compare', ['controller' => 'Md5', 'action' => 'compare_cron']);
$router->add('md5/generate_cron', ['controller' => 'Md5', 'action' => 'generate_cron']);

$router->add('delete', ['controller' => 'Delete', 'action' => 'index']);
$router->add('delete/del', ['controller' => 'Delete', 'action' => 'del']);

$router->add('settings', ['controller' => 'Settings', 'action' => 'index']);


$router->add('md5/generated', ['controller' => 'Md5', 'action' => 'index_generation']);


$router->add('md5/new_results', ['controller' => 'Md5', 'action' => 'results_bis']);

$router->add('test_one', ['controller' => 'Md5', 'action' => 'test_one']);
$router->add('test_two', ['controller' => 'Md5', 'action' => 'test_two']);
$router->add('md5/compare_test', ['controller' => 'Md5', 'action' => 'compare_test']);

// NEW COMPARE
$router->add('md5/compare_init', ['controller' => 'Md5', 'action' => 'compare_init']);
$router->add('md5/compare_compare', ['controller' => 'Md5', 'action' => 'compare_compare']);
$router->add('md5/compare_analyze', ['controller' => 'Md5', 'action' => 'compare_analyze']);
$router->add('md5/compare_finalyze', ['controller' => 'Md5', 'action' => 'compare_finalyze']);
$router->add('md5/test', ['controller' => 'Md5', 'action' => 'test']);
$router->add('md5/progress', ['controller' => 'Md5', 'action' => 'getCurrentProgress']);
$router->add('md5/gen_progress', ['controller' => 'Md5', 'action' => 'gen_progress']);
$router->add('md5/getInit', ['controller' => 'Md5', 'action' => 'getInitFile']);


$router->add('{controller}/{action}');

$router->dispatch($_SERVER['QUERY_STRING']);

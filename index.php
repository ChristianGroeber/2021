<?php

require __DIR__ . '/vendor/autoload.php';

use Nacho\Helpers\Request;
use Nacho\Security\JsonUserHandler;
use Nacho\Nacho;

session_start();
session_regenerate_id();

define('VIEWS_DIR', $_SERVER['DOCUMENT_ROOT'] . '/src/Views');
define('FILE_PATH', $_SERVER['DOCUMENT_ROOT'] . '/users.json');

if (isset($_SERVER['REDIRECT_URL'])) {
    $path = $_SERVER['REDIRECT_URL'];
} else {
    $path = $_SERVER['REQUEST_URI'];
}

function endswith($string, $test)
{
    $length = strlen($test);
    if( !$length ) {
        return true;
    }
    return substr( $string, -$length ) === $test;
}

function startsWith( $haystack, $needle ) {
    $length = strlen( $needle );
    return substr( $haystack, 0, $length ) === $needle;
}

if (endswith($path, '/') && $path !== '/') {
    $path = substr($path, 0, strlen($path) - 1);
}

function getRoute($path)
{
    $routes = json_decode(
        file_get_contents($_SERVER['DOCUMENT_ROOT'] . '/config/routes.json'),
        true
    );
    foreach ($routes as $route) {
        if ($route['route'] === $path) {
            return $route;
        }
    }
}

function getContent($route)
{
    $request = new Request();
    $userHandler = new JsonUserHandler();
    $nacho = new Nacho($request, $userHandler);
    if (isset($route['min_role']) && !$nacho->isGranted($route['min_role'])) {
        header('Http/1.1 302');
        header('Location: /login?required_page=' . $_SERVER['REDIRECT_URL']);
        die();
    }
    $controllerDir = $route['controller'];
    $cnt = new $controllerDir($nacho);
    $function = $route['function'];
    if (!method_exists($cnt, $function)) {
        header('Http/1.1 404');
        return "${function} does not exist in ${controllerDir}";
    }
    $request = new Request();
    return $cnt->$function($request);
}

$route = getRoute($path);
if (!$route) {
    $route = getRoute('/');
}
$content = getContent($route);
if ($content) {
    echo $content;
} else {
    $route = getRoute('/');
    $content = getContent($route);
    echo $content;
}

<?php
require_once __DIR__ . '/../bootstrap.php';

$requestMethod = $_SERVER['REQUEST_METHOD'];
$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// normalize route key with one space between method and URI
$route = trim($requestMethod) . ' ' . trim($requestUri);

// routes based on path
if (strpos($requestUri, '/api/') === 0) {
	$routes = require_once __DIR__ . '/../routes/api.php';
} else {
	$routes = require_once __DIR__ . '/../routes/web.php';
}

if (isset($routes[$route])) {
	[$controllerName, $method] = $routes[$route];
	$controllerClass = "App\\Controllers\\{$controllerName}";
	$controller = new $controllerClass();
	$controller->$method();
} else {
	http_response_code(404);
	echo 'Route not found';
	// temp debugging information
	error_log("Route not found: {$route}");
}

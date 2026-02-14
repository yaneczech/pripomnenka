<?php
/**
 * Připomněnka - Front Controller
 *
 * Vstupní bod aplikace. Všechny požadavky jsou směrovány sem.
 */

declare(strict_types=1);

// Definice základních konstant
define('ROOT_PATH', dirname(__DIR__));
define('PUBLIC_PATH', __DIR__);
define('START_TIME', microtime(true));

// Načtení konfigurace
$config = require ROOT_PATH . '/config/config.php';

// Nastavení časové zóny a locale
date_default_timezone_set($config['app']['timezone']);
setlocale(LC_ALL, $config['app']['locale'] . '.UTF-8');

// Chybové hlášení podle prostředí
if ($config['app']['debug']) {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
} else {
    error_reporting(0);
    ini_set('display_errors', '0');
    ini_set('log_errors', '1');
    ini_set('error_log', $config['paths']['logs'] . '/error.log');
}

// Bezpečnostní HTTP hlavičky
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');
header('Referrer-Policy: strict-origin-when-cross-origin');
header('Permissions-Policy: geolocation=(), microphone=(), camera=()');

// CSP hlavicka - povoluje Typekit fonty a Remixicon z jsDelivr
if (!$config['app']['debug']) {
    header("Content-Security-Policy: default-src 'self'; script-src 'self' https://use.typekit.net; style-src 'self' 'unsafe-inline' https://use.typekit.net https://p.typekit.net https://cdn.jsdelivr.net; font-src 'self' https://use.typekit.net https://p.typekit.net https://cdn.jsdelivr.net; img-src 'self' data: https://p.typekit.net;");
}

// Autoloader pro třídy
spl_autoload_register(function (string $class): void {
    // Převod namespace na cestu k souboru
    $prefix = '';
    $baseDir = ROOT_PATH . '/src/';

    $relativeClass = $class;
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';

    if (file_exists($file)) {
        require $file;
    }
});

// Načtení pomocných funkcí
require_once ROOT_PATH . '/src/Helpers/functions.php';

// Inicializace session a pomocných tříd
require_once ROOT_PATH . '/src/Helpers/Session.php';
require_once ROOT_PATH . '/src/Helpers/CSRF.php';
Session::start($config['security']['session_lifetime']);

// Načtení rout
$routes = require ROOT_PATH . '/config/routes.php';

// Získání HTTP metody a cesty
$method = $_SERVER['REQUEST_METHOD'];
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = rawurldecode($uri);

// Odstranění trailing slash (kromě root)
if ($uri !== '/' && str_ends_with($uri, '/')) {
    $uri = rtrim($uri, '/');
}

// Router - najít odpovídající routu
$matchedRoute = null;
$params = [];

foreach ($routes as $route => $handler) {
    [$routeMethod, $routePath] = explode(' ', $route, 2);

    if ($routeMethod !== $method) {
        continue;
    }

    // Převod parametrů v cestě na regex
    $pattern = preg_replace('/\{([a-zA-Z_]+)\}/', '(?P<$1>[^/]+)', $routePath);
    $pattern = '#^' . $pattern . '$#';

    if (preg_match($pattern, $uri, $matches)) {
        $matchedRoute = $handler;
        // Extrahovat pouze pojmenované parametry
        foreach ($matches as $key => $value) {
            if (is_string($key)) {
                $params[$key] = $value;
            }
        }
        break;
    }
}

// Zpracování požadavku
if ($matchedRoute === null) {
    // 404 - stránka nenalezena
    http_response_code(404);
    require ROOT_PATH . '/src/Views/errors/404.php';
    exit;
}

[$controllerName, $actionName, $middleware] = $matchedRoute;

// Kontrola middleware
if ($middleware === 'auth') {
    // Vyžaduje přihlášení zákazníka
    if (!Session::isLoggedIn()) {
        Session::set('redirect_after_login', $uri);
        redirect('/prihlaseni');
    }
} elseif ($middleware === 'admin') {
    // Vyžaduje přihlášení admina
    if (!Session::isAdmin()) {
        redirect('/admin/prihlaseni');
    }
} elseif ($middleware === 'cron') {
    // Kontrola CRON tokenu (timing-safe porovnání)
    $token = $_GET['token'] ?? '';
    $expectedToken = $config['security']['cron_token'] ?? '';
    if (!is_string($token) || !is_string($expectedToken) || $expectedToken === '' || !hash_equals($expectedToken, $token)) {
        http_response_code(403);
        echo 'Forbidden';
        exit;
    }
}

// Načtení a spuštění controlleru
$controllerClass = 'Controllers\\' . $controllerName;
$controllerFile = ROOT_PATH . '/src/Controllers/' . $controllerName . '.php';

if (!file_exists($controllerFile)) {
    http_response_code(500);
    error_log("Controller not found: {$controllerName}");
    require ROOT_PATH . '/src/Views/errors/500.php';
    exit;
}

require_once $controllerFile;

if (!class_exists($controllerClass)) {
    http_response_code(500);
    error_log("Controller class not found: {$controllerClass}");
    require ROOT_PATH . '/src/Views/errors/500.php';
    exit;
}

$controller = new $controllerClass($config);

if (!method_exists($controller, $actionName)) {
    http_response_code(500);
    error_log("Action not found: {$controllerClass}::{$actionName}");
    require ROOT_PATH . '/src/Views/errors/500.php';
    exit;
}

// Spuštění akce
try {
    $controller->$actionName($params);
} catch (Exception $e) {
    error_log("Exception: " . $e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine());

    if ($config['app']['debug']) {
        throw $e;
    }

    http_response_code(500);
    require ROOT_PATH . '/src/Views/errors/500.php';
}

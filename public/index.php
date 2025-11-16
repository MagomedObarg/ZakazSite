<?php

define('LARAVEL_START', microtime(true));

$_ENV['APP_ENV'] = $_ENV['APP_ENV'] ?? $_SERVER['APP_ENV'] ?? 'development';

require __DIR__ . '/../vendor/autoload.php';

// Get the request from the web server
$request = createRequestFromGlobals();
$app = \App\Application::create();
$response = $app->handle($request);

// Set status code
http_response_code($response->status());

// Send headers
foreach ($response->headers() as $header => $value) {
    header("{$header}: {$value}");
}

// Render response based on type
if ($response->isView()) {
    echo renderView($response->view(), $response->data());
} elseif ($response->isJson()) {
    echo $response->content();
} elseif ($response->isRedirect()) {
    // Redirect is already handled by Location header
} else {
    echo $response->content();
}

exit(0);

/**
 * Create Request object from PHP globals
 */
function createRequestFromGlobals(): \App\Http\Request
{
    // Extract path from REQUEST_URI, removing query string
    $uri = $_SERVER['REQUEST_URI'] ?? '/';
    $path = parse_url($uri, PHP_URL_PATH) ?? '/';
    
    // Remove /public from path if it exists (for some server configurations)
    if (strpos($path, '/public') === 0) {
        $path = substr($path, 7);
    }
    if ($path === '') {
        $path = '/';
    }

    $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
    $query = $_GET ?? [];
    $input = [];
    $headers = [];

    // Parse input from POST or JSON
    if (in_array($method, ['POST', 'PUT', 'PATCH'])) {
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        if (stripos($contentType, 'application/json') !== false) {
            $input = json_decode(file_get_contents('php://input'), true) ?? [];
        } else {
            $input = $_POST ?? [];
        }
    }

    // Collect headers
    foreach ($_SERVER as $key => $value) {
        if (strpos($key, 'HTTP_') === 0) {
            $headerName = str_replace('HTTP_', '', $key);
            $headerName = str_replace('_', '-', strtolower($headerName));
            $headers[$headerName] = $value;
        }
    }

    return \App\Http\Request::fromArray([
        'method' => $method,
        'path' => $path,
        'query' => $query,
        'input' => $input,
        'headers' => $headers,
    ]);
}

/**
 * Render a PHP view template
 */
function renderView(string $view, array $data = []): string
{
    $viewPath = __DIR__ . '/../resources/views/' . str_replace('.', '/', $view) . '.php';

    if (!file_exists($viewPath)) {
        http_response_code(404);
        return "View not found: {$view}";
    }

    ob_start();
    try {
        // Extract data into local scope
        extract($data, EXTR_SKIP);
        
        // Render the content file
        ob_start();
        include $viewPath;
        $__content = ob_get_clean();
        
        // Render with layout
        include __DIR__ . '/../resources/views/layout.php';
        
        return ob_get_clean();
    } catch (\Throwable $e) {
        ob_end_clean();
        return "Error rendering view: " . htmlspecialchars($e->getMessage());
    }
}

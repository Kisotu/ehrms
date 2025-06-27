<?php
// filepath: /opt/lampp/htdocs/simbiri-ehrms/public/index.php

// Start session
session_start();

// Set error reporting for development
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Define base paths
define('BASE_PATH', dirname(__DIR__));
define('PUBLIC_PATH', __DIR__);
define('VIEWS_PATH', BASE_PATH . '/views');
define('CONTROLLERS_PATH', BASE_PATH . '/controllers');
define('CONFIG_PATH', BASE_PATH . '/config');

// Simple autoloader for classes
spl_autoload_register(function ($class) {
    $file = CONTROLLERS_PATH . '/' . $class . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});

class Router {
    private $routes = [];
    private $currentRoute = '';
    
    public function __construct() {
        $this->currentRoute = $this->getCurrentRoute();
    }
    
    private function getCurrentRoute() {
        $uri = $_SERVER['REQUEST_URI'];
        $path = parse_url($uri, PHP_URL_PATH);
        
        // Remove the project folder from path if it exists
        $basePath = '/simbiri-ehrms/public';
        if (strpos($path, $basePath) === 0) {
            $path = substr($path, strlen($basePath));
        }
        
        return trim($path, '/');
    }
    
    public function get($route, $handler) {
        $this->addRoute('GET', $route, $handler);
    }
    
    public function post($route, $handler) {
        $this->addRoute('POST', $route, $handler);
    }
    
    private function addRoute($method, $route, $handler) {
        $this->routes[] = [
            'method' => $method,
            'route' => trim($route, '/'),
            'handler' => $handler
        ];
    }
    
    public function dispatch() {
        $requestMethod = $_SERVER['REQUEST_METHOD'];
        $matchedRoute = null;
        
        foreach ($this->routes as $route) {
            if ($route['method'] === $requestMethod && $this->matchRoute($route['route'], $this->currentRoute)) {
                $matchedRoute = $route;
                break;
            }
        }
        
        if ($matchedRoute) {
            $this->executeHandler($matchedRoute['handler']);
        } else {
            $this->handle404();
        }
    }
    
    private function matchRoute($routePattern, $currentRoute) {
        // Simple exact match for now - can be extended for parameters
        return $routePattern === $currentRoute;
    }
    
    private function executeHandler($handler) {
        if (is_callable($handler)) {
            call_user_func($handler);
        } elseif (is_string($handler)) {
            // Handle controller@method format
            if (strpos($handler, '@') !== false) {
                list($controller, $method) = explode('@', $handler);
                $controllerClass = $controller . 'Controller';
                
                if (class_exists($controllerClass)) {
                    $controllerInstance = new $controllerClass();
                    if (method_exists($controllerInstance, $method)) {
                        $controllerInstance->$method();
                        return;
                    }
                }
            }
            
            // Handle direct view rendering
            $this->renderView($handler);
        }
    }
    
    private function renderView($view, $data = []) {
        $viewFile = VIEWS_PATH . '/' . $view . '.php';
        
        if (file_exists($viewFile)) {
            extract($data);
            include $viewFile;
        } else {
            $this->handle404();
        }
    }
    
    private function handle404() {
        http_response_code(404);
        echo '<h1>404 - Page Not Found</h1>';
        echo '<p>The requested page could not be found.</p>';
    }
}

// Helper functions
function redirect($url) {
    header("Location: $url");
    exit;
}

function view($viewName, $data = []) {
    $viewFile = VIEWS_PATH . '/' . $viewName . '.php';
    
    if (file_exists($viewFile)) {
        extract($data);
        include $viewFile;
    } else {
        throw new Exception("View not found: $viewName");
    }
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function requireAuth() {
    if (!isLoggedIn()) {
        redirect('/simbiri-ehrms/public/login');
    }
}

// Initialize router
$router = new Router();

// Define routes
$router->get('', function() {
    if (isLoggedIn()) {
        redirect('/simbiri-ehrms/public/dashboard');
    } else {
        redirect('/simbiri-ehrms/public/login');
    }
});

$router->get('login', function() {
    if (isLoggedIn()) {
        redirect('/simbiri-ehrms/public/dashboard');
    }
    view('auth/login');
});

$router->post('login', function() {
    // Handle login form submission
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $role = $_POST['role'] ?? 'employee';
    
    // Simple authentication logic (replace with proper database authentication)
    if (!empty($username) && !empty($password)) {
        // TODO: Verify credentials against database
        // For now, accept any non-empty credentials
        $_SESSION['user_id'] = 1;
        $_SESSION['username'] = $username;
        $_SESSION['role'] = $role;
        
        // Redirect based on role
        if ($role === 'admin') {
            redirect('/simbiri-ehrms/public/admin/dashboard');
        } else {
            redirect('/simbiri-ehrms/public/dashboard');
        }
    } else {
        // Redirect back to login with error
        $_SESSION['error'] = 'Please fill in all fields';
        redirect('/simbiri-ehrms/public/login');
    }
});

$router->get('logout', function() {
    session_destroy();
    redirect('/simbiri-ehrms/public/login');
});

$router->get('dashboard', function() {
    requireAuth();
    
    if ($_SESSION['role'] === 'admin') {
        redirect('/simbiri-ehrms/public/admin/dashboard');
    }
    
    // Employee dashboard
    view('employee/dashboard', [
        'username' => $_SESSION['username']
    ]);
});

$router->get('admin/dashboard', function() {
    requireAuth();
    
    if ($_SESSION['role'] !== 'admin') {
        redirect('/simbiri-ehrms/public/dashboard');
    }
    
    view('admin/dashboard', [
        'username' => $_SESSION['username']
    ]);
});

// Employee routes
$router->get('profile', function() {
    requireAuth();
    view('employee/profile');
});

$router->get('attendance', function() {
    requireAuth();
    view('employee/attendance');
});

$router->get('payroll', function() {
    requireAuth();
    view('employee/payroll');
});

// Admin routes
$router->get('admin/employees', function() {
    requireAuth();
    if ($_SESSION['role'] !== 'admin') {
        redirect('/simbiri-ehrms/public/dashboard');
    }
    view('admin/employees');
});

$router->get('admin/reports', function() {
    requireAuth();
    if ($_SESSION['role'] !== 'admin') {
        redirect('/simbiri-ehrms/public/dashboard');
    }
    view('admin/reports');
});

// API routes for AJAX requests
$router->post('api/attendance/checkin', function() {
    requireAuth();
    header('Content-Type: application/json');
    
    // TODO: Implement check-in logic
    echo json_encode([
        'success' => true,
        'message' => 'Checked in successfully',
        'time' => date('Y-m-d H:i:s')
    ]);
});

$router->post('api/attendance/checkout', function() {
    requireAuth();
    header('Content-Type: application/json');
    
    // TODO: Implement check-out logic
    echo json_encode([
        'success' => true,
        'message' => 'Checked out successfully',
        'time' => date('Y-m-d H:i:s')
    ]);
});

// Handle the request
try {
    $router->dispatch();
} catch (Exception $e) {
    error_log($e->getMessage());
    http_response_code(500);
    echo '<h1>500 - Internal Server Error</h1>';
    echo '<p>Something went wrong. Please try again later.</p>';
}
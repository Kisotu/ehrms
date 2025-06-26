<?php
// filepath: /opt/lampp/htdocs/simbiri-ehrms/public/index.php

// Start session
session_start();

// Error reporting for development (remove in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Define constants
define('BASE_PATH', dirname(__DIR__));
define('APP_PATH', BASE_PATH . '/app');
define('CONFIG_PATH', BASE_PATH . '/config');
define('PUBLIC_PATH', __DIR__);

// Autoloader for classes
spl_autoload_register(function ($class) {
    $file = APP_PATH . '/' . str_replace('\\', '/', $class) . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});

// Load configuration
$config = [];
if (file_exists(CONFIG_PATH . '/app.php')) {
    $config = require CONFIG_PATH . '/app.php';
}

try {
    // Get the requested URI
    $requestUri = $_SERVER['REQUEST_URI'];
    $requestMethod = $_SERVER['REQUEST_METHOD'];
    
    // Remove query string and decode URI
    $path = parse_url($requestUri, PHP_URL_PATH);
    $path = urldecode($path);
    
    // Remove base path if running in subdirectory
    $basePath = dirname($_SERVER['SCRIPT_NAME']);
    if ($basePath !== '/') {
        $path = str_replace($basePath, '', $path);
    }
    
    // Remove trailing slash except for root
    $path = rtrim($path, '/') ?: '/';
    
    // Basic routing
    switch ($path) {
        case '/':
        case '/home':
            handleHome();
            break;
            
        case '/login':
            handleLogin();
            break;
            
        case '/logout':
            handleLogout();
            break;
            
        case '/dashboard':
            requireAuth();
            handleDashboard();
            break;
            
        case '/employees':
            requireAuth();
            handleEmployees();
            break;
            
        case '/attendance':
            requireAuth();
            handleAttendance();
            break;
            
        case '/leave':
            requireAuth();
            handleLeave();
            break;
            
        case '/payroll':
            requireAuth();
            handlePayroll();
            break;
            
        case '/reports':
            requireAuth();
            handleReports();
            break;
            
        case '/admin':
            requireAuth();
            requireAdmin();
            handleAdmin();
            break;
            
        default:
            handle404();
            break;
    }

} catch (Exception $e) {
    // Log error and show generic error page
    error_log("Application Error: " . $e->getMessage());
    handleError($e);
}

// Route handler functions
function handleHome() {
    if (isLoggedIn()) {
        redirect('/dashboard');
    }
    include APP_PATH . '/views/home.php';
}

function handleLogin() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Handle login form submission
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';
        
        // TODO: Implement authentication logic
        if (authenticate($username, $password)) {
            redirect('/dashboard');
        } else {
            $error = 'Invalid credentials';
            include APP_PATH . '/views/login.php';
        }
    } else {
        include APP_PATH . '/views/login.php';
    }
}

function handleLogout() {
    session_destroy();
    redirect('/login');
}

function handleDashboard() {
    include APP_PATH . '/views/dashboard.php';
}

function handleEmployees() {
    include APP_PATH . '/views/employees/index.php';
}

function handleAttendance() {
    include APP_PATH . '/views/attendance/index.php';
}

function handleLeave() {
    include APP_PATH . '/views/leave/index.php';
}

function handlePayroll() {
    include APP_PATH . '/views/payroll/index.php';
}

function handleReports() {
    include APP_PATH . '/views/reports/index.php';
}

function handleAdmin() {
    include APP_PATH . '/views/admin/index.php';
}

function handle404() {
    http_response_code(404);
    include APP_PATH . '/views/errors/404.php';
}

function handleError($exception) {
    http_response_code(500);
    include APP_PATH . '/views/errors/500.php';
}

// Utility functions
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

function requireAuth() {
    if (!isLoggedIn()) {
        redirect('/login');
    }
}

function requireAdmin() {
    if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
        http_response_code(403);
        include APP_PATH . '/views/errors/403.php';
        exit;
    }
}

function authenticate($username, $password) {
    // TODO: Implement database authentication
    // This is a placeholder - replace with actual database logic
    if ($username === 'admin' && $password === 'password') {
        $_SESSION['user_id'] = 1;
        $_SESSION['username'] = $username;
        $_SESSION['user_role'] = 'admin';
        return true;
    }
    return false;
}

function redirect($path) {
    $baseUrl = getBaseUrl();
    header("Location: $baseUrl$path");
    exit;
}

function getBaseUrl() {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    $basePath = dirname($_SERVER['SCRIPT_NAME']);
    return $protocol . '://' . $host . ($basePath !== '/' ? $basePath : '');
}

function asset($path) {
    return getBaseUrl() . '/assets/' . ltrim($path, '/');
}

function url($path) {
    return getBaseUrl() . '/' . ltrim($path, '/');
}
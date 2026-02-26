<?php
declare(strict_types=1);

/**
 * Front Controller for the Admin Area
 *
 * This file acts as the single entry point for all requests into the admin panel.
 * It initializes the application, dispatches the request to the correct handler
 * based on the URL, and manages the overall request lifecycle.
 */

// 1. Bootstrap the application
// This loads Composer's autoloader, sets up Eloquent, and starts the session.
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/database.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (function_exists('ob_start') && !headers_sent()) {
    ob_start('ob_gzhandler');
}

// 2. Set up the routing system
$dispatcher = FastRoute\simpleDispatcher(function(FastRoute\RouteCollector $r) {
    // The base path should match the actual directory structure
    // Extract from current request URI
    $requestUri = $_SERVER['REQUEST_URI'];
    $uriParts = explode('/', trim($requestUri, '/'));
    
    // Find the admin directory index
    $adminIndex = array_search('admin', $uriParts);
    if ($adminIndex !== false) {
        $basePath = '/' . implode('/', array_slice($uriParts, 0, $adminIndex + 1));
    } else {
        $basePath = '/admin'; // fallback
    }

    // DEBUG: Log the base path being used
    error_log("Router base path: $basePath");

    // Define routes. These map modern URLs to the legacy PHP files.
    // In the future, these could map to Controller@method syntax.
    $r->addRoute('GET', $basePath . '/all-bts', 'all-bts.php');
    $r->addRoute('POST', $basePath . '/load_all_bts', 'load_all_bts.php');
    
    // Routes for creating a new BTS record
    $r->addRoute('GET', $basePath . '/bts/create', 'bts.php');
    $r->addRoute('POST', $basePath . '/bts/create', 'bts.php');

    // Routes for editing an existing BTS record
    $r->addRoute('GET', $basePath . '/bts/{id:\d+}/edit', 'bts.php');
    $r->addRoute('POST', $basePath . '/bts/{id:\d+}/edit', 'bts.php');

    // Route for deleting a record
    $r->addRoute('POST', $basePath . '/delete_bts', 'delete_bts.php');
    $r->addRoute('POST', $basePath . '/delete_bts_image', 'delete_bts_image.php');

    // Routes for Core Team
    $r->addRoute('GET', $basePath . '/team', 'all-core-team.php');
    $r->addRoute('POST', $basePath . '/team', 'all-core-team.php'); // For create/update/delete
    $r->addRoute('GET', $basePath . '/team/create', 'core-team-form.php');
    $r->addRoute('POST', $basePath . '/team/create', 'core-team-form.php');
    $r->addRoute('GET', $basePath . '/team/{id:\d+}/edit', 'core-team-form.php');
    $r->addRoute('POST', $basePath . '/team/{id:\d+}/edit', 'core-team-form.php');
    $r->addRoute('POST', $basePath . '/team/delete', 'delete_core_team.php');
    $r->addRoute('POST', $basePath . '/team/{id:\d+}/delete', 'delete_core_team.php');

    // Panelists routes
    $r->addRoute('GET', $basePath . '/panelists', 'all-panelists.php');
    $r->addRoute('GET', $basePath . '/panelist/create', 'panelist-form.php');
    $r->addRoute('POST', $basePath . '/panelist/create', 'panelist-form.php');
    $r->addRoute('GET', $basePath . '/panelist/{id:\d+}/edit', 'panelist-form.php');
    $r->addRoute('POST', $basePath . '/panelist/{id:\d+}/edit', 'panelist-form.php');
    $r->addRoute('POST', $basePath . '/panelist/delete', 'delete_panelist.php');
    $r->addRoute('POST', $basePath . '/panelist/{id:\d+}/delete', 'delete_panelist.php');

    // Web Users
    $r->addRoute('GET', $basePath . '/web-users', 'web-users.php');
    $r->addRoute(['GET', 'POST'], $basePath . '/web-user/create', 'web-user-form.php');
    $r->addRoute('POST', $basePath . '/web-user/{id:\d+}/delete', function($vars) {
        if (!isset($_SESSION['user_type']) || !in_array($_SESSION['user_type'], ['webmaster', 'admin'])) {
            http_response_code(403);
            exit('Forbidden');
        }

        $user = \App\Models\WebUser::find((int)$vars['id']);
        if ($user) {
            $user->delete();
            $_SESSION['success_message'] = 'User deleted successfully!';
        } else {
            $_SESSION['error_message'] = 'User not found.';
        }
        header('Location: ' . $admin_path . 'web-users');
        exit;
    });

   

    // Routes for Categories
    $r->addRoute('GET', $basePath . '/all-categories', 'all-categories.php');
    $r->addRoute('POST', $basePath . '/load_all_categories', 'load_all_categories.php');
    $r->addRoute('GET', $basePath . '/categories/create', 'categories.php');
    $r->addRoute('POST', $basePath . '/categories/create', 'categories.php');
    $r->addRoute('GET', $basePath . '/categories/{id:\d+}/edit', 'categories.php');
    $r->addRoute('POST', $basePath . '/categories/{id:\d+}/edit', 'categories.php');
    $r->addRoute('POST', $basePath . '/categories/delete', 'delete_category.php');
    $r->addRoute('POST', $basePath . '/categories/{id:\d+}/delete', 'delete_category.php');
  

    // --- Season Management Routes ---
    // These routes handle the full lifecycle of seasons.
    $r->addRoute('GET', $basePath . '/all-seasons', 'all-seasons.php');
    $r->addRoute('GET', $basePath . '/seasons/create', 'seasons.php');
    $r->addRoute('POST', $basePath . '/seasons/create', 'seasons.php');
    $r->addRoute('GET', $basePath . '/seasons/{id:\d+}/edit', 'seasons.php');
    $r->addRoute('POST', $basePath . '/seasons/{id:\d+}/edit', 'seasons.php');
    $r->addRoute('POST', $basePath . '/seasons/delete', 'delete_season.php');
    $r->addRoute('POST', $basePath . '/seasons/{id:\d+}/delete', 'delete_season.php');
    $r->addRoute('POST', $basePath . '/set-default-season', 'set-default-season.php');
    $r->addRoute('POST', $basePath . '/seasons/{id:\d+}/set-default', 'set-default-season.php');
    // A simple dashboard route for the homepage of the admin area
    $r->addRoute('GET', $basePath . '/dashboard', 'dashboard.php');
    $r->addRoute('GET', $basePath . '/', 'dashboard.php'); // Root of admin area

    // Route for the access denied page
    $r->addRoute('GET', $basePath . '/access-denied', 'access-denied.php');

    // Download Files Routes
    $r->addRoute('GET', $basePath . '/download-files', 'all-download-files.php');
    $r->addRoute('GET', $basePath . '/download-files/create', 'download-file-form.php');
    $r->addRoute('POST', $basePath . '/download-files/create', 'download-file-form.php');
    $r->addRoute('GET', $basePath . '/download-files/{id:\d+}/edit', 'download-file-form.php');
    $r->addRoute('POST', $basePath . '/download-files/{id:\d+}/edit', 'download-file-form.php');
    $r->addRoute('POST', $basePath . '/download-files/delete', 'delete_download_file.php');
    $r->addRoute('POST', $basePath . '/download-files/{id:\d+}/delete', 'delete_download_file.php');

    // Routes for Enrollments
    $r->addRoute('GET', $basePath . '/enrollments', 'all-enrollments.php');
    $r->addRoute('GET', $basePath . '/enrollments/create', 'enrollments.php');
    $r->addRoute('POST', $basePath . '/enrollments/create', 'enrollments.php');
    $r->addRoute('GET', $basePath . '/enrollments/{id:\d+}/edit', 'enrollments.php');
    $r->addRoute('POST', $basePath . '/enrollments/{id:\d+}/edit', 'enrollments.php');
    $r->addRoute('POST', $basePath . '/enrollments/delete', 'delete-enrollments.php');
    $r->addRoute('POST', $basePath . '/enrollments/{id:\d+}/delete', 'delete-enrollments.php');


    // Routes for Testimonials
    $r->addRoute('GET', $basePath . '/all-testimonials', 'all-testimonials.php');
    $r->addRoute('POST', $basePath . '/load_all_testimonials', 'load_all_testimonials.php');
    $r->addRoute('GET', $basePath . '/testimonials/create', 'testimonials.php');
    $r->addRoute('POST', $basePath . '/testimonials/create', 'testimonials.php');
    $r->addRoute('GET', $basePath . '/testimonials/{id:\d+}/edit', 'testimonials.php');
    $r->addRoute('POST', $basePath . '/testimonials/{id:\d+}/edit', 'testimonials.php');
    $r->addRoute('POST', $basePath . '/testimonials/delete', 'delete_testimonial.php');
    $r->addRoute('POST', $basePath . '/testimonials/{id:\d+}/delete', 'delete_testimonial.php');

    //Routes for News
    $r->addRoute('GET' , $basePath . '/all-news', 'all-news.php');
    $r->addRoute('POST' , $basePath . '/news/create', 'news.php');
    $r->addRoute('GET' , $basePath . '/news/create', 'news.php');
    $r->addRoute('GET' , $basePath . '/news/{id:\d+}/edit', 'news.php');
    $r->addRoute('POST' , $basePath . '/news/{id:\d+}/edit', 'news.php');
    $r->addRoute('POST' , $basePath . '/news/delete', 'delete_news.php');
    $r->addRoute('POST' , $basePath . '/news/{id:\d+}/delete', 'delete_news.php');

    // Winners
    $r->addRoute('GET', $basePath . '/all-winners', 'all-winners.php');
    $r->addRoute('GET', $basePath . '/winners/create', 'winners.php');
    $r->addRoute('POST', $basePath . '/winners/create', 'winners.php');
    $r->addRoute('GET', $basePath . '/winners/{id:\d+}/edit', 'winners.php');
    $r->addRoute('POST', $basePath . '/winners/{id:\d+}/edit', 'winners.php');
    $r->addRoute('POST', $basePath . '/winners/{id:\d+}/delete', 'delete_winner.php');

    // Orders
    $r->addRoute('GET', $basePath . '/orders', 'all-orders.php');
    $r->addRoute(['GET', 'POST'], $basePath . '/orders/{id:\d+}/edit', 'order-edit.php');
    // If needed later:
    // $r->addRoute('POST', $basePath . '/orders/delete', 'delete-order.php');

    // Web Users details view
    $r->addRoute('GET', $basePath . '/web-user/{id:\d+}', 'web-user-details.php');
    $r->addRoute('GET', $basePath . '/web-user/{id:\d+}/edit', 'web-user-edit.php');
    $r->addRoute('POST', $basePath . '/web-user/{id:\d+}/edit', 'web-user-edit.php');

    // Redirect for duplicated /web-user/ segment
    $r->addRoute(['GET', 'POST'], $basePath . '/web-user/web-user/{action}', function($vars) use ($basePath) {
        header('Location: ' . $basePath . '/web-user/' . $vars['action']);
        exit;
    });

    // Profile Page
    $r->addRoute(['GET', 'POST'], $basePath . '/profile', 'profile.php');
    $r->addRoute('GET', $basePath . '/web-user/web-users', function($vars) use ($basePath) {
        header('Location: ' . $basePath . '/web-users');
        exit;
    });
    $r->addRoute('GET', $basePath . '/web-user/{id:\d+}/web-users', function($vars) use ($basePath) {
        error_log("Router called: Method={$_SERVER['REQUEST_METHOD']}, URI={$_SERVER['REQUEST_URI']}");
        header('Location: ' . $basePath . '/web-users');
        exit;
    });
});

// 3. Dispatch the request 
$httpMethod = $_SERVER['REQUEST_METHOD'];
$uri = $_SERVER['REQUEST_URI'];

// Strip query string (e.g., ?foo=bar) and decode the URI
if (false !== $pos = strpos($uri, '?')) {
    $uri = substr($uri, 0, $pos);
}
$uri = rawurldecode($uri);

$routeInfo = $dispatcher->dispatch($httpMethod, $uri);

// 4. Handle the route matching result
switch ($routeInfo[0]) {
    case FastRoute\Dispatcher::NOT_FOUND:
        http_response_code(404);
        // You might want to create a simple 404.php page
        echo '404 Not Found'; 
        break;

    case FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
        http_response_code(405);
        echo '405 Method Not Allowed';
        break;

    case FastRoute\Dispatcher::FOUND:
        $handler = $routeInfo[1];
        $vars = $routeInfo[2];
        if (is_callable($handler)) {
            $handler($vars);
            break;
        } else {
            if (!empty($vars)) {
                $_GET = array_merge($_GET, $vars);
            }
            require $handler;
        }
        break;
}
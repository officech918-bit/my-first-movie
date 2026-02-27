<?php

namespace App\Middleware;

require_once __DIR__ . '/../../admin/inc/requires.php';

class CategoryMiddleware
{
    public function handle()
    {
        // Only run this middleware for category-related routes.
        // If the URL does not contain 'categories', do nothing.
        if (strpos($_SERVER['REQUEST_URI'], 'categories') === false) {
            return;
        }

        // The visitor class is needed for its session checking logic.
        $user = new \visitor();

        // If the user is not logged in, deny access.
        if (!$user->check_session()) {
            // Get dynamic admin path
            $scheme = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
            $host = $_SERVER['HTTP_HOST'];
            $requestUri = $_SERVER['REQUEST_URI'];

            // Extract the actual path from the current request
            $uriParts = explode('/', trim($requestUri, '/'));
            $adminIndex = array_search('admin', $uriParts);

            if ($adminIndex !== false) {
                $admin_path = $scheme . '://' . $host . '/' . implode('/', array_slice($uriParts, 0, $adminIndex + 1)) . '/';
            } else {
                $admin_path = $scheme . '://' . $host . '/admin/'; // fallback
            }
            
            header('Location: ' . $admin_path . 'access-denied.php');
            exit;
        }

        // Allow 'webmaster' and 'admin' to access, deny everyone else.
        if (!isset($_SESSION['user_type']) || !in_array($_SESSION['user_type'], ['webmaster', 'admin'])) {
            // Get dynamic admin path
            $scheme = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
            $host = $_SERVER['HTTP_HOST'];
            $requestUri = $_SERVER['REQUEST_URI'];

            // Extract the actual path from the current request
            $uriParts = explode('/', trim($requestUri, '/'));
            $adminIndex = array_search('admin', $uriParts);

            if ($adminIndex !== false) {
                $admin_path = $scheme . '://' . $host . '/' . implode('/', array_slice($uriParts, 0, $adminIndex + 1)) . '/';
            } else {
                $admin_path = $scheme . '://' . $host . '/admin/'; // fallback
            }
            
            header('Location: ' . $admin_path . 'access-denied.php');
            exit;
        }
    }
}
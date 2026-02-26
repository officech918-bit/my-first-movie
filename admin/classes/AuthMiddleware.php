<?php

namespace App\Middleware;

class AuthMiddleware
{
    public function handle(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
                // CSRF token is invalid, reject the request
                header('HTTP/1.1 403 Forbidden');
                exit('Invalid CSRF token');
            }
        }

        if (!isset($_SESSION['uid']) || empty($_SESSION['uid'])) {
            // For non-API requests, redirect.
            // For future API requests, we might return a 401 JSON response.
            
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
            
            header("Location: " . $admin_path . "index.php");
            exit();
        }
    }
}
<?php

declare(strict_types=1);

namespace App\Middleware;

// We need to load the legacy files to access session functions and user classes.
require_once __DIR__ . '/../../admin/inc/requires.php';

class AuthMiddleware
{
    /**
     * Handle an incoming request.
     *
     * This middleware checks if a user is authenticated by looking for
     * a 'uid' in the session. If not found, it redirects to the login page.
     */
    public function handle(): void
    {
        // Start session if it's not already active
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Check for the existence of the user ID in the session
        if (!isset($_SESSION['uid'])) {
            $this->redirectToLogin();
        }

        // After confirming the user is logged in, check their role.
        // Only 'webmaster' and 'admin' are allowed.
        $allowedUserTypes = ['webmaster', 'admin'];
        if (!isset($_SESSION['user_type']) || !in_array($_SESSION['user_type'], $allowedUserTypes, true)) {
            $this->redirectToAccessDenied();
        }
    }

    /**
     * Redirects the user to the login page and terminates the script.
     */
    private function redirectToLogin(): void
    {
        session_regenerate_id(true);
        
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
        
        header('Location: ' . $admin_path . 'index.php');
        exit();
    }

    /**
     * Redirects the user to the access denied page and terminates the script.
     */
    private function redirectToAccessDenied(): void
    {
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
        
        header('Location: ' . $admin_path . 'access-denied');
        exit();
    }
}
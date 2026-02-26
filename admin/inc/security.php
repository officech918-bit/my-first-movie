<?php








/**
 * Validates the CSRF token for POST requests.
 * Dies with a 403 error if the token is invalid.
 */
function validate_csrf_token(): void
{
    // Only validate for state-changing methods
    if (in_array($_SERVER['REQUEST_METHOD'], ['POST', 'PUT', 'DELETE', 'PATCH'], true)) {
        if (
            empty($_POST['csrf_token']) ||
            empty($_SESSION['csrf_token']) ||
            !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])
        ) {
            http_response_code(403);
            die('Invalid CSRF token.');
        }
    }
}

/**
 * Generates and stores a CSRF token if one doesn't exist.
 */
function generate_csrf_token(): void
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
}

/**
 * Checks if a user is authenticated. If not, redirects to the login page.
 */
function require_login(): void
{
    if (!isset($_SESSION['user_type'])) {
        // Use a more robust relative path for the redirect
        header("Location: index.php");
        exit();
    }
}

/**
 * Checks if the current user has a specific role
 *
 * @param string $role The role to check for.
 * @return bool True if the user has the role, false otherwise.
 */
function has_role(string $role): bool
{
    return isset($_SESSION['user_type']) && $_SESSION['user_type'] === $role;
}

/**
 * Restricts access to a page to specific roles.
 *
 * @param array $roles An array of allowed roles.
 */
function require_role(array $roles): void
{
    require_login();
    if (!in_array($_SESSION['user_type'], $roles, true)) {
        http_response_code(403);
        die('You do not have permission to access this page.');
    }
}



?>
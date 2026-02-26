<?php
declare(strict_types=1);

namespace App;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

/**
 * Mailer Class
 *
 * Handles sending emails using PHPMailer and SMTP settings from the .env file.
 */
class Mailer
{
    private PHPMailer $mail;
    private string $fromAddress;
    private string $fromName;

    public function __construct()
    {
        $this->mail = new PHPMailer(true); // Enable exceptions
        $this->setupMailer();
    }

    /**
     * Sets up PHPMailer with SMTP configuration from environment variables.
     */
    private function setupMailer(): void
    {
        try {
            // Server settings
            $this->mail->isSMTP();
            $this->mail->Host = isset($_ENV['MAIL_HOST']) ? $_ENV['MAIL_HOST'] : 'localhost';
            $this->mail->SMTPAuth   = true;
            $this->mail->Username = isset($_ENV['MAIL_USERNAME']) ? $_ENV['MAIL_USERNAME'] : '';
            $this->mail->Password = isset($_ENV['MAIL_PASSWORD']) ? $_ENV['MAIL_PASSWORD'] : '';
            $this->mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // Use TLS by default
            if (isset($_ENV['MAIL_ENCRYPTION']) && strtolower($_ENV['MAIL_ENCRYPTION']) === 'ssl') {
                $this->mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            }
            $this->mail->Port = (int) (isset($_ENV['MAIL_PORT']) ? $_ENV['MAIL_PORT'] : 587);

            // Recipients
            $this->fromAddress = isset($_ENV['MAIL_FROM_ADDRESS']) ? $_ENV['MAIL_FROM_ADDRESS'] : 'no-reply@example.com';
            $this->fromName = isset($_ENV['MAIL_FROM_NAME']) ? $_ENV['MAIL_FROM_NAME'] : 'Mailer';
            $this->mail->setFrom($this->fromAddress, $this->fromName);

            // Content
            $this->mail->isHTML(true); // Set email format to HTML
            $this->mail->CharSet = PHPMailer::CHARSET_UTF8;

        } catch (Exception $e) {
            error_log("Mailer setup error: {$e->getMessage()}");
            // Depending on your error handling strategy, you might re-throw or handle differently
        }
    }

    /**
     * Sends an email.
     *
     * @param string $to The recipient's email address.
     * @param string $subject The email subject.
     * @param string $body The HTML content of the email.
     * @param string $altBody The plain text alternative body (optional).
     * @return bool True on success, false on failure.
     */
    public function send(string $to, string $subject, string $body, string $altBody = ''): bool
    {
        try {
            $this->mail->clearAddresses(); // Clear all addresses for the current message
            $this->mail->addAddress($to);

            $this->mail->Subject = $subject;
            $this->mail->Body    = $body;
            $this->mail->AltBody = $altBody ?: strip_tags($body); // Generate alt body if not provided

            $this->mail->send();
            return true;
        } catch (Exception $e) {
            error_log("Email to {$to} failed: {$e->getMessage()}");
            return false;
        }
    }

    /**
     * Renders an email template.
     *
     * @param string $templateName The name of the template file (e.g., 'welcome').
     * @param array $data Associative array of data to pass to the template.
     * @return string The rendered HTML content.
     */
    public function renderTemplate(string $templateName, array $data = []): string
    {
        // Ensure the template path is correct.
        // Assuming templates are in a 'templates/emails' directory relative to the project root.
        $templatePath = dirname(__DIR__) . "/templates/emails/{$templateName}.php";

        if (!file_exists($templatePath)) {
            error_log("Email template not found: {$templatePath}");
            return "<h1>Error: Template not found!</h1><p>Subject: " . (isset($data['subject']) ? $data['subject'] : 'N/A') . "</p>";
        }

        // Extract data into local variables for the template
        extract($data);

        // Start output buffering to capture the template output
        ob_start();
        include $templatePath;
        return ob_get_clean();
    }
}
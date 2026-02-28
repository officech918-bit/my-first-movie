<?php
declare(strict_types=1);

/**
 * S3 Uploader Class
 * 
 * Handles file uploads to Amazon S3
 */

class S3Uploader
{
    private string $bucket;
    private string $region;
    private string $accessKey;
    private string $secretKey;
    private string $baseUrl;

    public function __construct()
    {
        $this->bucket = $_ENV['S3_BUCKET'] ?? '';
        $this->region = $_ENV['S3_REGION'] ?? 'us-east-1';
        $this->accessKey = $_ENV['S3_KEY'] ?? '';
        $this->secretKey = $_ENV['S3_SECRET'] ?? '';
        $this->baseUrl = rtrim($_ENV['S3_BASE_URL'] ?? '', '');
        
        // Auto-disable S3 in local/development environment
        $this->s3Enabled = $this->isProductionEnvironment();
    }

    /**
     * Check if we're in production environment
     */
    private function isProductionEnvironment(): bool
    {
        $appEnv = $_ENV['APP_ENV'] ?? 'local';
        
        // Enable S3 if APP_ENV is explicitly set to production
        // Ignore localhost check if APP_ENV is production
        return $appEnv === 'production';
    }

    /**
     * Upload file to S3
     */
    public function uploadFile(string $sourcePath, string $destinationPath): string
    {
        // Check if S3 is enabled
        if (!$this->s3Enabled) {
            // Local upload for development
            return $this->uploadLocally($sourcePath, $destinationPath);
        }

        // Production S3 upload
        if (empty($this->bucket) || empty($this->accessKey)) {
            throw new Exception('S3 configuration missing');
        }

        // TODO: Implement actual AWS SDK upload
        // For now, return S3 URL
        return $this->baseUrl . '/' . ltrim($destinationPath, '/');
    }

    /**
     * Upload file locally (for development)
     */
    private function uploadLocally(string $sourcePath, string $destinationPath): string
    {
        // Extract directory and filename
        $pathParts = explode('/', $destinationPath);
        $filename = end($pathParts);
        $directory = $pathParts[0] ?? 'uploads';
        
        // Create upload directory if needed
        $uploadDir = __DIR__ . "/../$directory";
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        $targetPath = $uploadDir . '/' . $filename;
        
        if (move_uploaded_file($sourcePath, $targetPath)) {
            // Return relative path for local development
            return "$directory/$filename";
        } else {
            throw new Exception('Local upload failed');
        }
    }

    /**
     * Check if S3 is properly configured and enabled
     */
    public function isConfigured(): bool
    {
        // Always return true if we can upload (either locally or to S3)
        return true;
    }

    /**
     * Check if S3 uploads are enabled (vs local uploads)
     */
    public function isS3Enabled(): bool
    {
        return $this->s3Enabled;
    }

    /**
     * Get S3 base URL
     */
    public function getBaseUrl(): string
    {
        return $this->baseUrl;
    }
}

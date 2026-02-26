# S3 Image Storage Setup

## Quick Setup

1. **Create your .env file** (copy from `.env.example`):
   ```bash
   cp .env.example .env
   ```

2. **Add your S3 URL to .env**:
   ```
   S3_BASE_URL=https://your-bucket-name.s3.amazonaws.com
   ```

3. **That's it!** Your images will now use S3 URLs.

## How It Works

- **Local Storage**: If `S3_BASE_URL` is empty, images are stored locally
- **S3 Storage**: If `S3_BASE_URL` is set, S3 URLs are stored in the database
- **Automatic Detection**: The system automatically detects S3 vs local URLs
- **Fallback**: If an image fails to load, a placeholder is shown
- **Dynamic Base Path**: System automatically detects correct base path (`/myfirstmovie3/`)
- **Smart Path Handling**: Handles both full paths and filenames in database

## Supported Modules

✅ **Categories**: Category images and thumbnails  
✅ **Testimonials**: Client logos and thumbnails  
✅ **Enrollments**: User uploaded documents and files  
✅ **Winners**: Winner photos and images  
✅ **Behind the Scenes (BTS)**: Screenshots and gallery images  
✅ **Call for Entry**: Category images  
✅ **Home Page**: BTS, Testimonials, News sections  
✅ **Future Modules**: Easy to extend to other modules

## Example S3 URLs

```
# AWS S3
S3_BASE_URL=https://my-bucket.s3.amazonaws.com

# DigitalOcean Spaces
S3_BASE_URL=https://my-bucket.nyc3.digitaloceanspaces.com

# Cloudflare R2
S3_BASE_URL=https://my-bucket.r2.cloudflarestorage.com
```

## Implementation Details

### Admin Panel S3 Support
- **Categories**: `admin/categories.php`, `admin/all-categories.php`
- **Testimonials**: `admin/testimonials.php`, `admin/all-testimonials.php`
- **Winners**: `admin/winners.php`, `admin/all-winners.php`
- **BTS**: `admin/bts.php`, `admin/all-bts.php`
- **Enrollments**: `admin/view-enrollment.php`
- **Call for Entry**: `call-for-entry.php`

### Public Pages S3 Support
- **Winners**: `winners-history.php`, `selecteds.php`
- **Home Page**: `index.php` (BTS, Testimonials, News sections)
- **Behind the Scenes**: `behind-the-scenes.php`
- **Call for Entry**: `call-for-entry.php`

## Current Behavior

- **Upload**: Files are still saved locally as backup (until full S3 upload is implemented)
- **Database**: Stores S3 URLs when enabled
- **Display**: Automatically shows S3 or local images
- **Fallback**: Shows placeholder if image fails to load
- **URL Generation**: Dynamic base path detection prevents hardcoded path issues

## File Structure

```
uploads/
├── categories/          # Category images
├── testimonials/        # Testimonial logos
├── winners/            # Winner photos
├── bts/                # BTS screenshots and gallery
├── news/               # News images
├── [user_id]/          # Enrollment files by user
│   ├── [enrollment_id]_category/
│   └── [category]/
└── [other modules]/     # Future modules

admin/uploads/
├── categories/          # Category images (admin)
├── bts/                # BTS images (admin)
├── winners/            # Winner photos (admin)
└── [other modules]/     # Future modules
```

## Technical Implementation

### Environment Variable Loading
```php
// Load environment variables from .env file
if (class_exists('Dotenv\Dotenv')) {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/');
    $dotenv->load();
}
```

### Dynamic Base Path Detection
```php
// Get the correct base path from current request
$script = $_SERVER['SCRIPT_NAME'] ?? '';
$basePath = '';
if ($script) {
    $parts = explode('/', trim($script, '/'));
    if (!empty($parts)) {
        $basePath = '/' . $parts[0]; // This will give us /myfirstmovie3
    }
}
$correct_base_path = $basePath;
```

### Smart Image Path Logic
```php
// Check if it's an S3 URL or local path
$imagePath = $image['path'];
if (strpos($imagePath, 'http') === 0) {
    // S3 URL or full URL
    $imageUrl = $imagePath;
} else {
    // Local path - construct proper URL using correct base path
    if (strpos($imagePath, 'uploads/module/') === 0) {
        // Already includes the path, just prepend base path
        $imageUrl = $correct_base_path . "/" . $imagePath;
    } else {
        // Just the filename, prepend full path
        $imageUrl = $correct_base_path . "/uploads/module/" . $imagePath;
    }
}
```

## DevOps Notes

### Environment Configuration
- **Development**: Leave `S3_BASE_URL` empty to use local storage
- **Production**: Set `S3_BASE_URL` to your S3 bucket URL
- **Testing**: Can test both modes by toggling the environment variable

### File Permissions
- **Local Storage**: Ensure `uploads/` directory is writable (755)
- **Admin Uploads**: Ensure `admin/uploads/` directory is writable (755)
- **S3**: Ensure proper IAM permissions for bucket access

### Performance Considerations
- **Local Storage**: Faster for development, limited by disk space
- **S3 Storage**: Scalable, CDN-friendly, better for production
- **Backup Strategy**: Local files serve as backup until full S3 implementation

### Monitoring
- **Error Handling**: Failed image loads show placeholders
- **Logging**: Check PHP error logs for upload issues
- **Fallback**: System gracefully degrades to placeholder images

## Future Enhancement

Full S3 upload functionality can be added by installing AWS SDK and implementing the upload logic:

```bash
composer require aws/aws-sdk-php
```

### Required Changes for Full S3 Upload
1. Install AWS SDK
2. Implement S3 upload in file upload handlers
3. Remove local file backup (optional)
4. Add S3 error handling and retry logic
5. Implement S3 file deletion when records are removed

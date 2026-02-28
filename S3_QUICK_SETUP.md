# S3 Quick Setup for DevOps

## 🚀 What DevOps Needs to Do

### 1. Get AWS S3 Credentials
```bash
# Create IAM user with S3 access
aws iam create-user --user-name myfirstmovie-s3-user
aws iam attach-user-policy --user-name myfirstmovie-s3-user --policy-arn arn:aws:iam::aws:policy/AmazonS3FullAccess
aws iam create-access-key --user-name myfirstmovie-s3-user
```

### 2. Add to .env File
```env
# Add these to your .env file
S3_ACCESS_KEY_ID=AKIAIOSFODNN7EXAMPLE
S3_SECRET_ACCESS_KEY=wJalrXUtnFEMI/K7MDENG/bPxRfiCYEXAMPLEKEY
S3_REGION=us-east-1
S3_BUCKET=myfirstmovie-bucket
S3_BASE_URL=https://myfirstmovie-bucket.s3.amazonaws.com
S3_ENABLED=true
```

### 3. Done! 🎉

That's it! The application will automatically:
- ✅ Upload files to S3 when `S3_ENABLED=true`
- ✅ Use local storage when `S3_ENABLED=false`
- ✅ Handle all file types (avatars, BTS, enrollments, etc.)

## 🧪 Quick Test
```bash
# Test if S3 is working
php -r "require_once 'classes/S3Uploader.php'; $s3 = new S3Uploader(); echo $s3->isS3Enabled() ? 'S3 Enabled' : 'Local Mode';"
```

## 📁 S3 Structure (Auto-Created)
```
myfirstmovie-bucket/
├── bts/           # Behind the scenes
├── members/       # User avatars
├── enrollments/   # User files
├── news/          # News images
├── core-team/     # Team photos
├── panelists/     # Panelist photos
├── testimonials/  # Client logos
├── categories/    # Category images
├── winners/       # Winner badges
└── admin/         # Admin uploads
```

## 🌐 Frontend Pages That Use S3

### User-Facing Pages:
- `index.php` - BTS images, testimonials, news
- `behind-the-scenes.php` - BTS gallery images
- `call-for-entry.php` - Entry form uploads
- `the-core-team.php` - Team member photos
- `the-panelists.php` - Panelist photos
- `members/dashboard.php` - User profile display
- `members/account-details.php` - Avatar upload/update
- `members/current-enrollments.php` - File uploads

### Admin Pages:
- `admin/view-enrollment.php` - Enrollment file access
- `admin/testimonials.php` - Client logo uploads
- `admin/categories.php` - Category image uploads
- `admin/winners.php` - Winner badge uploads
- `admin/profile.php` - Admin avatar uploads
- `admin/behind-the-scenes.php` - BTS image uploads
- `admin/news.php` - News image uploads
- `admin/core-team.php` - Team member photos
- `admin/panelists.php` - Panelist photos
## 🔧 Toggle S3/Local
```env
# Production
S3_ENABLED=true

# Development
S3_ENABLED=false
```

**That's all DevOps needs to know!** 🚀

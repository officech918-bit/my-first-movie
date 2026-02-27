# Render Deployment Guide

## 🚀 Quick Deployment Steps

### 1. Push to GitHub
```bash
git add .
git commit -m "Fix session configuration for Docker deployment"
git push origin clean-deploy
```

### 2. Deploy on Render
1. Go to [Render Dashboard](https://dashboard.render.com/)
2. **New+ → Web Service**
3. Connect GitHub repository
4. Use branch: `clean-deploy`
5. Render will auto-detect the `render.yaml`

### 3. Set Environment Variables in Render Dashboard

Go to your service dashboard → **Environment** tab and add these variables:

#### 🔐 **Database Configuration**
```
DB_HOST = mysql-2e207f46-officech918-5233.i.aivencloud.com
DB_PORT = 11827
DB_DATABASE = defaultdb
DB_USERNAME = avnadmin
DB_PASSWORD = YOUR_AIVEN_PASSWORD_HERE
```

#### 🌐 **Application Configuration**
```
APP_ENV = production
APP_DEBUG = false
SITENAME = https://your-app-name.onrender.com
```

#### 📧 **Email Configuration (Gmail)**
```
MAIL_DRIVER = smtp
MAIL_HOST = smtp.gmail.com
MAIL_PORT = 587
MAIL_USERNAME = YOUR_GMAIL_ADDRESS_HERE
MAIL_PASSWORD = YOUR_GMAIL_PASSWORD_HERE
MAIL_ENCRYPTION = tls
MAIL_FROM_ADDRESS = no-reply@myfirstmovie.com
MAIL_FROM_NAME = MyFirstMovie Support
```

#### 💳 **CCAvenue Payment Gateway**
```
CCAV_ACCESS_CODE = YOUR_CCAV_ACCESS_CODE_HERE
CCAV_MERCHANT_ID = YOUR_CCAV_MERCHANT_ID_HERE
CCAV_WORKING_KEY = YOUR_CCAV_WORKING_KEY_HERE
```

#### 🔑 **API Configuration**
```
API_NEWS = YOUR_NEWS_API_KEY_HERE
```

#### ☁️ **AWS S3 Configuration (Optional)**
```
S3_BASE_URL = 
AWS_ACCESS_KEY_ID = 
AWS_SECRET_ACCESS_KEY = 
AWS_REGION = ap-south-1
AWS_BUCKET = 
```

### 4. Deploy and Test

#### 📋 **Health Check**
- **URL**: `https://your-app-name.onrender.com/health.php`
- **Expected**: "OK"

#### 🌐 **Main Application**
- **URL**: `https://your-app-name.onrender.com`
- **Expected**: Homepage loads with content

#### ⚙️ **Admin Panel**
- **URL**: `https://your-app-name.onrender.com/admin/`
- **Expected**: Admin login page

## 🔧 What's Fixed

### ✅ **Session Issues**
- Session path changed from cPanel to Docker-compatible `/tmp`
- Session cookies configured for security
- Session regeneration will work properly

### ✅ **Database Connection**
- All credentials available in environment
- Proper connection string configured
- Database will connect successfully

### ✅ **Docker Optimizations**
- PHP 8.1 with all required extensions
- OPcache for performance
- Security headers enabled
- Proper file permissions

## 📊 Deployment Timeline

1. **Build**: 5-7 minutes
2. **Deploy**: 2-3 minutes  
3. **Health Check**: 30 seconds
4. **Total**: ~10-12 minutes

## 🎯 Success Indicators

✅ **Render Dashboard Shows:**
- Status: Live
- Health: Passing
- URL: Available

✅ **Application Tests:**
- Health endpoint returns "OK"
- Homepage loads with database content
- Admin panel accessible
- File uploads work
- Email notifications send

## 🔒 Security Notes

- ✅ No secrets in Git repository
- ✅ Environment variables set in Render
- ✅ HTTPS automatically enabled
- ✅ Security headers configured
- ✅ Session cookies secure

## 📞 Support

If you encounter issues:

1. **Check Render Logs** in dashboard
2. **Verify Environment Variables** are set correctly
3. **Test Health Endpoint**: `/health.php`
4. **Check Database Connection** credentials

---

**🎉 Your MyFirstMovie application is ready for production deployment on Render!**

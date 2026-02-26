# Deploy MyFirstMovie on Render

This guide will help you deploy your MyFirstMovie application on Render using Docker.

## 🚀 Quick Deploy

### 1. Push to GitHub
```bash
git add .
git commit -m "Add Docker configuration for Render deployment"
git push origin main
```

### 2. Connect Render to GitHub
1. Go to [Render Dashboard](https://dashboard.render.com/)
2. Click "New +" → "Web Service"
3. Connect your GitHub repository
4. Select the `myfirstmovie1` repository
5. Configure deployment settings

### 3. Configure Web Service
```yaml
Name: myfirstmovie-web
Environment: Docker
Region: Choose nearest region
Branch: main
Root Directory: ./
Dockerfile Path: ./Dockerfile
Instance Type: Free
```

### 4. Environment Variables
Set these environment variables in Render:

#### Database Configuration:
```
DB_HOST=myfirstmovie-db
DB_DATABASE=myfirstmovie
DB_USERNAME=myfirstmovie_user
DB_PASSWORD=myfirstmovie_pass_2024
```

#### Application Configuration:
```
APP_ENV=production
APP_DEBUG=false
SITENAME=https://your-app-name.onrender.com
```

#### S3 Configuration (if using S3):
```
AWS_ACCESS_KEY_ID=your_access_key
AWS_SECRET_ACCESS_KEY=your_secret_key
AWS_REGION=your_region
AWS_BUCKET=your_bucket_name
```

### 5. Create Database Service
1. Click "New +" → "PostgreSQL" or "MySQL"
2. Name: `myfirstmovie-db`
3. Database: `myfirstmovie`
4. User: `myfirstmovie_user`
5. Plan: Free

### 6. Import Database
After deployment, import your database:

#### Option A: Using Render Shell
```bash
# Connect to your web service shell
# Run:
mysql -h your-db-host -u your-db-user -p myfirstmovie < myfirstm_live.sql
```

#### Option B: Using pgAdmin/MySQL Client
1. Download connection details from Render dashboard
2. Connect using your preferred database client
3. Import `myfirstm_live.sql`

## 🔧 Configuration Files Created

### Docker Configuration:
- `Dockerfile` - Main application container
- `docker-compose.yml` - Local development setup
- `docker/Dockerfile.mysql` - MySQL container
- `docker/apache.conf` - Apache configuration

### Render Configuration:
- `render.yaml` - Render service definitions
- `.dockerignore` - Files to exclude from Docker build

## 🏃‍♂️ Local Development

### Using Docker Compose:
```bash
# Start all services
docker-compose up -d

# View logs
docker-compose logs -f

# Stop services
docker-compose down
```

### Access Local Services:
- **Application**: http://localhost:8080
- **Database**: localhost:3306
- **Redis**: localhost:6379

## 📋 Post-Deployment Checklist

### 1. Update Site Configuration
Log into admin panel and update:
- Site Name: `https://your-app-name.onrender.com`
- Sub-location: (empty)
- Admin Location: `admin`

### 2. Test Functionality
- [ ] Homepage loads correctly
- [ ] Admin panel accessible
- [ ] User registration/login works
- [ ] File uploads work
- [ ] Email functionality works
- [ ] S3 integration (if configured)

### 3. Configure Custom Domain (Optional)
1. Add custom domain in Render dashboard
2. Update DNS records
3. Update site configuration in admin panel

### 4. SSL Certificate
Render automatically provides SSL certificates for all services.

## 🔍 Troubleshooting

### Common Issues:

#### 1. Database Connection Error
```bash
# Check database service is running
docker-compose ps db

# Check database logs
docker-compose logs db
```

#### 2. File Upload Issues
```bash
# Check permissions
ls -la uploads/
chmod -R 777 uploads/
```

#### 3. 500 Internal Server Error
```bash
# Check Apache logs
docker-compose logs app

# Check PHP errors
docker-compose exec app tail -f /var/log/apache2/error.log
```

#### 4. Memory Issues
Update `Dockerfile` PHP settings:
```dockerfile
RUN echo "memory_limit = 512M" >> /usr/local/etc/php/conf.d/custom.ini
```

## 📊 Monitoring

### Render Dashboard:
- Service health status
- Resource usage metrics
- Error logs
- Deployment history

### Application Logs:
```bash
# Real-time logs
docker-compose logs -f app

# Apache access logs
docker-compose exec app tail -f /var/log/apache2/access.log

# Apache error logs
docker-compose exec app tail -f /var/log/apache2/error.log
```

## 🔄 CI/CD Pipeline

Render automatically deploys when you push to the connected branch. To control deployments:

### Manual Deployments:
1. Go to Render dashboard
2. Select your service
3. Click "Manual Deploy"

### Branch-Specific Deployments:
- `main` → Production
- `develop` → Staging
- `feature/*` → Preview environments

## 💡 Optimization Tips

### 1. Performance:
- Enable Redis caching
- Use CDN for static assets
- Optimize images
- Enable Gzip compression

### 2. Security:
- Update environment variables
- Use strong database passwords
- Enable HTTPS (automatic on Render)
- Regular security updates

### 3. Scaling:
- Upgrade Render plan as needed
- Use load balancers for high traffic
- Implement database read replicas

## 🆘 Support

- [Render Documentation](https://render.com/docs)
- [Docker Documentation](https://docs.docker.com/)
- [Render Support](https://render.com/support)

---

**Your MyFirstMovie application is now ready for deployment on Render!** 🎉

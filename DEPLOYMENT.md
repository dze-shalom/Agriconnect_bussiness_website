# AgriConnect Website Deployment Guide

This guide provides step-by-step instructions for deploying the AgriConnect website to production.

## Pre-Deployment Checklist

### 1. Content Verification
- [ ] All placeholder text replaced with actual content
- [ ] Team photos uploaded and optimized
- [ ] Logo files in place (`logo.png`, `logo-white.png`, `favicon.ico`)
- [ ] Contact information updated throughout site
- [ ] Email addresses configured in contact forms
- [ ] Phone numbers and addresses verified

### 2. Technical Verification
- [ ] All pages load correctly locally
- [ ] Contact forms tested and working
- [ ] Mobile responsiveness verified
- [ ] Cross-browser compatibility checked
- [ ] Image optimization completed
- [ ] Analytics tracking code updated

### 3. SEO Preparation
- [ ] Meta descriptions reviewed and optimized
- [ ] Page titles finalized
- [ ] Keywords updated for local market
- [ ] Open Graph images optimized
- [ ] XML sitemap generated (if needed)

## Deployment Options

## Option 1: Netlify (Recommended - Free with Forms)

### Why Netlify?
- ✅ Free hosting with SSL
- ✅ Built-in form handling (replaces PHP backend)
- ✅ Automatic deployments from Git
- ✅ Custom domain support
- ✅ Global CDN for fast loading

### Step-by-Step Netlify Deployment

#### Step 1: Prepare Your Files
```bash
# Ensure all files are ready
# Remove any local development files
# Verify all paths are relative (no localhost references)
```

#### Step 2: Netlify Setup
1. **Sign up** at [netlify.com](https://netlify.com)
2. **Drag and drop** your project folder to Netlify dashboard
3. **Configure site name**: `agriconnect-cm` or similar
4. **Enable HTTPS**: Automatic with Netlify

#### Step 3: Configure Forms
Replace PHP forms with Netlify forms:

**In your HTML forms, add:**
```html
<!-- For contact form -->
<form name="contact" method="POST" data-netlify="true">
    <input type="hidden" name="form-name" value="contact" />
    <!-- your form fields -->
</form>

<!-- For pilot program form -->
<form name="pilot-program" method="POST" data-netlify="true">
    <input type="hidden" name="form-name" value="pilot-program" />
    <!-- your form fields -->
</form>
```

#### Step 4: Custom Domain
1. **Purchase domain**: `agriconnect.cm` or similar
2. **Configure DNS**: Point to Netlify servers
3. **Update Netlify**: Add custom domain in site settings
4. **SSL Certificate**: Automatically generated

#### Step 5: Environment Variables
Configure in Netlify dashboard:
- `CONTACT_EMAIL=agri.connek@gmail.com`
- `GA_TRACKING_ID=your-analytics-id`

---

## Option 2: Shared Hosting (Full PHP Support)

### Why Shared Hosting?
- ✅ Full PHP backend support
- ✅ Email functionality
- ✅ Database support (if needed later)
- ✅ More control over server configuration
- 💰 Usually ₦2,000-5,000/month

### Recommended Hosting Providers (Cameroon)
1. **Cameroonian Providers**:
   - Hosting Cameroun
   - CamerHost
   - Smart Systems

2. **International Options**:
   - Namecheap (affordable)
   - Hostinger (budget-friendly)
   - SiteGround (premium)

### Step-by-Step Shared Hosting Deployment

#### Step 1: Purchase Hosting
1. **Choose plan**: Basic plan sufficient to start
2. **Select domain**: `agriconnect.cm` or `.com`
3. **Configure email**: Set up `contact@agriconnect.cm`

#### Step 2: Upload Files
```bash
# Using FTP (FileZilla, etc.)
# Upload all files to public_html/ or www/ directory
# Maintain folder structure exactly
```

#### Step 3: Configure Backend
1. **Test PHP**: Ensure PHP 7.4+ is available
2. **Email setup**: Configure SMTP if needed
3. **File permissions**: Set correct permissions for contact.php
4. **Test forms**: Send test submissions

#### Step 4: SSL Certificate
1. **Free SSL**: Most hosts provide Let's Encrypt
2. **Enable HTTPS**: Force HTTPS in hosting panel
3. **Update URLs**: Ensure all links use HTTPS

---

## Option 3: GitHub Pages (Free Static Hosting)

### Why GitHub Pages?
- ✅ Completely free
- ✅ Integrated with Git workflow
- ✅ Custom domain support
- ❌ No server-side processing (forms need external service)

### Step-by-Step GitHub Pages Deployment

#### Step 1: Setup Repository
```bash
# Create repository
git init
git add .
git commit -m "Initial AgriConnect website"
git branch -M main
git remote add origin https://github.com/yourusername/agriconnect-website.git
git push -u origin main
```

#### Step 2: Enable Pages
1. **Repository settings** → Pages
2. **Source**: Deploy from main branch
3. **Custom domain**: Add your domain
4. **HTTPS**: Enable force HTTPS

#### Step 3: External Form Service
Use Formspree or similar:

```html
<!-- Replace form action -->
<form action="https://formspree.io/f/YOUR_FORM_ID" method="POST">
    <!-- your form fields -->
</form>
```

---

## Post-Deployment Tasks

### 1. DNS Configuration

#### For Custom Domain (agriconnect.cm)
```
Type    Name    Value
A       @       [hosting provider IP]
CNAME   www     yourdomain.com
MX      @       [mail server]
```

### 2. Email Setup
```
# Create email accounts
contact@agriconnect.cm
team@agriconnect.cm
info@agriconnect.cm
```

### 3. Analytics Setup
1. **Google Analytics**: Create account and get tracking ID
2. **Google Search Console**: Submit sitemap
3. **Social Media**: Create business profiles

### 4. Performance Optimization

#### Image Optimization
```bash
# Optimize images before upload
# Use tools like TinyPNG, ImageOptim
# Ensure images are properly sized
```

#### Caching (if using shared hosting)
```apache
# Add to .htaccess
<IfModule mod_expires.c>
    ExpiresActive on
    ExpiresByType text/css "access plus 1 year"
    ExpiresByType application/javascript "access plus 1 year"
    ExpiresByType image/png "access plus 1 year"
    ExpiresByType image/jpg "access plus 1 year"
</IfModule>
```

### 5. Security Measures

#### Basic Security Headers
```apache
# Add to .htaccess
Header always set X-Content-Type-Options nosniff
Header always set X-Frame-Options DENY
Header always set X-XSS-Protection "1; mode=block"
```

#### Backup Strategy
1. **Automated backups**: Configure with hosting provider
2. **Git backup**: Keep repository updated
3. **Database backup**: If using database later

---

## Testing Checklist

### Pre-Launch Testing
- [ ] All pages load correctly
- [ ] Forms submit successfully
- [ ] Email notifications received
- [ ] Mobile responsiveness works
- [ ] Page loading speed < 3 seconds
- [ ] SSL certificate valid
- [ ] Analytics tracking functional

### Post-Launch Monitoring
- [ ] Set up uptime monitoring
- [ ] Configure Google Search Console
- [ ] Monitor form submissions
- [ ] Check analytics data
- [ ] Test from different devices/browsers

---

## Quick Deployment Summary

### For Immediate Launch (Recommended):

1. **Choose Netlify** for easiest deployment
2. **Upload project** via drag-and-drop
3. **Configure forms** with Netlify Forms
4. **Add custom domain** if purchased
5. **Test everything** thoroughly

### Estimated Timeline:
- **Preparation**: 2-4 hours
- **Deployment**: 30 minutes - 2 hours
- **Testing**: 1-2 hours
- **DNS propagation**: 24-48 hours

---

## Troubleshooting

### Common Issues

#### Forms Not Working
- Check form action URLs
- Verify server PHP support
- Test email server configuration
- Check spam folders

#### Images Not Loading
- Verify file paths are correct
- Check image file permissions
- Ensure proper file extensions
- Optimize large images

#### Site Not Accessible
- Check DNS propagation
- Verify hosting configuration
- Ensure SSL certificate is valid
- Check domain name servers

### Getting Help
- **Hosting Support**: Contact your hosting provider
- **Technical Issues**: Refer to platform documentation
- **Emergency Contact**: Development team

---

## Maintenance Schedule

### Weekly
- [ ] Check form submissions
- [ ] Monitor site uptime
- [ ] Review analytics data

### Monthly
- [ ] Update content as needed
- [ ] Check for broken links
- [ ] Review performance metrics
- [ ] Backup website files

### Quarterly
- [ ] Update contact information
- [ ] Review and update SEO
- [ ] Check security updates
- [ ] Evaluate hosting performance

---

**Ready to Launch!** 🚀

After following this guide, your AgriConnect website will be live and ready to connect with farmers, partners, and investors across Africa.

For any deployment questions, contact the development team at agri.connek@gmail.com
# AgriConnect Website

Building Africa's first satellite-powered smart farming platform with AIMS Cameroon research support and NMD satellite technology.

## Project Overview

AgriConnect is a comprehensive website showcasing our innovative agricultural technology that combines ground-based IoT sensors with satellite connectivity to revolutionize farming across Africa. Currently in development with a planned pilot program launching Q2 2025.

## Features

### Pages
- **Homepage** (`index.html`) - Main landing page with project overview
- **Technology** (`technology.html`) - Detailed technical specifications and system architecture
- **Pilot Program** (`pilot-program.html`) - Comprehensive pilot program information and application
- **Beyond Agriculture** (`beyond-agriculture.html`) - IoT solutions for other industries
- **Our Team** (`team.html`) - Team member profiles and backgrounds
- **Progress** (`progress.html`) - Development timeline and current status
- **Contact** (`contact.html`) - Contact forms and information

### Technical Features
- **Responsive Design** - Mobile-first approach with full tablet and desktop support
- **Professional UI** - Modern design with consistent branding and user experience
- **Form Handling** - Contact forms with PHP backend processing
- **SEO Optimized** - Comprehensive meta tags and structured content
- **Analytics Ready** - Google Analytics integration
- **Performance Optimized** - Efficient loading and smooth animations

## Technology Stack

### Frontend
- **HTML5** - Semantic markup with accessibility features
- **CSS3** - Custom CSS with CSS variables and modern features
- **JavaScript (ES6+)** - Modern JavaScript with modular architecture
- **Font Awesome** - Professional icon library
- **Google Fonts** - Inter font family for professional typography

### Backend
- **PHP** - Contact form processing and email handling
- **Rate Limiting** - Built-in protection against spam and abuse
- **Input Validation** - Comprehensive form validation and sanitization

### External Integrations
- **Google Analytics** - Traffic and user behavior tracking
- **Email Integration** - Automated email responses and notifications
- **WhatsApp Integration** - Direct communication channel

## Project Structure

```
agriconnect-website/
├── index.html              # Homepage
├── technology.html         # Technology details
├── pilot-program.html      # Pilot program information
├── beyond-agriculture.html # IoT services
├── team.html              # Team profiles
├── progress.html          # Development progress
├── contact.html           # Contact information
├── css/
│   └── styles.css         # Main stylesheet
├── js/
│   └── main.js           # Core JavaScript functionality
├── images/               # Image assets (organized by category)
│   ├── logo/
│   ├── team/
│   ├── technology/
│   ├── partners/
│   └── backgrounds/
├── backend/
│   └── contact.php       # Form processing
├── README.md             # This file
└── .gitignore           # Git ignore rules
```

## Setup Instructions

### 1. Local Development

**Basic Setup (Static Files)**
```bash
# Clone or download the project
git clone <repository-url>
cd agriconnect-website

# Open index.html in your browser
# For basic viewing, you can directly open the HTML files
```

**Local Server (Recommended)**
```bash
# Using Python (if installed)
python -m http.server 8000

# Using Node.js (if installed)
npx http-server

# Using PHP (if installed)
php -S localhost:8000

# Then open: http://localhost:8000
```

### 2. Backend Setup (Forms)

**For Contact Forms to Work:**

1. **Upload to PHP-enabled server** (shared hosting, VPS, etc.)
2. **Configure email settings** in `backend/contact.php`:
   ```php
   $config = [
       'admin_email' => 'your-email@example.com',
       'from_email' => 'noreply@yourdomain.com',
       // ... other settings
   ];
   ```
3. **Test form submissions** to ensure emails are working

### 3. Deployment Options

#### Option A: Netlify (Recommended for Static + Forms)
1. **Drag & Drop**: Upload project folder to Netlify
2. **Forms**: Use Netlify Forms instead of PHP backend
3. **Custom Domain**: Configure your domain in Netlify settings
4. **SSL**: Automatic HTTPS

#### Option B: Shared Hosting (Full PHP Support)
1. **Upload via FTP**: Transfer all files to your hosting account
2. **Database**: Not required (forms use email)
3. **Email**: Configure SMTP if needed
4. **SSL**: Enable HTTPS in hosting panel

#### Option C: GitHub Pages (Static Only)
1. **Push to GitHub**: Upload to GitHub repository
2. **Enable Pages**: In repository settings
3. **Forms**: Use external service (Formspree, etc.)
4. **Custom Domain**: Configure in repository settings

## Customization

### 1. Branding & Content
- **Logo**: Replace files in `images/logo/`
- **Colors**: Update CSS variables in `styles.css`
- **Content**: Edit HTML files with your specific information
- **Images**: Replace placeholder images with actual photos

### 2. Contact Information
Update these locations with your details:
- Contact information in all HTML files
- Email addresses in `backend/contact.php`
- Phone numbers and addresses throughout site
- Social media links (if applicable)

### 3. Analytics
```javascript
// Replace in all HTML files
gtag('config', 'YOUR_GA_MEASUREMENT_ID');
```

### 4. Email Configuration
```php
// In backend/contact.php
$config = [
    'admin_email' => 'your-email@domain.com',
    'from_email' => 'noreply@yourdomain.com',
    // ... update other settings
];
```

## Required Assets

### Images Needed
- **Logo files**: `logo.png`, `logo-white.png`, `favicon.ico`
- **Team photos**: Individual headshots for team members
- **Technology images**: Prototype photos, system diagrams
- **Partner logos**: AIMS Cameroon, NMD logos
- **Hero backgrounds**: Professional farming/technology images

### Content to Update
- Specific team member information and photos
- Actual technology specifications and metrics
- Real progress updates and milestones
- Accurate contact information and location
- Authentic partnership details

## Browser Support

- **Chrome/Edge**: Full support (latest versions)
- **Firefox**: Full support (latest versions)
- **Safari**: Full support (latest versions)
- **Mobile browsers**: Optimized for mobile viewing
- **Internet Explorer**: Not supported (uses modern CSS features)

## Performance

### Optimization Features
- **CSS Variables**: Efficient styling with consistent design tokens
- **Image Optimization**: Responsive images with proper sizing
- **Minimal Dependencies**: Only essential external resources
- **Lazy Loading**: Images load as needed
- **Efficient JavaScript**: Modern, performance-focused code

### Loading Speed
- **First Contentful Paint**: < 2 seconds
- **Time to Interactive**: < 3 seconds
- **Total Bundle Size**: < 500KB (excluding images)

## Security

### Built-in Protection
- **Input Validation**: All form inputs validated and sanitized
- **Rate Limiting**: Prevents spam and abuse
- **XSS Protection**: Output encoding and input filtering
- **CSRF Protection**: Form tokens and validation

### Recommendations
- **HTTPS**: Always use SSL certificates in production
- **Regular Updates**: Keep server software updated
- **Backup**: Regular backups of website and data
- **Monitoring**: Monitor for security issues and uptime

## SEO Features

### Technical SEO
- **Semantic HTML**: Proper heading structure and markup
- **Meta Tags**: Comprehensive meta descriptions and keywords
- **Open Graph**: Social media sharing optimization
- **Structured Data**: Schema markup for search engines
- **XML Sitemap**: Search engine indexing support

### Content Optimization
- **Keyword Strategy**: Targeted agricultural technology keywords
- **Content Quality**: Informative, valuable content
- **Internal Linking**: Proper page interconnections
- **Mobile Optimization**: Mobile-first responsive design

## Maintenance

### Regular Tasks
- **Content Updates**: Keep progress and news current
- **Image Optimization**: Compress and optimize new images
- **Security Updates**: Update server software and dependencies
- **Performance Monitoring**: Check loading speeds and user experience
- **Analytics Review**: Monitor traffic and user behavior

### Monitoring
- **Uptime Monitoring**: Ensure website availability
- **Form Testing**: Regularly test contact forms
- **Mobile Testing**: Check mobile experience
- **Cross-browser Testing**: Verify compatibility

## Support

### Getting Help
- **Documentation**: This README and inline code comments
- **Contact**: For technical issues, contact the development team
- **Updates**: Check for updates and improvements

### Contributing
1. **Fork the repository**
2. **Create feature branch**
3. **Make changes**
4. **Test thoroughly**
5. **Submit pull request**

## License

This project is proprietary to AgriConnect and AIMS Cameroon. All rights reserved.

## Contact

- **Email**: agri.connek@gmail.com
- **Phone**: +237 695 465 755
- **Location**: Yaoundé, Cameroon
- **Institution**: AIMS Cameroon

---

**AgriConnect** - Building the future of African agriculture through innovative satellite-IoT technology.

*AIMS Cameroon × NMD Satellite Technology*
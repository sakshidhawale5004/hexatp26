# HexaTP - Global Consultation Services

Professional consultation services for immigration, business setup, and international expansion.

## 🌐 Website Features

- **Homepage**: Professional landing page
- **Contact Form**: Consultation booking system
- **Country Pages**: 20+ country-specific information pages
- **Admin Panel**: Manage consultation requests
- **Responsive Design**: Mobile-friendly interface

## 🗄️ Database

**MySQL Database**: hexatp_db

**Tables**:
- `consultations` - Consultation requests
- `inquiries` - General inquiries

## 📁 Files

- **HTML**: 24 pages
- **PHP**: 3 backend files
- **Images**: 8 files

## 🚀 Deployment

See `HOSTINGER_DEPLOYMENT_READY.md` for deployment instructions.

## 🔧 Configuration

Update `db_config.php` with your database credentials:

```php
define('DB_HOST', 'localhost');
define('DB_USER', 'your_db_user');
define('DB_PASS', 'your_password');
define('DB_NAME', 'hexatp_db');
```

## 📞 Admin Panel

Access: `/admin_consultations.php`

## 🔐 Security

- Use strong database passwords
- Enable HTTPS/SSL
- Add admin authentication
- Regular backups

---

**Status**: Production Ready  
**Version**: 1.0  
**Database**: MySQL

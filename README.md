# Vehicle Parking Management System (VPMS)

A comprehensive web-based parking management system built with PHP and MySQL that allows efficient management of vehicle parking spaces, bookings, and payments.

## Features

### Admin Panel
- **Dashboard**: Overview of parking statistics and system metrics
- **Vehicle Management**: Add, edit, and manage vehicle categories
- **Parking Space Management**: Configure and manage available parking spaces
- **Booking Management**: View and manage all parking bookings
- **User Management**: Manage registered users and their profiles
- **Reports & Analytics**: Generate detailed reports with date range filtering
- **Payment Management**: Track and manage payment transactions
- **Export Functionality**: Export reports to Excel and PDF formats

### User Features
- **User Registration & Login**: Secure user authentication system
- **Parking Space Booking**: Book available parking spaces
- **Payment Integration**: Integrated with Paystack for secure payments
- **Booking History**: View past and current bookings
- **Receipt Generation**: Download parking receipts
- **Profile Management**: Update user profile information
- **Password Management**: Change password and forgot password functionality

## Technology Stack

- **Backend**: PHP 7.4+
- **Database**: MySQL
- **Frontend**: HTML5, CSS3, JavaScript, Bootstrap
- **Payment Gateway**: Paystack
- **PDF Generation**: DomPDF
- **Dependencies**: Composer for package management

## Installation

### Prerequisites
- XAMPP/WAMP/LAMP server
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Composer

### Setup Instructions

1. **Clone the repository**
   ```bash
   git clone https://github.com/Hassan1910/vpms.git
   cd vpms
   ```

2. **Install dependencies**
   ```bash
   composer install
   ```

3. **Database Setup**
   - Create a new MySQL database named `vpmsdb`
   - Import the database schema:
     ```bash
     mysql -u your_username -p vpmsdb < vpmsdb.sql
     ```

4. **Configure Database Connection**
   - Update database credentials in:
     - `admin/includes/dbconnection.php`
     - `users/includes/dbconnection.php`
   
   ```php
   $host = 'localhost';
   $dbname = 'vpmsdb';
   $username = 'your_username';
   $password = 'your_password';
   ```

5. **Configure Paystack (Optional)**
   - Get your Paystack API keys from [Paystack Dashboard](https://dashboard.paystack.com/)
   - Update the payment configuration files with your API keys

6. **Set Permissions**
   - Ensure proper file permissions for upload directories
   - Make sure the web server has read/write access to necessary folders

## Usage

### Admin Access
- Navigate to `http://localhost/vpms/admin/`
- Use admin credentials to login
- Access the dashboard to manage the system

### User Access
- Navigate to `http://localhost/vpms/users/`
- Register a new account or login with existing credentials
- Book parking spaces and make payments

## Project Structure

```
vpms/
├── admin/                 # Admin panel files
│   ├── assets/           # Admin CSS, JS, and other assets
│   ├── images/           # Admin images and uploads
│   ├── includes/         # Admin includes (header, footer, etc.)
│   └── *.php            # Admin functionality files
├── users/                # User panel files
│   ├── assets/          # User CSS and assets
│   ├── includes/        # User includes
│   └── *.php           # User functionality files
├── assets/              # Shared assets
├── vendor/              # Composer dependencies
├── css/                 # Global stylesheets
├── js/                  # Global JavaScript files
├── includes/            # Global includes
├── vpmsdb.sql          # Database schema
└── composer.json       # Composer configuration
```

## Key Features Explained

### Payment Integration
The system integrates with Paystack for secure payment processing. Users can pay for parking bookings using various payment methods supported by Paystack.

### Report Generation
Admins can generate comprehensive reports with:
- Date range filtering
- Export to Excel and PDF formats
- Analytics and insights

### Responsive Design
The system is built with a responsive design that works on desktop, tablet, and mobile devices.

## Contributing

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/new-feature`)
3. Commit your changes (`git commit -am 'Add new feature'`)
4. Push to the branch (`git push origin feature/new-feature`)
5. Create a Pull Request

## License

This project is open source and available under the [MIT License](LICENSE).

## Support

For support and questions, please open an issue on GitHub or contact the development team.

## Screenshots

*Add screenshots of your application here to showcase the UI*

## Changelog

### Version 1.0.0
- Initial release
- Basic parking management functionality
- User registration and booking system
- Admin panel with reporting
- Paystack payment integration

---

**Note**: Make sure to update the database credentials and API keys before deploying to production. Always use environment variables for sensitive configuration in production environments.
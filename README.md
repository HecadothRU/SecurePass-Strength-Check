# SecurePass-Strength-Check

This PHP-based API evaluates the strength of passwords based on various criteria and suggests improvements.

## Prerequisites

Before setting up the project, ensure you have the following:
- PHP 8.0 or higher
- Access to a MySQL database
- A web server like Apache or Nginx
- SSL/TLS certificate for HTTPS (recommended for production)

## Setup

### Clone the Repository

First, clone the repository to your local machine or server:

```bash
git clone https://github.com/HecadothRU/SecurePass-Strength-Check
cd SecurePass-Strength-Check
```

### Database Setup

1. Create a MySQL database and user with the necessary permissions.
2. Create a table for rate limiting with the following SQL command:

```sql
CREATE TABLE rate_limit (
    ip_address VARCHAR(45),
    timestamp DATETIME,
    PRIMARY KEY (ip_address, timestamp)
);
```

### Configuration

1. Open `password_strength_api.php`.
2. Update the database connection details:

```php
$db = new PDO('mysql:host=[your_host];dbname=[your_db]', '[username]', '[password]');
```

### Running the API

Place the `password_strength_api.php` file in your web server's document root or a specific directory accessible via the web server.

## Usage

To use the API, send a POST request with a JSON payload containing the password you want to evaluate. 

Example using cURL:

```bash
curl -X POST -d '{"password":"YourTestPassword"}' -H "Content-Type: application/json" https://your-server.com/password_strength_api.php
```

The API will return a JSON response with the password strength and suggestions.

## Security

For production environments:
- Ensure HTTPS is enabled.
- Implement additional security measures like firewalls and secure coding practices.
- Regularly update and audit the code.

# Nergiz Architecture API

A complete RESTful API backend for Nergiz Architecture website built with PHP and MySQL.

## 📁 Project Structure

```
backend/
├── .env                    # Environment variables
├── config/
│   ├── db.php              # Database connection
│   └── env.php             # Environment loader
├── controllers/
│   ├── CategoryController.php
│   ├── ServiceController.php
│   ├── PortfolioController.php
│   ├── ContactController.php
│   └── UserController.php
├── models/
│   ├── Category.php
│   ├── Service.php
│   ├── Portfolio.php
│   ├── Contact.php
│   └── User.php
├── routes/
│   ├── categories.php
│   ├── services.php
│   ├── portfolio.php
│   ├── contacts.php
│   └── users.php
├── auth/
│   ├── login.php           # User authentication
│   ├── logout.php          # User logout
│   ├── me.php              # Current user info
│   └── refresh.php         # Token refresh
├── middleware/
│   ├── require_auth.php    # Authentication middleware
│   ├── require_role.php    # Role-based access control
│   └── protect_admin.php   # Admin protection
├── utils/
│   └── jwt.php             # JWT token management
├── helpers/
│   └── response.php        # JSON response formatter
├── index.php               # Main entry point
├── database.sql            # Database setup script
├── postman-collection.json # Postman API collection
└── README.md
```

## 🚀 Setup Instructions

### 1. Database Setup
1. Start your XAMPP/WAMP server
2. Open phpMyAdmin or MySQL command line
3. Import the `database.sql` file to create the database and tables:
   ```sql
   mysql -u root -p < database.sql
   ```
   Or copy and paste the SQL content into phpMyAdmin

### 2. Environment Configuration
1. Copy the `.env` file and configure your environment variables:
   ```bash
   # Database Configuration
   DB_HOST=localhost
   DB_USERNAME=root
   DB_PASSWORD=
   DB_NAME=arch-nergiz
   
   # JWT Configuration
   JWT_SECRET=your-secret-key-here
   JWT_REFRESH_SECRET=your-refresh-secret-key-here
   JWT_EXPIRY=3600
   JWT_REFRESH_EXPIRY=604800
   ```

2. Database configuration is in `config/db.php`
3. Environment variables are loaded via `config/env.php`

### 3. Default Admin User
- Username: `admin`
- Password: `admin123`
- Role: `admin`

### 4. Testing the API
- Base URL: `http://localhost/api-nergiz/`
- Import `postman-collection.json` into Postman for easy testing
- Or use curl/browser to test endpoints

## 📚 API Endpoints

### Categories
- `GET /categories` - Get all categories
- `POST /categories` - Create new category
- `PUT /categories` - Update category
- `DELETE /categories` - Delete category

### Services
- `GET /services` - Get all services
- `GET /services?category_id=X` - Get services by category
- `POST /services` - Create new service
- `PUT /services` - Update service
- `DELETE /services` - Delete service

### Portfolio
- `GET /portfolio` - Get all portfolio items
- `GET /portfolio?category_id=X` - Get portfolio by category
- `POST /portfolio` - Create new portfolio item
- `PUT /portfolio` - Update portfolio item
- `DELETE /portfolio` - Delete portfolio item

### Contacts
- `GET /contacts` - Get all contacts
- `GET /contacts?unread=true` - Get unread contacts
- `POST /contacts` - Create new contact
- `PUT /contacts` - Update contact or mark as read
- `DELETE /contacts` - Delete contact

### Authentication
- `POST /auth/login` - User login (returns JWT tokens)
- `POST /auth/logout` - User logout
- `GET /auth/me` - Get current user info (requires auth)
- `POST /auth/refresh` - Refresh JWT tokens

### User Management (Admin Only)
- `GET /users` - Get all users
- `GET /users/{id}` - Get specific user
- `POST /users` - Create new user
- `PUT /users/{id}` - Update user
- `DELETE /users/{id}` - Delete user
- `POST /users/{id}/change-password` - Change user password

## 📝 Request/Response Examples

### Create Category
```bash
curl -X POST http://localhost/api-nergiz/categories \
  -H "Content-Type: application/json" \
  -d '{"name": "Architecture"}'
```

Response:
```json
{"message": "Category created"}
```

### Get All Categories
```bash
curl http://localhost/api-nergiz/categories
```

Response:
```json
[
  {
    "id": "1",
    "name": "Architecture"
  }
]
```

### Update Category
```bash
curl -X PUT http://localhost/api-nergiz/categories \
  -H "Content-Type: application/json" \
  -d '{
    "id": 1,
    "name": "Urban Design"
  }'
```

Response:
```json
{"message": "Category updated"}
```

### Delete Category
```bash
curl -X DELETE http://localhost/api-nergiz/categories \
  -H "Content-Type: application/json" \
  -d '{"id": 1}'
```

Response:
```json
{"message": "Category deleted"}
```

### Create Service (JSON, Admin Only)
```bash
curl -X POST http://localhost/api-nergiz/services \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_ACCESS_TOKEN" \
  -d '{
    "name": "Residential Design",
    "image": "uploads/services/example.jpg",  
    "description": "Complete residential architecture design services"
  }'
```

Optional (upload image file via multipart/form-data):
```bash
curl -X POST http://localhost/api-nergiz/services \
  -H "Authorization: Bearer YOUR_ACCESS_TOKEN" \
  -F "name=Residential Design" \
  -F "description=Complete residential architecture design services" \
  -F "image=@/absolute/path/to/file.jpg"
```
- On success: `{ "message": "Service created successfully", "service_id": <ID> }`

### Update Service (JSON, Admin Only)
```bash
curl -X PUT http://localhost/api-nergiz/services/1 \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_ACCESS_TOKEN" \
  -d '{
    "name": "Residential Design (Updated)",
    "image": "uploads/services/new-image.webp",
    "description": "Updated description"
  }'
```
Alternative (provide ID in JSON body if not in path):
```bash
curl -X PUT http://localhost/api-nergiz/services \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_ACCESS_TOKEN" \
  -d '{
    "id": 1,
    "name": "Residential Design (Updated)",
    "image": "uploads/services/new-image.webp",
    "description": "Updated description"
  }'
```
- On success: `{ "message": "Service updated successfully" }`

### Delete Service (Admin Only)
```bash
curl -X DELETE http://localhost/api-nergiz/services/1 \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_ACCESS_TOKEN"
```
Alternative (provide ID in JSON body):
```bash
curl -X DELETE http://localhost/api-nergiz/services \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_ACCESS_TOKEN" \
  -d '{"id": 1}'
```
- On success: `{ "message": "Service deleted successfully" }`

### Create Portfolio Item
```bash
curl -X POST http://localhost/api-nergiz/portfolio \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Modern Villa",
    "work": "Villa Design Project",
    "type": "exterior",
    "description": "A stunning modern villa with contemporary design",
    "date": "2024-01-15",
    "category_id": 1
  }'
```

### Add Portfolio Image
```bash
curl -X POST http://localhost/api-nergiz/portfolio-images \
  -H "Content-Type: application/json" \
  -d '{
    "portfolio_id": 1,
    "image": "https://example.com/villa-main.jpg",
    "is_primary": true
  }'
```

### Submit Contact Form
```bash
curl -X POST http://localhost/api-nergiz/contacts \
  -H "Content-Type: application/json" \
  -d '{
    "address": "123 Main Street, City",
    "email": "john@example.com",
    "phone": "+1234567890"
  }'
```

### User Login
```bash
curl -X POST http://localhost/api-nergiz/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "username": "admin",
    "password": "admin123"
  }'
```

Response:
```json
{
  "message": "Login successful",
  "user": {
    "id": "1",
    "username": "admin",
    "role": "admin"
  },
  "access_token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
  "refresh_token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9..."
}
```

### Get Current User (Protected Route)
```bash
curl -X GET http://localhost/api-nergiz/auth/me \
  -H "Authorization: Bearer YOUR_ACCESS_TOKEN"
```

### Create User (Admin Only)
```bash
curl -X POST http://localhost/api-nergiz/users \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_ACCESS_TOKEN" \
  -d '{
    "username": "newuser",
    "password": "password123",
    "role": "user"
  }'
```

### Update User (Admin Only)
```bash
curl -X PUT http://localhost/api-nergiz/users/2 \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_ACCESS_TOKEN" \
  -d '{
    "username": "updateduser",
    "password": "newPass123",  
    "role": "user"
  }'
```
- Notes:
  - You can update any combination of fields: `username`, `password`, `role`.
  - Minimum password length is 6 characters.
  - Username must be unique and at least 3 characters.

Response (example):
```json
{
  "message": "User updated successfully",
  "user": {
    "id": "2",
    "username": "updateduser",
    "role": "user",
    "created_at": "2025-01-01 12:00:00"
  }
}
```

### Delete User (Admin Only)
```bash
curl -X DELETE http://localhost/api-nergiz/users/2 \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_ACCESS_TOKEN" \
  -d '{"id": 1}'
```
- Notes:
  - Admin cannot delete their own account.
  - Returns `{"message": "User deleted successfully"}` on success.

## 🔧 Features

- **CORS Support** - Cross-origin requests enabled
- **JSON Responses** - Consistent JSON format with proper HTTP status codes
- **Error Handling** - Comprehensive error messages and validation
- **Category Filtering** - Filter services and portfolio by category
- **Contact Management** - Track read/unread status for contact messages
- **RESTful Design** - Following REST API best practices
- **Sample Data** - Pre-populated with sample data for testing
- **JWT Authentication** - Secure token-based authentication system
- **Role-Based Access Control** - Admin and user role management
- **Password Hashing** - Secure bcrypt password encryption
- **Environment Variables** - Configurable settings via .env file
- **Middleware Protection** - Route protection and authentication middleware

## 🗄️ Database Schema

### Categories Table
```sql
CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL
);
```

### Services Table
```sql
CREATE TABLE services (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    image VARCHAR(255),
    description TEXT
);
```

### Portfolio Table
```sql
CREATE TABLE portfolio (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    work VARCHAR(255),
    type ENUM('interior', 'exterior') NOT NULL,
    description TEXT,
    date DATE,
    category_id INT,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
);
```

### Portfolio Images Table
```sql
CREATE TABLE portfolio_images (
    id INT AUTO_INCREMENT PRIMARY KEY,
    portfolio_id INT,
    image VARCHAR(255) NOT NULL,
    is_primary BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (portfolio_id) REFERENCES portfolio(id) ON DELETE CASCADE
);
```

### Contacts Table
```sql
CREATE TABLE contacts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    address VARCHAR(255),
    email VARCHAR(255),
    phone VARCHAR(50)
);
```

### Users Table
```sql
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'user') NOT NULL DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

## 🛠️ Development

### Adding New Endpoints
1. Create model in `models/` directory
2. Create controller in `controllers/` directory
3. Create route file in `routes/` directory
4. Add route to `index.php` dispatcher

### Error Handling
All endpoints return consistent JSON responses:
- Success: `{"message": "Success message"}` or data array
- Error: `{"error": "Error message"}` with appropriate HTTP status code

## 📱 Frontend Integration

This API is designed to work with any frontend framework. Key points:
- CORS is enabled for cross-origin requests
- All responses are in JSON format
- RESTful endpoints follow standard conventions
- Comprehensive error handling with proper HTTP status codes

## 🔒 Security Notes

- Input validation is implemented in controllers
- SQL injection protection via PDO prepared statements
- CORS headers configured for cross-origin requests
- Error messages don't expose sensitive information
- **JWT Token Security** - Secure token-based authentication with configurable expiry
- **Password Encryption** - Bcrypt hashing with configurable rounds
- **Role-Based Access** - Admin and user roles with middleware protection
- **Environment Variables** - Sensitive configuration stored in .env file
- **Authentication Middleware** - Route protection for sensitive endpoints

## 📞 Support

For questions or issues, please contact the development team or create an issue in the project repository.

### Update Category
```bash
curl -X PUT http://localhost/api-nergiz/categories \
  -H "Content-Type: application/json" \
  -d '{
    "id": 1,
    "name": "Urban Design"
  }'
```

Response:
```json
{"message": "Category updated"}
```

### Delete Category
```bash
curl -X DELETE http://localhost/api-nergiz/categories \
  -H "Content-Type: application/json" \
  -d '{"id": 1}'
```

Response:
```json
{"message": "Category deleted"}
```
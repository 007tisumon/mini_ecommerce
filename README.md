# Bloom Basket

A small flower-shop e-commerce application built with Core PHP, MySQL, and Bootstrap 5. This learning project practises object-oriented PHP, namespaces, Composer autoloading, custom routing, MVC architecture, PDO, and session-based shopping carts.

> **Project status:** This README describes the planned architecture and features. Update it as implementation progresses.

## Project Information

| Field | Details |
|---|---|
| Project Name | Bloom Basket |
| Student Name | Md Sumon |
| Trainee ID | 1297460 |
| Batch | PWAD/USSL-M/73/01 |

## Technology Stack

- PHP 8.3+ (Core PHP, OOP)
- MySQL 8.4 and PDO
- Composer with PSR-4 autoloading
- MVC architecture and a custom router
- HTML5, CSS3, Bootstrap 5, and JavaScript
- Lerd or Apache for local development

## Features

### Customer
- Register, log in, and log out
- Browse products, view details, search by name, and filter by category
- Add, update, and remove items from a session-based cart
- Checkout with recipient name, phone number, and delivery address
- Choose a payment method
- View order history, order details, and order status

### Administrator
- Admin authentication and dashboard
- Category CRUD
- Product CRUD, image uploads, and stock management
- View orders and order details
- Update order status
- View payment records and payment statuses

## Payment Methods

- Cash on Delivery (COD)
- bKash
- Nagad
- Rocket

**Important:** bKash, Nagad, and Rocket are simulated payment options for this educational project. No real payment gateway is integrated. Do not collect real payment PINs or OTPs. A test payment can record a simulated success or failure.

Payment status is separate from order status. One order may have multiple payment attempts.

## Database Design

The planned database has six main tables:

| Table | Purpose |
|---|---|
| `users` | Administrator and customer accounts |
| `categories` | Product categories |
| `products` | Flower product details, prices, images, and stock |
| `orders` | Customer and delivery information for orders |
| `order_items` | Items and price/name snapshots for each order |
| `payments` | Payment method, transaction reference, amount, and status |

### Relationships
- One user can place many orders.
- One category can contain many products.
- One order can contain many order items.
- A product can appear in many order items.
- One order can have multiple payment attempts.

Store product name and price snapshots in `order_items` so historical orders remain accurate if a product changes. Calculate totals on the server, and use database transactions when creating orders and updating stock.

## Project Structure

```text
bloom-basket/
├── app/
│   ├── Controllers/
│   ├── Models/
│   ├── Core/
│   │   ├── Router.php
│   │   ├── Database.php
│   │   ├── Controller.php
│   │   ├── Request.php
│   │   ├── Response.php
│   │   └── Session.php
│   ├── Middleware/
│   └── Services/
├── resources/
│   └── views/
├── routes/
│   ├── web.php
│   └── admin.php
├── config/
├── database/
│   └── migrations/
├── public/
│   ├── index.php
│   ├── .htaccess
│   └── assets/
├── storage/
│   └── uploads/
├── vendor/
├── .env
├── .gitignore
└── composer.json
```

## Installation and Setup

### 1. Clone the repository

```bash
git clone <repository-url>
cd bloom-basket
```

Replace `<repository-url>` with your actual repository URL.

### 2. Install dependencies

```bash
composer install
```

### 3. Configure Composer autoloading

Add this mapping to `composer.json`:

```json
{
  "autoload": {
    "psr-4": {
      "App\\": "app/"
    }
  }
}
```

Then run:

```bash
composer dump-autoload
```

### 4. Create the database

```sql
CREATE DATABASE bloom_basket
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;
```

Import the project's SQL schema or run its migrations when available.

### 5. Configure environment variables

Create a `.env` file in the project root:

```dotenv
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=bloom_basket
DB_USERNAME=your_database_username
DB_PASSWORD=your_database_password
```

Use your own local credentials. Do not commit real credentials to Git.

### 6. Configure the web server

Configure Lerd or Apache to use the project's `public/` directory as its document root. Enable URL rewriting so requests are handled by `public/index.php`.

### 7. Run the project

Open the local domain configured in your development environment.

## Security Considerations

- Use PDO prepared statements.
- Hash passwords with `password_hash()` and verify them with `password_verify()`.
- Validate user input on the server.
- Check authentication and administrator permissions on protected routes.
- Add CSRF protection to data-changing forms.
- Escape dynamic output to help prevent XSS.
- Validate uploaded image types and sizes; generate safe filenames.
- Never trust prices, totals, roles, or payment results submitted by the browser.
- Use database transactions for order creation and stock changes.
- Never collect real payment PINs or OTPs in the simulated payment flow.

## Learning Objectives

- Object-oriented programming in PHP
- Namespaces and Composer PSR-4 autoloading
- Custom routing and MVC architecture
- MySQL database operations with PDO
- CRUD operations, sessions, and authentication
- Form validation and file uploads
- Shopping cart and checkout workflows
- Basic web application security

## Development Roadmap

1. Project setup, Composer, and namespaces
2. PDO database connection class
3. Custom router
4. Request, response, and base controller classes
5. MVC integration
6. Authentication and middleware
7. Category and product management
8. Customer storefront
9. Session-based shopping cart
10. Checkout and order creation
11. Simulated payment processing
12. Testing and security improvements

## Future Improvements

- Product reviews and ratings
- Wishlist
- Order invoice generation
- Email notifications
- Advanced product filtering
- Real payment gateway integration, subject to provider approval and requirements

## Author

**Md Sumon**  
Trainee ID: 1297460  
Batch: PWAD/USSL-M/73/01

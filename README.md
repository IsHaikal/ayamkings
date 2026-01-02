# 🍗 AyamKings - Food Ordering System

A modern, full-stack food ordering system built for restaurant management with customer ordering, staff management, and admin analytics.

![PHP](https://img.shields.io/badge/PHP-777BB4?style=flat&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-4479A1?style=flat&logo=mysql&logoColor=white)
![TailwindCSS](https://img.shields.io/badge/Tailwind_CSS-38B2AC?style=flat&logo=tailwind-css&logoColor=white)
![Vercel](https://img.shields.io/badge/Vercel-000000?style=flat&logo=vercel&logoColor=white)
![Railway](https://img.shields.io/badge/Railway-0B0D0E?style=flat&logo=railway&logoColor=white)

## 🌐 Live Demo

- **Frontend:** [https://ayamkings.vercel.app](https://ayamkings.vercel.app)
- **Backend API:** [https://ayamkings-production.up.railway.app](https://ayamkings-production.up.railway.app)

---

## ✨ Features

### 👤 Customer Features
- 🛒 Browse menu with categories filter
- ⭐ View ratings & reviews
- 📝 Write and edit reviews
- 🛍️ Add to cart and place orders
- 📋 View order history
- 👤 Profile management
- 🔐 Email/Password + Google OAuth login

### 👨‍🍳 Staff Features
- 📋 View incoming orders
- ✅ Accept/Reject orders
- 🔔 Mark orders as "Ready"
- 📊 Daily specials management

### 👑 Admin Features
- 📊 Dashboard with analytics
- 🍔 Full menu CRUD (Create, Read, Update, Delete)
- 👥 User management
- 🎟️ Coupon management
- 📈 Sales reports & statistics
- 🖼️ Image upload via Cloudinary

---

## 🏗️ Project Structure

```
ayamkings/
├── ayamkings_frontend/          # Frontend (HTML/JS/CSS)
│   ├── index.html               # Landing page
│   ├── customer_login.html      # Customer login (Email + Google OAuth)
│   ├── customer_register.html   # Customer registration
│   ├── customer_menu.html       # Menu browsing & ordering
│   ├── customer_profile.html    # Profile management
│   ├── staff_login.html         # Staff/Admin login
│   ├── staff_register.html      # Staff registration
│   ├── staff_dashboard.html     # Staff order management
│   ├── admin_dashboard.html     # Admin full dashboard
│   ├── config.js                # API configuration
│   └── uploads/                 # Local image uploads
│
├── ayamkings_backend/           # Backend API (PHP)
│   ├── db_config.php            # Database configuration
│   ├── login.php                # Email/Password authentication
│   ├── google_login.php         # Google OAuth handler
│   ├── register.php             # User registration
│   ├── get_menu.php             # Fetch menu items
│   ├── menu_crud.php            # Menu CRUD operations
│   ├── place_order.php          # Create orders
│   ├── get_orders.php           # Fetch orders
│   ├── update_order_status.php  # Update order status
│   ├── get_reviews.php          # Fetch reviews
│   ├── add_review.php           # Submit reviews
│   ├── update_review.php        # Edit reviews
│   ├── upload_image.php         # Image upload (Cloudinary)
│   ├── cloudinary_config.php    # Cloudinary settings
│   ├── get_statistics.php       # Admin analytics
│   └── health.php               # API health check
│
├── database/                    # Database schema
│   └── ayamkings_db.sql         # MySQL schema file
│
├── vercel.json                  # Vercel deployment config
├── build.js                     # Build script for Vercel
└── README.md                    # This file
```

---

## 🛠️ Tech Stack

| Layer | Technology |
|-------|------------|
| Frontend | HTML5, JavaScript (ES6+), TailwindCSS |
| Backend | PHP 8.x |
| Database | MySQL 8.x |
| Image Storage | Cloudinary |
| Frontend Hosting | Vercel |
| Backend Hosting | Railway |
| Authentication | Email/Password + Google OAuth 2.0 |

---

## 🚀 Setup & Installation

### Prerequisites
- XAMPP (PHP 8.x + MySQL)
- Git
- Node.js (for build tools)

### Local Development

1. **Clone the repository**
   ```bash
   git clone https://github.com/IsHaikal/ayamkings.git
   cd ayamkings
   ```

2. **Setup Database**
   - Start XAMPP (Apache + MySQL)
   - Create database `ayamkings_db`
   - Import `database/ayamkings_db.sql`

3. **Configure Backend**
   ```bash
   cp ayamkings_backend/db_config.example.php ayamkings_backend/db_config.php
   ```
   Edit `db_config.php` with your local credentials.

4. **Run Development Server**
   ```bash
   # Using PowerShell script
   ./start_dev.ps1
   
   # Or manually start XAMPP and open in browser
   ```

5. **Access the app**
   - Frontend: `http://localhost/Coding%20PSM/ayamkings_frontend/`
   - Backend API: `http://localhost/Coding%20PSM/ayamkings_backend/`

---

## ☁️ Deployment

### Frontend (Vercel)
1. Connect GitHub repo to Vercel
2. Set environment variable: `BACKEND_URL=https://your-railway-url.up.railway.app`
3. Deploy

### Backend (Railway)
1. Create new Railway project
2. Add MySQL database
3. Set environment variables:
   - `DB_HOST`, `DB_USER`, `DB_PASSWORD`, `DB_NAME`
   - `CLOUDINARY_CLOUD_NAME`, `CLOUDINARY_API_KEY`, `CLOUDINARY_API_SECRET`
4. Deploy from GitHub

---

## 🔐 Environment Variables

### Backend (Railway)
```env
DB_HOST=your-mysql-host
DB_USER=your-username
DB_PASSWORD=your-password
DB_NAME=railway
CLOUDINARY_CLOUD_NAME=your-cloud-name
CLOUDINARY_API_KEY=your-api-key
CLOUDINARY_API_SECRET=your-api-secret
```

### Frontend (Vercel)
```env
BACKEND_URL=https://your-backend.up.railway.app
```

---

## 📱 User Roles

| Role | Access |
|------|--------|
| **Customer** | Browse menu, order food, write reviews |
| **Staff** | Manage orders, update status |
| **Admin** | Full access to dashboard, menu, users, analytics |

---

## 🧪 Testing

### Default Test Accounts
| Role | Email | Password |
|------|-------|----------|
| Admin | admin@ayamkings.com | admin123 |
| Staff | staff@ayamkings.com | staff123 |
| Customer | customer@test.com | test123 |

---

## 📄 API Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/login.php` | User login |
| POST | `/register.php` | User registration |
| POST | `/google_login.php` | Google OAuth login |
| GET | `/get_menu.php` | Fetch all menu items |
| POST/PUT/DELETE | `/menu_crud.php` | Menu CRUD |
| POST | `/place_order.php` | Create order |
| GET | `/get_orders.php` | Fetch orders |
| PUT | `/update_order_status.php` | Update order status |
| GET | `/get_reviews.php?menu_item_id=X` | Get reviews |
| POST | `/add_review.php` | Submit review |
| GET | `/get_statistics.php` | Admin analytics |
| GET | `/health.php` | API health check |

---

## 👨‍💻 Author

**Muhammad Haikal** - Final Year Project (PSM)

---

## 📝 License

This project is for educational purposes (Final Year Project / PSM).

---

## 🙏 Acknowledgements

- [TailwindCSS](https://tailwindcss.com/) - CSS Framework
- [Font Awesome](https://fontawesome.com/) - Icons
- [Cloudinary](https://cloudinary.com/) - Image hosting
- [Vercel](https://vercel.com/) - Frontend hosting
- [Railway](https://railway.app/) - Backend hosting

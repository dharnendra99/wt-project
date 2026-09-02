# AutoPulse - Automotive News, Reviews & Car Buyer Portal

> Inspired by **Autocar India's** visual hierarchy, editorial layout, and technical depth.  
> Built with **HTML5, CSS3, JavaScript, AngularJS, PHP (PDO), and MySQL**.

---

## 🌟 Key Highlights & Design Direction

- **Brand & Theme**:
  - Primary Red: `#D90000` (Logo accent, active nav underline, CTA buttons, price highlights, trending badges)
  - Background: Clean White (`#FFFFFF`) with Section separator tone (`#F5F5F5`)
  - Typography: Bold sans-serif headings with dark charcoal `#1A1A1A` and meta text `#666666`
- **10 Core Autocar India-Inspired Sections on Homepage**:
  1. **Top Bar**: Logo on left, Location/City selector (e.g. Delhi, Mumbai), and search modal toggle
  2. **Horizontal Nav**: Car news, Bike news, Motorsport, Reviews, All cars, Upcoming cars, Compare Cars
  3. **Hero / Featured Carousel**: Image slider with text overlay, author avatar & time-ago timestamp
  4. **Suggested / Latest News Feed**: Left-thumbnail cards with view counts & timestamps
  5. **News by Models**: Pill/chip tags filtering news by specific car models
  6. **Trending News**: Numbered ranking badges (1, 2, 3, 4) with view count badges (e.g. `135K+`)
  7. **Editorial Team**: Horizontal scrollable circular author avatars
  8. **Explore Quick-Links**: Explore Cars, Bikes, Expert's Advice, Reviews, Blogs
  9. **Trending & Upcoming Car Grids**: Cards with prices in bold red and ex-showroom labels
  10. **Multi-Column Dark Footer**: Comprehensive links, social badges, copyright, and floating rule-based chatbot
- **Interactive Rule-Based Chatbot Assistant**:
  - Bottom-right floating red button `#D90000`
  - Dynamic vehicle querying (e.g., *"What is the price of Nexon?"*, *"Tell me about Creta"*, *"Compare Swift and Nexon"*)
  - Offline rules knowledge base from MySQL / JSON with quick suggestions
  - 100% offline & API-free — zero external subscriptions or API keys needed.
- **Side-by-Side Car Comparator**:
  - Interactive selection of 2 or 3 cars across price, dimensions, engines, fuel efficiency, and crash safety ratings.
- **Admin Content Management System (CMS)**:
  - Dashboard with summary metric cards
  - CRUD management for cars with image upload
  - Article publishing with author bylines and category tags
  - User review & comment moderation

---

## 🚀 How to Run Locally (XAMPP / WAMP)

1. **Start Apache and MySQL**:
   - Open **XAMPP Control Panel**.
   - Start **Apache** and **MySQL** (ports 3306 or 3307).
2. **Import the Database**:
   - Open `http://localhost/phpmyadmin` in your browser.
   - Create a new database named `autopulse_db`.
   - Click **Import** and select `database.sql` from this project folder.
   - Alternatively, the schema automatically connects and detects ports `3307` and `3306`.
3. **Launch the Portal**:
   - Open your browser to:
     ```text
     http://localhost/wt/index.php
     ```
   - Access the Admin Portal at:
     ```text
     http://localhost/wt/admin/login.php
     ```
   - **Admin Credentials**:
     - Email: `admin@autopulse.com`
     - Password: `admin123`
   - **Demo User Credentials**:
     - Email: `rahul@example.com`
     - Password: `user123`

---

## ☁️ How to Deploy the Frontend to Vercel

The frontend is built with pure static **HTML, CSS, JavaScript, and AngularJS (1.8.2)**, complete with offline mock JSON datasets in `/data` and client-side fallback engines. It is 100% ready for Vercel deployment without needing PHP runtime!

### Option 1: Deploy via GitHub (Recommended)
1. Push this project to your GitHub repository.
2. Go to [vercel.com](https://vercel.com) and click **"Add New Project"**.
3. Import your repository.
4. Keep the default settings (Framework Preset: **Other**) and click **"Deploy"**.
5. Your live site will immediately be available at `https://your-project.vercel.app/`!

### Option 2: Deploy via Vercel CLI
```bash
npm install -g vercel
vercel
```

---

## 📂 Project Directory Structure

```text
/wt
│── index.html              # Frontend Homepage (AngularJS + Static)
│── index.php               # Local PHP Homepage (MySQL PDO)
│── cars.html / cars.php    # Car Listings with instant filters
│── car-detail.html / .php  # Vehicle specs, interactive gallery & reviews
│── compare.html / .php     # Side-by-side 2-3 car comparison tool
│── news.html / news.php    # News listing by categories & model tags
│── news-detail.html / .php # Full article reader with user comments
│── reviews.html / .php     # Star ratings and owner reviews
│── admin.html              # Frontend AngularJS Admin CMS
│── database.sql            # Complete database schema and seed data
│── chatbot.php             # Rule-based offline chatbot endpoint
│── vercel.json             # Vercel deployment routing configuration
│
├── /includes
│   ├── db_connect.php      # Secure PDO connection (smart port fallback 3307/3306)
│   ├── functions.php       # Common helpers (time_ago, pricing, auth, stars)
│   ├── header.php          # Shared header component with city selector & nav
│   └── footer.php          # Shared dark footer with floating chatbot widget
│
├── /admin
│   ├── index.php           # Admin metrics dashboard
│   ├── login.php / logout  # Admin session authentication
│   ├── cars.php            # Cars CRUD listing
│   ├── car-add.php         # Add car with image upload
│   ├── car-edit.php        # Edit car details
│   ├── news.php            # News articles CRUD
│   ├── news-add.php        # Publish new article
│   ├── reviews.php         # Moderate owner reviews
│   └── comments.php        # Moderate article comments
│
├── /api
│   ├── cars.php            # REST JSON endpoint for cars
│   ├── news.php            # REST JSON endpoint for news
│   ├── reviews.php         # REST JSON endpoint for reviews
│   ├── filter-cars.php     # AJAX filtering endpoint
│   └── toggle-wishlist.php # AJAX wishlist bookmarking
│
├── /assets
│   ├── /css
│   │   ├── style.css       # Complete Autocar India design styling
│   │   └── responsive.css  # Mobile and tablet responsiveness
│   ├── /js
│   │   ├── angular.min.js  # Offline AngularJS library
│   │   ├── app.js          # AngularJS controllers and services
│   │   ├── main.js         # City selector, search, mobile menu
│   │   ├── slider.js       # Hero carousel image slider
│   │   ├── ajax-filter.js  # AJAX car filtering
│   │   └── chatbot.js      # Chatbot UI and rule matcher
│   └── /images             # Vector SVG assets for cars, news & avatars
│
└── /data
    ├── cars.json           # Offline cars dataset for Vercel
    ├── news.json           # Offline news dataset for Vercel
    └── reviews.json        # Offline reviews dataset for Vercel
```

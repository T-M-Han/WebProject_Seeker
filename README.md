# WebProject_Seeker
An academic web project for an online sneakers store that allows users to browse, search, and purchase sneakers. 
This project demonstrates the integration of front-end design with a backend database system for product management.

## Features
- User registration and login
- Search for sneakers by brand, category, or price
- Shopping cart functionality
- Admin panel for adding/editing/deleting products
- SQL database integration for data storage

## Technology Stack
- **Front-End:** HTML, CSS, JavaScript
- **Back-End:** PHP
- **Database:** MySQL
- **Web Server:** Apache

## Installation
1. Clone the repository: git clone - https://github.com/T-M-Han/WebProject_Seeker.git
2. Move files:Copy the project files(src/Final) to your web server's root directory (htdocs for XAMPP or www for WAMP).
3. Import the database:
    Open phpMyAdmin and create a new database.
    Import the seekerdb.sql file from the /database folder.
4. Configure database: 
    Update the database connection settings in config.php:
        $servername = "localhost";
        $username = "root";
        $password = "";
        $dbname = "sneaker_store";
5. Access the project: http://localhost/Final/3main/1new.php

## Usage
1. Register or log in as a user.
2. Browse products on the homepage or search for specific sneakers.
3. Add sneakers to your shopping cart.
4. Proceed to checkout and complete the purchase.
5. Admins can log in to the admin panel for product management.

## Folder Structure
- /src          - Contains web project files (HTML, CSS, JS, PHP)
- /database     - Database file (seekerdb.sql)
- /docs         - Project Proposal, Report, User Manual
- README.md     

## Contact
Thaw Myo Han  
- Email: thawmyohan736@gmail.com  
- GitHub: [yourusername](https://github.com/yourusername)

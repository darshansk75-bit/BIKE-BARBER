# ROLE AND CONTEXT
You are an expert Full-Stack PHP Developer, Database Architect, and QA Engineer. You will act as my dedicated pair-programmer for the next 2 months to build my college project: "Automation Bike Accessories and Services (SaaS Website)". 

I have attached a file named Roadmap.md. You must read and internalize this entire document before proceeding. 

# PROJECT OVERVIEW ("THE WHY" & "THE WHAT")
* **What we are building:** A centralized, web-based system to automate the sales, inventory, and service operations of a bike accessories shop. 
* **Why we are building it:** To eliminate manual errors, automate GST billing, provide real-time inventory tracking, streamline manufacturer procurement, and maximize customer retention through a seamless UI/UX.
* **Target Audience:** Customers (browse, book, buy), Admins (manage, analyze, approve), and Manufacturers (supply, track payments).
* **Environment:** Strictly local WAMP server (Windows, Apache, MySQL, PHP). 

# STRICT TECH STACK & ARCHITECTURAL RULES
* **Backend:** Vanilla PHP. **CRITICAL:** Use mysqli exclusively. DO NOT use PDO.
* **Frontend:** HTML, CSS, Vanilla JavaScript, Bootstrap.
* **Database:** MySQL (Strictly adhere to the 1NF-3NF normalized schema provided in the markdown).
* **Security Must-Haves:** Encrypt sensitive info (OTP, passwords) via hash functions, use PHPMailer for automated emails, implement strict session/cookie management, encrypt URLs to prevent manual tweaking, and globally suppress/hide raw PHP errors from the UI (show custom caution messages instead).
* **Out of Scope:** NO online payment gateways, NO mobile apps, NO advanced AI forecasting.

# EXECUTION PROTOCOL (CRITICAL INSTRUCTIONS)
As this is a 2-month project built in my free time, we will NOT use a day-by-day timeline. We will use a STRICT **Module-by-Module Execution** workflow.
1.  **Stop and Wait:** You must NEVER write code for a module, execute terminal commands, or move to the next phase until I explicitly type: "Command: Proceed to [Module Name]".
2.  **Context Matching:** When I paste UI code snippets from external sources, you must analyze them and integrate them seamlessly into our existing project context and styling.
3.  **Holistic Approach:** For every module, you must consider its Frontend UI, Backend Logic, Database queries, and Security implications.

# YOUR TASK RIGHT NOW
Based on the Roadmap.md, generate a highly precise, highly detailed, module-wise development roadmap. Break down the project from start to finish. Ensure nothing from the markdown is missed. 

Structure your response exactly like this:

1.  **Phase 0: Architecture & Database Setup:** Outline the SQL script generation for all 15 tables.
2.  **Phase 1: Security & Authentication Core:** Outline login/registration, Google/Facebook Auth, session management, and URL encryption.
3.  **Phase 2: Primary Modules (1-4, 6):** Break down Admin, Customer, Product/Inventory (including the CSV/Excel upload feature), Order/GST billing, and Manufacturer management.
4.  **Phase 3: Secondary Modules (5, 7, 8):** Break down Service Booking, Warranty/AMC, and the Automated Email Notification system.
5.  **Phase 4: Comprehensive Testing:** Outline the specific test cases mentioned (White box, Black box, SQL Injection, Auth bypass, Form validation).

Do not write any actual application code or SQL scripts yet. Provide only the detailed roadmap. End your response by asking for my explicit permission to begin coding Phase 0.Roadmap.md File

Hey gemini generate me a complete roadmap prompt which I will use generate a saas website where i will give the topic for the model to create components of the website and it must not execute further modules until my command i will do this project for 2 months so just do the things as per the task provided by me on that particular day
I will be adding some ui code for references from other sources it must replica the context
My college project title : Automation Bike accessories and Services (Saas Website)

Hardware Requirements:
 Operating System: Windows 10 / Windows 11
 RAM: 4 GB or above
 Storage (Memory): 256 GB Hard Disk / SSD
 Processor: Intel Core (TM) i3 or AMD RYZEN 3 3200G

Software Requirements:
 Frontend  : HTML CSS JAVASCRIPT ( VANILLA )  BOOTSTRAP
 Backend : PHP
 Database : MYSQL
 Version Control System : GIT ( Local Repository Management )
 IDE ( Integrated Development Environment ) : VS CODE ( VISUAL STUDIO CODE )
 Development Platform : WAMP ( Windows , Apache , Mysql , PhP ) Server
 Browser : Google Chrome / Microsoft Edge

1.13 LIMITATIONS OF THE PROJECT:
• The system works only in a local server environment (WAMP) and is not hosted online.
• Online payment integration is not included in the current version.
• The system is designed for single-shop management only
• Mobile application support is not available.
• Advanced analytics and AI-based forecasting features are not included.

1.14 SCOPE OR FUTURE APPLICATION:
 Integration of Online Payment Gateway
 Development of mobile application (Android )
 Multi-branch management system for expansion
 Advanced analytics and sales forecasting
 AI-based product recommendation system
 SMS and WhatsApp notification integration
 Supplier comparison and automated reordering system
 Barcode Scanner Integration for Faster Billing and Stock Control.

1.2 OBJECTIVE:
The objective of Automation Bike Accessories and Services is to automate the
sales and service operations of a bike accessories shop by  developing a
centralized web-based system. The system provides secure login access with
role-based permissions for   Admin and Users. It enables customers to browse
products, purchase accessories, and book services online with ease. The project
also implements a GST billing system incorporating CGST and SGST during
checkout. It ensures real-time inventory management to prevent stock
mismatches and automatically updates stock after sales and manufacturer
purchases. Additionally, the system manages procurement directly from
manufacturers, tracks manufacturer payments and outstanding balances, and
ultimately improves overall operational efficiency while reducing manual errors

1.3 INPUT:
• Admin Data – Information such as admin login credentials, role permissions (Admin/Staff),
and access rights for secure system control.
• Customer Details – Customer ID, name, email, phone number, address, and login
credentials for account management and order processing.
• Product Details – Product ID, product name, category, brand, price, image, and available
stock quantity.
• Service Details – Service ID, service name, service description, service charges, and
available time slots.
• Manufacturer Details – Manufacturer ID, company name, contact person, phone number,
address, and GST number for procurement management.
• Purchase Data – Records of products purchased from manufacturers including product
name, quantity, purchase price, purchase date, and payment status.
• Sales Data – Details of products sold including customer name, quantity, selling price, GST
calculation (CGST & SGST), payment status, and date of sale.
• Booking Data – Service booking information including customer name, selected service,
booking date, time slot, and booking status (Pending/Approved/Completed).

1.4 OUTPUT:
• Login Confirmation – Successful authentication of Admin or Customer with role-based
access to the system dashboard.
• Product Display Information – List of available products with category, price, stock
availability, and product details.
• Invoice & GST Calculation – Generated invoice showing product details, quantity, CGST,
SGST, total amount, and payment status.
• Order Confirmation – Confirmation message with order ID, order status
(Placed/Completed), and transaction details.
• Stock Update Information – Automatic update of inventory levels after sales and
manufacturer purchases.
• Service Booking Confirmation – Confirmation of service booking with booking ID, date, time
slot, and approval status.
• Purchase & Payment Status – Manufacturer purchase records showing payment status
(Paid/Pending/Partial) and outstanding balance.
• Reports & Dashboard Summary – Summary reports including total sales, total purchases,
revenue details, and low stock alerts for administrative review
____________________________________________________________________________________________________________________________________________________________
Takeaways :
Authentication : Google auth , Facebook
Authorization : Minimum authorization for modules
Login page : After entering the details by user it must validate credentials and allow the submit button after entering valid details
Account Switching : Website must ask user permission before switching account or logging out from the website , it must load the website from beginning if the user uses the website freshly after closing the tab
Write forms using php functions for fetching server side data
Dynamic coding of html and css tags in php
For email digital certification is required and use of phpmailer and automate it
Sensitive information like OTP , PIN Number , Passwords must be encrypted
Bypass php error to simple caution error in user interface hide php errors rather display simple cautions or invalid statements
Use mysqli rather than pdo
Encrypt url for login it must not navigate further page if user tries to tweek the url it must show error
Enable send and recive cookies from the user and save their preferences , create sessions
Encrypt data using hash functions

Firstly :
1.I will create databases and tables
2.Login/Registration page
3.UI Design
4.Further modules

My Requirement :
1.Add a feature where admin can add csv or excel file which automatically reads the content and adds the product in the inventory
2.Admin can send request for the products from the manufacturer vice versa
3.Admin can accept / reject the product order , service booking confirmations , payment tracking of the customer and manufacturer
4.Automation of email where if the user register new account , orders product , books services , warranty date remainder should be sent to the particular       customer email notification
5.Automation of product cost calculation + gst calculation
6.Add a feature for customer to upload a file for example his profile picture
7.Maintain seperate id for order id , customer id , product id... etc which must not be same it must be unique

Goal of the website:
Customer : can create there account , login , browse website , search products , order products , recive invoice in pdf format , book services , view history
Admin/Client : has his own dashboard where he can view analytics , pending orders , report viewing in daily/weekly/monthly options , can supply products from manfucaturer , and has the authority to confirm customer orders and services booked by customer , add / delete manufacturer details and can order products from him
Manufacturer : supplies product
The main motive of this website is to automate the website with minimal human interaction and maximum customer retention
____________________________________________________________________________________________________________________________________________________________
I need a complete roadmap for my project
MODULES : Total 8 modules
1	Admin & System Management
2	Customer Management
3	Product & Inventory Management
4	Order & GST Billing Management
5	Service Booking & Slot Management
6	Manufacturer & Purchase Management
7	Warranty & AMC Management
8	Notification & Reminder System

Primary modules (Current working on this )
1	Admin & System Management
2	Customer Management
3	Product & Inventory Management
4	Order & GST Billing Management
6	Manufacturer & Purchase Management

Secondary modules (After completion of primary modules I will work on these)
5	Service Booking & Slot Management
7	Warranty & AMC Management
8	Notification & Reminder System

Modules Details :
🏍 BIKE ACCESSORIES & SERVICES MANAGEMENT SYSTEM
✅ DETAILED WORKFLOWS & NORMALIZED TABLES (1NF → 3NF APPLIED)

🔵 1️⃣ Admin & System Management (PRIMARY)
**Detailed Workflow & Working:**
This module serves as the central control panel for the entire system.
- **Authentication & Security:** Secure login for Admins with role-based access. Passwords are encrypted utilizing secure hashing functions to prevent unauthorized access.
- **System Configuration (GST):** Admins can configure global CGST and SGST percentages centrally in the gst_settings table, which will be applied dynamically during the checkout process for products or services.
- **Dashboard & Analytics:** Provides an overview of overall sales, active service bookings, pending manufacturer payments, and low-stock alerts. Admins can view comprehensive reports (daily/weekly/monthly).
- **Core Operations:** Admins have the authority to accept or reject product orders, service booking confirmations, and track payments of customers and manufacturers.

📌 Table: admins
| Field      | Type                | Key |
| ---------- | ------------------- | --- |
| admin_id   | INT AUTO_INCREMENT  | PK  |
| username   | VARCHAR(100) UNIQUE |     |
| email      | VARCHAR(150) UNIQUE |     |
| password   | VARCHAR(255)        |     |
| created_at | TIMESTAMP           |     |
 
📌 Table: gst_settings
(Separated to satisfy 3NF — not stored inside orders)
| Field        | Type               | Key |
| ------------ | ------------------ | --- |
| gst_id       | INT AUTO_INCREMENT | PK  |
| cgst_percent | DECIMAL(5,2)       |     |
| sgst_percent | DECIMAL(5,2)       |     |
| updated_at   | TIMESTAMP          |     |

🔵 2️⃣ Customer Management (PRIMARY)
**Detailed Workflow & Working:**
Handles the end-user experience, allowing customers to securely interact with the platform.
- **Registration & Profile:** Customers register with a unique phone number, email, and password. Profile management allows them to update addresses and upload a profile picture.
- **Authentication:** Features secure session and cookie management. The system prompts to fill out required details securely, hiding raw PHP errors and showing custom responses. Account switching or logging out will prompt a confirmation.
- **User Dashboard:** Customers can view their order history, download PDF invoices, track active service bookings, and check their warranty/AMC status.

📌 Table: customers
| Field           | Type                | Key |
| --------------- | ------------------- | --- |
| customer_id     | INT AUTO_INCREMENT  | PK  |
| name            | VARCHAR(150)        |     |
| email           | VARCHAR(150) UNIQUE |     |
| phone           | VARCHAR(15) UNIQUE  |     |
| password        | VARCHAR(255)        |     |
| address         | TEXT                |     |
| profile_picture | VARCHAR(255)        |     |
| created_at      | TIMESTAMP           |     |

📌 Table: password_resets
| Field       | Type               | Key |
| ----------- | ------------------ | --- |
| id          | INT AUTO_INCREMENT | PK  |
| email       | VARCHAR(255)       |     |
| otp         | VARCHAR(6)         |     |
| expires_at  | DATETIME           |     |

🔵 3️⃣ Product & Inventory Management (PRIMARY)
**Detailed Workflow & Working:**
The core cataloging and automated stock tracking system.
- **Catalog Management:** Admins can manually add, update, or remove products mapped to specific categories.
- **Bulk Data Import:** Includes an automated feature for admins to upload a CSV or Excel file mapping products, instantly populating the database and avoiding manual entry errors.
- **Real-Time Stock Tracking:** Stock quantities decrement dynamically upon a customer purchase, and increment upon receiving stock from a manufacturer order.

📌 Table: categories
(Separate table → removes repeating category names → 3NF)
| Field         | Type               | Key |
| ------------- | ------------------ | --- |
| category_id   | INT AUTO_INCREMENT | PK  |
| category_name | VARCHAR(100)       |     |

📌 Table: products
| Field          | Type               | Key |
| -------------- | ------------------ | --- |
| product_id     | INT AUTO_INCREMENT | PK  |
| category_id    | INT                | FK  |
| product_name   | VARCHAR(150)       |     |
| description    | TEXT               |     |
| price          | DECIMAL(10,2)      |     |
| stock_quantity | INT DEFAULT 0      |     |
| image          | VARCHAR(255)       |     |
| created_at     | TIMESTAMP          |     |

🔵 4️⃣ Order & GST Billing Management (PRIMARY)
**Detailed Workflow & Working:**
Manages customer purchases, secure cart checkout, and automated invoice generation.
- **Cart & Checkout:** Customers browse the product catalog, search via keywords, add items to their cart, and proceed to checkout securely.
- **Automated Pricing & Taxation:** The system automatically calculates the total product cost, fetches the active percentages from gst_settings, and dynamically applies CGST and SGST on the bill to prevent manual calculation errors.
- **Order Lifecycle & Invoicing:** Orders transition through stages (Placed -> Completed). Upon confirmation, an automated PDF invoice is generated containing unique Order ID, Product Details, GST breakup, and Payment Status.

📌 Table: orders
(Only order-level data stored → 2NF satisfied)
| Field          | Type               | Key |
| -------------- | ------------------ | --- |
| order_id       | INT AUTO_INCREMENT | PK  |
| customer_id    | INT                | FK  |
| order_date     | TIMESTAMP          |     |
| order_status   | VARCHAR(50)        |     |
| payment_status | VARCHAR(50)        |     |

📌 Table: order_items
(Separate table removes partial dependency → 2NF)
| Field      | Type               | Key |
| ---------- | ------------------ | --- |
| item_id    | INT AUTO_INCREMENT | PK  |
| order_id   | INT                | FK  |
| product_id | INT                | FK  |
| quantity   | INT                |     |
| price      | DECIMAL(10,2)      |     |

🔵 5️⃣ Service Booking & Slot Management (SECONDARY)
**Detailed Workflow & Working:**
Enables customers to seamlessly schedule their bike maintenance/repair services online.
- **Service Catalog:** Admins define various services (e.g., General Service, Oil Change) along with detailed descriptions and standard charges.
- **Slot Allocation:** Customers select available dates and exact time slots for their service, preventing double-bookings.
- **Booking Lifecycle:** Bookings commence as 'Pending'. Admin checks shop capacity and accepts/rejects the booking. Upon approval, the customer is notified, and ultimately the status transitions to 'Completed' post-service.

📌 Table: services
| Field        | Type               | Key |
| ------------ | ------------------ | --- |
| service_id   | INT AUTO_INCREMENT | PK  |
| service_name | VARCHAR(150)       |     |
| description  | TEXT               |     |
| price        | DECIMAL(10,2)      |     |

📌 Table: service_bookings
| Field        | Type               | Key |
| ------------ | ------------------ | --- |
| booking_id   | INT AUTO_INCREMENT | PK  |
| customer_id  | INT                | FK  |
| service_id   | INT                | FK  |
| booking_date | DATE               |     |
| booking_time | TIME               |     |
| status       | VARCHAR(50)        |     |

🔵 6️⃣ Manufacturer & Purchase Management (PRIMARY)
**Detailed Workflow & Working:**
Streamlines the shop's B2B supply chain and procurement tracking.
- **Manufacturer Records:** Maintains a directory of suppliers including their contact details, address, and GST numbers. Admin can add, edit, or delete manufacturers.
- **Procurement Logistics:** Admin can initiate product supply requests directly to the manufacturer. This ensures seamless restocking when inventory hits low limits.
- **Financial Tracking:** Tracks purchases precisely, logging the purchase price, stock volume, purchase date, and most importantly, payment status (Paid/Pending/Partial) to maintain clear accounts of outstanding balances.

📌 Table: manufacturers
| Field            | Type               | Key |
| ---------------- | ------------------ | --- |
| manufacturer_id  | INT AUTO_INCREMENT | PK  |
| name             | VARCHAR(150)       |     |
| company_name     | VARCHAR(150)       |     |
| phone            | VARCHAR(15)        |     |
| email            | VARCHAR(150)       |     |
| gst_number       | VARCHAR(50) UNIQUE |     |
| created_at       | TIMESTAMP          |     |

📌 Table: purchases
(Product name removed → use FK → 3NF satisfied)
| Field                      | Type                 | Key |
| -------------------------- | -------------------- | --- |
| purchase_id                | INT AUTO_INCREMENT   | PK  |
| manufacturer_id            | INT                  | FK  |
| product_id                 | INT                  | FK  |
| quantity                   | INT                  |     |
| purchase_price             | DECIMAL(10,2)        |     |
| total_amount               | DECIMAL(12,2)        |     |
| request_status             | ENUM                 |     |
| manufacturer_response_date | DATETIME             |     |
| admin_notes                | TEXT                 |     |
| payment_status             | VARCHAR(50)          |     |
| purchase_date              | TIMESTAMP            |     |

🔵 7️⃣ Warranty & AMC Management (SECONDARY)
**Detailed Workflow & Working:**
Handles post-sales advantages, fostering solid customer retention through long-term servicing contracts.
- **Warranty Tracking:** Automatically assigns a warranty duration (Start and End dates) to eligible products post-purchase, binding them securely to the customer's profile.
- **Annual Maintenance Contracts (AMC)::** Customers can purchase or subscribe to AMC for periodic bike services throughout the year. The module tracks contract validity windows.
- **Status Lifecycle:** Clearly flags whether warranties or contracts are Active or Expired, allowing admin staff to quickly verify customer coverage claims via their unique customer ID.

📌 Table: warranties
| Field          | Type               | Key |
| -------------- | ------------------ | --- |
| warranty_id    | INT AUTO_INCREMENT | PK  |
| customer_id    | INT                | FK  |
| product_id     | INT                | FK  |
| warranty_start | DATE               |     |
| warranty_end   | DATE               |     |
| status         | VARCHAR(50)        |     |

📌 Table: amc_contracts
| Field        | Type               | Key |
| ------------ | ------------------ | --- |
| amc_id       | INT AUTO_INCREMENT | PK  |
| customer_id  | INT                | FK  |
| service_id   | INT                | FK  |
| start_date   | DATE               |     |
| end_date     | DATE               |     |
| status       | VARCHAR(50)        |     |

🔵 8️⃣ Notification & Reminder System (SECONDARY)
**Detailed Workflow & Working:**
An automated digital communication channel directly integrated with the application to minimize manual follow-up dependency.
- **Trigger-Based Actions:** Secures digital certification and leverages PHPMailer to send instantaneous contextual emails upon system events:
  - Account registration welcome and verifications.
  - Success emails post-order placement (shipping with PDF invoice).
  - Booking status alerts (confirmation, rejections, time slots).
- **Scheduled Automated Reminders:** Runs background jobs to send reminders to customers seamlessly prior to AMC expirations, warranty conclusions, or recurring scheduled servicing.

📌 Table: notifications
| Field           | Type               | Key |
| --------------- | ------------------ | --- |
| notification_id | INT AUTO_INCREMENT | PK  |
| customer_id     | INT                | FK  |
| message         | TEXT               |     |
| type            | VARCHAR(50)        |     |
| sent_at         | TIMESTAMP          |     |

🎯 NORMALIZATION CONFIRMATION
✅ 1NF
- No repeating columns
- Atomic values
- One record per row

✅ 2NF
- Order items separated from orders
- No partial dependency on composite keys

✅ 3NF
- Categories separated
- Manufacturers separated
- GST separated
- No transitive dependency

📊 FINAL TABLE COUNT
admins, gst_settings, customers, password_resets, categories, products, orders, order_items, services, service_bookings, manufacturers, purchases, warranties, amc_contracts, notifications
____________________________________________________________________________________________________________________________________________________________
Website Pages
Refer these pages / website its just for reference purpose
1.https://wroom.co.in
2.https://www.sparify.co
3.https://bandidospitstop.com/?srsltid=AfmBOorXm_1mCJv4tIRF-f2pABrabXANVx98zuwVmIyGR6h9MYhH609l
____________________________________________________________________________________________________________________________________________________________
Testing cases : It should pass all test cases after the completion of the project , I will give some of the test cases according to the project you ensure the website should pass all such test cases if tester examines the ability of the website make sure it must pass his tests
I will perform [ White box testing , black box testing , Code testing , Specification testing , Unit testing , system testing , integration testing ,acceptance testing ]

Test Objectives :
The system should handles attack performed by the user Example : SQL INJECTION , DDOS .... etc
The system is tested with variety of inputs .The system is tested for accuracy and correctness of the results obtained.Finally the system is tested for inter-operability
All field entries must work properly
Pages must be activated the identified link
The entry screen, messages and responses must not be delayed

Login Form :
Leave the username and password textbox blank and press login ( Prompts to fill the required details)
Enter the username and password (Homepage must be displayed)
Enter invalid username and password (Error message must be displayed and should allow to enter next page)
Leave the text boxes blank in insert and edit form ( Prompt to fill the required details)

Features to be tested
Verify that the entries are of the correct format
No duplicate entries should be allowed
All links should take the user to their respective page

Leave the Form unfilled  and press submit  button (Prompts to fill the required details)
Leave one of the fields blank (Prompts to fill the required details)
Fill the required details (Inserted record successfully)


 LIMITATIONS OF THE PROJECT
● No online payment: User cannot make online payments through UPI, card or wallet only
charges are calculated.
● No service module: Extra service like printing, scanning, gaming or lamination are not
tracked or charged.

Conclusion:
The Automation Bike accessories and services developed as part of this project successfully automates and streamlines the core operations of a bike  accessories shop, including user management, computer tracking, and time-based billing. The system ensures efficient handling  of users and real-time computer usage monitoring, offering accuracy and reducing manual effort. While the current version includes essential features like user login, session tracking, and  charge calculation, it also leaves room for future enhancements such as online payment  integration, service modules, invoice generation, and role-based access control.  Overall, this project not only demonstrates practical implementation of database management  and PHP-based web development but also serves as a solid foundation for scaling into a more comprehensive commercial solution in the future.

# AyamKings System - Comprehensive Test Cases

## System Overview
**Project Name:** AyamKings Food Ordering System  
**Version:** 2.0  
**Date:** 11 January 2026  
**Technology Stack:** HTML, CSS (Tailwind), JavaScript, PHP, MySQL, ToyyibPay API

---

## Table of Contents
1. [Module 1: User Authentication](#module-1-user-authentication)
2. [Module 2: Customer Menu & Ordering](#module-2-customer-menu--ordering)
3. [Module 3: Cart Management](#module-3-cart-management)
4. [Module 4: Order Processing](#module-4-order-processing)
5. [Module 5: Payment Integration (ToyyibPay)](#module-5-payment-integration-toyyibpay)
6. [Module 6: Review System](#module-6-review-system)
7. [Module 7: Coupon System](#module-7-coupon-system)
8. [Module 8: Daily Specials](#module-8-daily-specials)
9. [Module 9: User Profile Management](#module-9-user-profile-management)
10. [Module 10: Staff Dashboard](#module-10-staff-dashboard)
11. [Module 11: Admin Dashboard](#module-11-admin-dashboard)
12. [Module 12: Financial Reports](#module-12-financial-reports)
13. [Module 13: Session Management](#module-13-session-management)

---

## Module 1: User Authentication

### TC-AUTH-001: Customer Registration
| Field | Description |
|-------|-------------|
| **Test Case ID** | TC-AUTH-001 |
| **Module** | Authentication |
| **Test Scenario** | Customer creates new account |
| **Pre-conditions** | User on registration page, email not registered |
| **Test Steps** | 1. Navigate to customer_register.html<br>2. Enter full name, email, phone, password<br>3. Confirm password<br>4. Click "Register" button |
| **Test Data** | Name: "Ahmad Ali", Email: "ahmad@test.com", Phone: "0123456789", Password: "Pass123!" |
| **Expected Result** | Account created successfully, redirected to login page |
| **Status** | ☐ Pass / ☐ Fail |

### TC-AUTH-002: Customer Login (Valid Credentials)
| Field | Description |
|-------|-------------|
| **Test Case ID** | TC-AUTH-002 |
| **Module** | Authentication |
| **Test Scenario** | Customer login with valid credentials |
| **Pre-conditions** | Customer account exists in database |
| **Test Steps** | 1. Navigate to customer_login.html<br>2. Enter registered email<br>3. Enter correct password<br>4. Click "Login" button |
| **Test Data** | Email: "ahmad@test.com", Password: "Pass123!" |
| **Expected Result** | Login successful, redirected to customer menu, welcome message displayed |
| **Status** | ☐ Pass / ☐ Fail |

### TC-AUTH-003: Customer Login (Invalid Credentials)
| Field | Description |
|-------|-------------|
| **Test Case ID** | TC-AUTH-003 |
| **Module** | Authentication |
| **Test Scenario** | Customer login with wrong password |
| **Pre-conditions** | Customer account exists |
| **Test Steps** | 1. Navigate to customer_login.html<br>2. Enter registered email<br>3. Enter wrong password<br>4. Click "Login" button |
| **Test Data** | Email: "ahmad@test.com", Password: "WrongPass!" |
| **Expected Result** | Error message "Invalid email or password" displayed, user stays on login page |
| **Status** | ☐ Pass / ☐ Fail |

### TC-AUTH-004: Staff/Admin Login
| Field | Description |
|-------|-------------|
| **Test Case ID** | TC-AUTH-004 |
| **Module** | Authentication |
| **Test Scenario** | Staff/Admin login via portal |
| **Pre-conditions** | Staff/Admin account exists |
| **Test Steps** | 1. Navigate to staff_admin_portal.html<br>2. Select role (Staff/Admin)<br>3. Enter email and password<br>4. Click Login |
| **Test Data** | Email: "staff@ayamkings.com", Password: "Staff123!", Role: "staff" |
| **Expected Result** | Login successful, redirected to appropriate dashboard |
| **Status** | ☐ Pass / ☐ Fail |

### TC-AUTH-005: Logout Functionality
| Field | Description |
|-------|-------------|
| **Test Case ID** | TC-AUTH-005 |
| **Module** | Authentication |
| **Test Scenario** | User logout from system |
| **Pre-conditions** | User is logged in |
| **Test Steps** | 1. Click logout button<br>2. Confirm logout |
| **Expected Result** | Session cleared, localStorage cleared, redirected to index page |
| **Status** | ☐ Pass / ☐ Fail |

### TC-AUTH-006: Google Login
| Field | Description |
|-------|-------------|
| **Test Case ID** | TC-AUTH-006 |
| **Module** | Authentication |
| **Test Scenario** | Customer login using Google account |
| **Pre-conditions** | User has valid Google account |
| **Test Steps** | 1. Navigate to customer_login.html<br>2. Click "Continue with Google" button<br>3. Select Google account<br>4. Authorize access |
| **Expected Result** | Login successful, new account created if first time, redirected to customer menu, password section hidden in profile |
| **Status** | ☐ Pass / ☐ Fail |

---

## Module 2: Customer Menu & Ordering

### TC-MENU-001: View Menu Items
| Field | Description |
|-------|-------------|
| **Test Case ID** | TC-MENU-001 |
| **Module** | Menu |
| **Test Scenario** | Customer views all menu items |
| **Pre-conditions** | Customer logged in, menu items exist in database |
| **Test Steps** | 1. Navigate to customer_menu.html<br>2. View menu grid |
| **Expected Result** | All menu items displayed with image, name, price, category |
| **Status** | ☐ Pass / ☐ Fail |

### TC-MENU-002: Filter Menu by Category
| Field | Description |
|-------|-------------|
| **Test Case ID** | TC-MENU-002 |
| **Module** | Menu |
| **Test Scenario** | Filter menu items by category |
| **Pre-conditions** | Multiple categories exist |
| **Test Steps** | 1. Click on category filter button (e.g., "Chicken")<br>2. Observe filtered results |
| **Test Data** | Category: "Chicken" |
| **Expected Result** | Only items from selected category displayed |
| **Status** | ☐ Pass / ☐ Fail |

### TC-MENU-003: Search Menu Items
| Field | Description |
|-------|-------------|
| **Test Case ID** | TC-MENU-003 |
| **Module** | Menu |
| **Test Scenario** | Search for menu items by keyword |
| **Pre-conditions** | Menu items exist |
| **Test Steps** | 1. Type search term in search box<br>2. Observe filtered results |
| **Test Data** | Search: "Ayam Goreng" |
| **Expected Result** | Only matching items displayed, real-time filtering |
| **Status** | ☐ Pass / ☐ Fail |

### TC-MENU-004: View Item Detail
| Field | Description |
|-------|-------------|
| **Test Case ID** | TC-MENU-004 |
| **Module** | Menu |
| **Test Scenario** | View detailed information of menu item |
| **Pre-conditions** | Menu item exists |
| **Test Steps** | 1. Click on menu item card<br>2. View detail modal |
| **Expected Result** | Modal displays full image, name, price, description, rating, reviews |
| **Status** | ☐ Pass / ☐ Fail |

---

## Module 3: Cart Management

### TC-CART-001: Add Item to Cart
| Field | Description |
|-------|-------------|
| **Test Case ID** | TC-CART-001 |
| **Module** | Cart |
| **Test Scenario** | Add menu item to shopping cart |
| **Pre-conditions** | Customer logged in, cart may be empty or have items |
| **Test Steps** | 1. Click "Add to Cart" button on menu item |
| **Expected Result** | Item added to cart, cart count increases, success message shown |
| **Status** | ☐ Pass / ☐ Fail |

### TC-CART-002: Update Item Quantity
| Field | Description |
|-------|-------------|
| **Test Case ID** | TC-CART-002 |
| **Module** | Cart |
| **Test Scenario** | Change quantity of cart item |
| **Pre-conditions** | Item already in cart |
| **Test Steps** | 1. Open cart modal<br>2. Click +/- buttons to change quantity |
| **Expected Result** | Quantity updated, total price recalculated |
| **Status** | ☐ Pass / ☐ Fail |

### TC-CART-003: Remove Item from Cart
| Field | Description |
|-------|-------------|
| **Test Case ID** | TC-CART-003 |
| **Module** | Cart |
| **Test Scenario** | Remove item from cart |
| **Pre-conditions** | Item exists in cart |
| **Test Steps** | 1. Open cart modal<br>2. Click remove/delete button on item |
| **Expected Result** | Item removed, cart total updated |
| **Status** | ☐ Pass / ☐ Fail |

### TC-CART-004: Cart Persistence
| Field | Description |
|-------|-------------|
| **Test Case ID** | TC-CART-004 |
| **Module** | Cart |
| **Test Scenario** | Cart items persist after page refresh |
| **Pre-conditions** | Items in cart |
| **Test Steps** | 1. Add items to cart<br>2. Refresh page<br>3. Check cart |
| **Expected Result** | Cart items still present (stored in localStorage) |
| **Status** | ☐ Pass / ☐ Fail |

---

## Module 4: Order Processing

### TC-ORDER-001: Place Order Successfully
| Field | Description |
|-------|-------------|
| **Test Case ID** | TC-ORDER-001 |
| **Module** | Order |
| **Test Scenario** | Customer completes checkout process |
| **Pre-conditions** | Items in cart, customer logged in |
| **Test Steps** | 1. Open cart<br>2. Click "Checkout"<br>3. Select payment method<br>4. Confirm payment |
| **Expected Result** | Order placed, order ID generated, cart cleared, success message |
| **Status** | ☐ Pass / ☐ Fail |

### TC-ORDER-002: View Order History
| Field | Description |
|-------|-------------|
| **Test Case ID** | TC-ORDER-002 |
| **Module** | Order |
| **Test Scenario** | Customer views past orders |
| **Pre-conditions** | Customer has placed orders before |
| **Test Steps** | 1. Click "Order History" button<br>2. View order history modal |
| **Expected Result** | All past orders displayed with order ID, date, items, total, status |
| **Status** | ☐ Pass / ☐ Fail |

### TC-ORDER-003: Order Status Display
| Field | Description |
|-------|-------------|
| **Test Case ID** | TC-ORDER-003 |
| **Module** | Order |
| **Test Scenario** | Order status shown correctly |
| **Pre-conditions** | Orders with different statuses exist |
| **Test Steps** | 1. View order history<br>2. Check status badges |
| **Expected Result** | Status badges show correct colors: Pending (amber), Preparing (blue), Ready (indigo), Completed (green), Cancelled (red) |
| **Status** | ☐ Pass / ☐ Fail |

### TC-ORDER-004: Order with Complete Status Icon
| Field | Description |
|-------|-------------|
| **Test Case ID** | TC-ORDER-004 |
| **Module** | Order |
| **Test Scenario** | Completed orders show green checkmark icon |
| **Pre-conditions** | Completed order exists |
| **Test Steps** | 1. View order history<br>2. Find completed order<br>3. Check icon |
| **Expected Result** | Green checkmark icon (fa-check-circle) displayed for finished/completed orders |
| **Status** | ☐ Pass / ☐ Fail |

---

## Module 5: Payment Integration (ToyyibPay)

### TC-PAY-001: Online Payment - Successful
| Field | Description |
|-------|-------------|
| **Test Case ID** | TC-PAY-001 |
| **Module** | Payment |
| **Test Scenario** | Customer completes online payment via ToyyibPay |
| **Pre-conditions** | Items in cart, customer logged in |
| **Test Steps** | 1. Click Checkout<br>2. Select payment method (Card/FPX/QR)<br>3. Redirect to ToyyibPay<br>4. Complete payment<br>5. Redirect back to success page |
| **Expected Result** | Order created, payment_status = 'paid', order visible to staff, success page shown |
| **Status** | ☐ Pass / ☐ Fail |

### TC-PAY-002: Online Payment - Failed/Cancelled
| Field | Description |
|-------|-------------|
| **Test Case ID** | TC-PAY-002 |
| **Module** | Payment |
| **Test Scenario** | Customer cancels or fails payment |
| **Pre-conditions** | Order created, redirected to ToyyibPay |
| **Test Steps** | 1. At ToyyibPay payment page<br>2. Click cancel or payment fails<br>3. Redirect back |
| **Expected Result** | Order remains with payment_status = 'pending', not visible to staff |
| **Status** | ☐ Pass / ☐ Fail |

### TC-PAY-003: Order Only Visible After Payment
| Field | Description |
|-------|-------------|
| **Test Case ID** | TC-PAY-003 |
| **Module** | Payment |
| **Test Scenario** | Staff only sees paid orders |
| **Pre-conditions** | Unpaid and paid orders exist |
| **Test Steps** | 1. Create order without completing payment<br>2. Check staff dashboard<br>3. Complete payment<br>4. Check staff dashboard again |
| **Expected Result** | Unpaid orders hidden from staff, paid orders visible |
| **Status** | ☐ Pass / ☐ Fail |

### TC-PAY-004: Payment Success Page Flow
| Field | Description |
|-------|-------------|
| **Test Case ID** | TC-PAY-004 |
| **Module** | Payment |
| **Test Scenario** | After payment, user can navigate to order history |
| **Pre-conditions** | Payment completed |
| **Test Steps** | 1. Complete payment<br>2. View success page<br>3. Click "View Order History" |
| **Expected Result** | Redirect to customer menu with order history modal auto-opened |
| **Status** | ☐ Pass / ☐ Fail |

---

## Module 6: Review System

### TC-REV-001: Submit New Review
| Field | Description |
|-------|-------------|
| **Test Case ID** | TC-REV-001 |
| **Module** | Review |
| **Test Scenario** | Customer submits review for menu item |
| **Pre-conditions** | Customer logged in, item not reviewed by this user |
| **Test Steps** | 1. Open item detail<br>2. Click "Write Review"<br>3. Select star rating (1-5)<br>4. Enter comment<br>5. Submit |
| **Test Data** | Rating: 5 stars, Comment: "Sedap sangat!" |
| **Expected Result** | Review saved, displayed in reviews list, average rating updated |
| **Status** | ☐ Pass / ☐ Fail |

### TC-REV-002: Edit Own Review
| Field | Description |
|-------|-------------|
| **Test Case ID** | TC-REV-002 |
| **Module** | Review |
| **Test Scenario** | Customer edits their existing review |
| **Pre-conditions** | Customer has submitted review for item |
| **Test Steps** | 1. Open item detail<br>2. Find own review with "Edit" button<br>3. Click Edit<br>4. Change rating/comment<br>5. Submit |
| **Expected Result** | Review updated, changes reflected immediately |
| **Status** | ☐ Pass / ☐ Fail |

### TC-REV-003: View Reviews
| Field | Description |
|-------|-------------|
| **Test Case ID** | TC-REV-003 |
| **Module** | Review |
| **Test Scenario** | View all reviews for menu item |
| **Pre-conditions** | Reviews exist for item |
| **Test Steps** | 1. Open item detail<br>2. Scroll to reviews section |
| **Expected Result** | All reviews displayed with reviewer name, date, rating stars, comment |
| **Status** | ☐ Pass / ☐ Fail |

### TC-REV-004: Review Restricted to Purchasers
| Field | Description |
|-------|-------------|
| **Test Case ID** | TC-REV-004 |
| **Module** | Review |
| **Test Scenario** | Customer cannot review item they haven't purchased |
| **Pre-conditions** | Customer logged in, item NOT purchased by this customer |
| **Test Steps** | 1. Open item detail for unpurchased item<br>2. Check review button |
| **Expected Result** | Review button disabled, shows "You can review after purchased" message |
| **Status** | ☐ Pass / ☐ Fail |

### TC-REV-005: Review Enabled After Purchase
| Field | Description |
|-------|-------------|
| **Test Case ID** | TC-REV-005 |
| **Module** | Review |
| **Test Scenario** | Customer can review item after purchasing |
| **Pre-conditions** | Customer has completed order containing the item |
| **Test Steps** | 1. Purchase item and complete payment<br>2. Open item detail<br>3. Check review button |
| **Expected Result** | Review button enabled with "Give a Review" text |
| **Status** | ☐ Pass / ☐ Fail |

---

## Module 7: Coupon System

### TC-COUP-001: Apply Valid Coupon
| Field | Description |
|-------|-------------|
| **Test Case ID** | TC-COUP-001 |
| **Module** | Coupon |
| **Test Scenario** | Customer applies valid discount coupon |
| **Pre-conditions** | Valid coupon exists, items in cart |
| **Test Steps** | 1. Open cart<br>2. Enter coupon code<br>3. Click "Apply" |
| **Test Data** | Coupon: "SAVE10" (10% discount) |
| **Expected Result** | Discount applied, total recalculated, success message |
| **Status** | ☐ Pass / ☐ Fail |

### TC-COUP-002: Apply Invalid/Expired Coupon
| Field | Description |
|-------|-------------|
| **Test Case ID** | TC-COUP-002 |
| **Module** | Coupon |
| **Test Scenario** | Customer applies invalid coupon |
| **Pre-conditions** | Items in cart |
| **Test Steps** | 1. Enter invalid/expired coupon code<br>2. Click "Apply" |
| **Test Data** | Coupon: "INVALIDCODE" |
| **Expected Result** | Error message displayed, no discount applied |
| **Status** | ☐ Pass / ☐ Fail |

---

## Module 8: Daily Specials

### TC-SPEC-001: View Daily Specials Carousel
| Field | Description |
|-------|-------------|
| **Test Case ID** | TC-SPEC-001 |
| **Module** | Daily Specials |
| **Test Scenario** | Customer views daily specials in sidebar |
| **Pre-conditions** | Active daily specials exist |
| **Test Steps** | 1. Navigate to customer menu<br>2. View sidebar carousel |
| **Expected Result** | Carousel displays specials with image, auto-slides |
| **Status** | ☐ Pass / ☐ Fail |

### TC-SPEC-002: View Daily Specials Modal
| Field | Description |
|-------|-------------|
| **Test Case ID** | TC-SPEC-002 |
| **Module** | Daily Specials |
| **Test Scenario** | Customer opens specials detail modal |
| **Pre-conditions** | Active daily specials exist |
| **Test Steps** | 1. Click on daily specials banner<br>2. View modal |
| **Expected Result** | Modal displays all specials with image, name, price, time remaining, quantity selector |
| **Status** | ☐ Pass / ☐ Fail |

### TC-SPEC-003: Add Special to Cart
| Field | Description |
|-------|-------------|
| **Test Case ID** | TC-SPEC-003 |
| **Module** | Daily Specials |
| **Test Scenario** | Customer adds daily special to cart |
| **Pre-conditions** | Daily special active |
| **Test Steps** | 1. Open specials modal<br>2. Select quantity<br>3. Click "Add to Cart" |
| **Expected Result** | Special item added to cart with correct quantity and discounted price |
| **Status** | ☐ Pass / ☐ Fail |

---

## Module 9: User Profile Management

### TC-PROF-001: View Profile
| Field | Description |
|-------|-------------|
| **Test Case ID** | TC-PROF-001 |
| **Module** | Profile |
| **Test Scenario** | Customer views profile information |
| **Pre-conditions** | Customer logged in |
| **Test Steps** | 1. Navigate to customer_profile.html |
| **Expected Result** | Profile displays full name, email, phone number |
| **Status** | ☐ Pass / ☐ Fail |

### TC-PROF-002: Update Profile
| Field | Description |
|-------|-------------|
| **Test Case ID** | TC-PROF-002 |
| **Module** | Profile |
| **Test Scenario** | Customer updates profile information |
| **Pre-conditions** | Customer logged in |
| **Test Steps** | 1. Navigate to profile<br>2. Edit name/phone<br>3. Click "Update" |
| **Test Data** | New Name: "Ahmad Bin Ali", New Phone: "0198765432" |
| **Expected Result** | Profile updated, success message shown |
| **Status** | ☐ Pass / ☐ Fail |

---

## Module 10: Staff Dashboard

### TC-STAFF-001: View Active Orders
| Field | Description |
|-------|-------------|
| **Test Case ID** | TC-STAFF-001 |
| **Module** | Staff Dashboard |
| **Test Scenario** | Staff views pending orders |
| **Pre-conditions** | Staff logged in, pending orders exist |
| **Test Steps** | 1. Navigate to staff_dashboard.html<br>2. View Active Orders section |
| **Expected Result** | All pending orders displayed with customer info, items, total |
| **Status** | ☐ Pass / ☐ Fail |

### TC-STAFF-002: Accept Order
| Field | Description |
|-------|-------------|
| **Test Case ID** | TC-STAFF-002 |
| **Module** | Staff Dashboard |
| **Test Scenario** | Staff accepts pending order |
| **Pre-conditions** | Pending order exists |
| **Test Steps** | 1. Find pending order<br>2. Click "Accept" button |
| **Expected Result** | Order status changes to "Preparing", moved to appropriate section |
| **Status** | ☐ Pass / ☐ Fail |

### TC-STAFF-003: Reject Order
| Field | Description |
|-------|-------------|
| **Test Case ID** | TC-STAFF-003 |
| **Module** | Staff Dashboard |
| **Test Scenario** | Staff rejects order |
| **Pre-conditions** | Pending order exists |
| **Test Steps** | 1. Find pending order<br>2. Click "Reject" button |
| **Expected Result** | Order status changes to "Cancelled" |
| **Status** | ☐ Pass / ☐ Fail |

### TC-STAFF-004: Mark Order Ready
| Field | Description |
|-------|-------------|
| **Test Case ID** | TC-STAFF-004 |
| **Module** | Staff Dashboard |
| **Test Scenario** | Staff marks order as ready for pickup |
| **Pre-conditions** | Order in "Preparing" status |
| **Test Steps** | 1. Find preparing order<br>2. Click "Ready" button |
| **Expected Result** | Order status changes to "Ready for Pickup" |
| **Status** | ☐ Pass / ☐ Fail |

### TC-STAFF-005: Complete Order
| Field | Description |
|-------|-------------|
| **Test Case ID** | TC-STAFF-005 |
| **Module** | Staff Dashboard |
| **Test Scenario** | Staff marks order as finished |
| **Pre-conditions** | Order in "Ready for Pickup" status |
| **Test Steps** | 1. Find ready order<br>2. Click "Finish" button |
| **Expected Result** | Order status changes to "Finished" |
| **Status** | ☐ Pass / ☐ Fail |

---

## Module 11: Admin Dashboard

### TC-ADMIN-001: View Dashboard Overview
| Field | Description |
|-------|-------------|
| **Test Case ID** | TC-ADMIN-001 |
| **Module** | Admin Dashboard |
| **Test Scenario** | Admin views main dashboard |
| **Pre-conditions** | Admin logged in |
| **Test Steps** | 1. Navigate to admin_dashboard.html |
| **Expected Result** | Dashboard displays with sidebar navigation and main content areas |
| **Status** | ☐ Pass / ☐ Fail |

### TC-ADMIN-002: Manage Menu Items (Add)
| Field | Description |
|-------|-------------|
| **Test Case ID** | TC-ADMIN-002 |
| **Module** | Admin Dashboard |
| **Test Scenario** | Admin adds new menu item |
| **Pre-conditions** | Admin logged in, on Menu Management section |
| **Test Steps** | 1. Click "Add Menu Item"<br>2. Fill in name, description, price, category<br>3. Upload image<br>4. Submit |
| **Test Data** | Name: "Ayam Special", Price: 15.90, Category: "Chicken" |
| **Expected Result** | New menu item created, appears in menu list |
| **Status** | ☐ Pass / ☐ Fail |

### TC-ADMIN-003: Manage Menu Items (Edit)
| Field | Description |
|-------|-------------|
| **Test Case ID** | TC-ADMIN-003 |
| **Module** | Admin Dashboard |
| **Test Scenario** | Admin edits existing menu item |
| **Pre-conditions** | Menu item exists |
| **Test Steps** | 1. Find menu item<br>2. Click "Edit"<br>3. Modify fields<br>4. Save |
| **Expected Result** | Menu item updated successfully |
| **Status** | ☐ Pass / ☐ Fail |

### TC-ADMIN-004: Manage Menu Items (Delete)
| Field | Description |
|-------|-------------|
| **Test Case ID** | TC-ADMIN-004 |
| **Module** | Admin Dashboard |
| **Test Scenario** | Admin deletes menu item |
| **Pre-conditions** | Menu item exists |
| **Test Steps** | 1. Find menu item<br>2. Click "Delete"<br>3. Confirm deletion |
| **Expected Result** | Menu item removed from database and list |
| **Status** | ☐ Pass / ☐ Fail |

### TC-ADMIN-005: Manage Users
| Field | Description |
|-------|-------------|
| **Test Case ID** | TC-ADMIN-005 |
| **Module** | Admin Dashboard |
| **Test Scenario** | Admin views and manages user accounts |
| **Pre-conditions** | Users exist in system |
| **Test Steps** | 1. Navigate to User Management section<br>2. View user list |
| **Expected Result** | Users displayed sorted by role (Admin → Staff → Customer), then alphabetically |
| **Status** | ☐ Pass / ☐ Fail |

### TC-ADMIN-006: Manage Daily Specials
| Field | Description |
|-------|-------------|
| **Test Case ID** | TC-ADMIN-006 |
| **Module** | Admin Dashboard |
| **Test Scenario** | Admin creates daily special |
| **Pre-conditions** | Menu items exist |
| **Test Steps** | 1. Navigate to Daily Specials section<br>2. Click "Add Special"<br>3. Select menu item, set discount, set duration<br>4. Save |
| **Expected Result** | Daily special created, visible to customers |
| **Status** | ☐ Pass / ☐ Fail |

### TC-ADMIN-007: Manage Coupons
| Field | Description |
|-------|-------------|
| **Test Case ID** | TC-ADMIN-007 |
| **Module** | Admin Dashboard |
| **Test Scenario** | Admin creates discount coupon |
| **Pre-conditions** | Admin on coupon management section |
| **Test Steps** | 1. Click "Add Coupon"<br>2. Enter code, discount type, value, expiry<br>3. Save |
| **Test Data** | Code: "NEWYEAR25", Discount: 25% |
| **Expected Result** | Coupon created, usable by customers |
| **Status** | ☐ Pass / ☐ Fail |

---

## Module 12: Financial Reports

### TC-FIN-001: View Financial Statistics
| Field | Description |
|-------|-------------|
| **Test Case ID** | TC-FIN-001 |
| **Module** | Financial Report |
| **Test Scenario** | Admin views sales statistics |
| **Pre-conditions** | Orders exist in system |
| **Test Steps** | 1. Navigate to Financial Report section |
| **Expected Result** | Dashboard cards show: Daily Sales, Monthly Sales, Monthly Profit, Monthly Expenses |
| **Status** | ☐ Pass / ☐ Fail |

### TC-FIN-002: View Charts
| Field | Description |
|-------|-------------|
| **Test Case ID** | TC-FIN-002 |
| **Module** | Financial Report |
| **Test Scenario** | Admin views sales charts |
| **Pre-conditions** | Orders exist |
| **Test Steps** | 1. View Financial Report section<br>2. Check charts |
| **Expected Result** | Monthly Performance chart and Top Selling Items chart displayed |
| **Status** | ☐ Pass / ☐ Fail |

### TC-FIN-003: Filter by Date Range
| Field | Description |
|-------|-------------|
| **Test Case ID** | TC-FIN-003 |
| **Module** | Financial Report |
| **Test Scenario** | Admin filters report by date range |
| **Pre-conditions** | On Financial Report section |
| **Test Steps** | 1. Select start date<br>2. Select end date<br>3. Click "Apply Filter" |
| **Test Data** | Start: 2025-12-01, End: 2025-12-24 |
| **Expected Result** | Statistics updated for selected date range |
| **Status** | ☐ Pass / ☐ Fail |

### TC-FIN-004: Download CSV Report
| Field | Description |
|-------|-------------|
| **Test Case ID** | TC-FIN-004 |
| **Module** | Financial Report |
| **Test Scenario** | Admin downloads sales report as CSV |
| **Pre-conditions** | Orders exist in date range |
| **Test Steps** | 1. Set date range<br>2. Click "Download CSV" button |
| **Expected Result** | CSV file downloaded with columns: Order ID, Date, Customer Name, Items Summary, Total Amount, Status |
| **Status** | ☐ Pass / ☐ Fail |

### TC-FIN-005: Download PDF Report
| Field | Description |
|-------|-------------|
| **Test Case ID** | TC-FIN-005 |
| **Module** | Financial Report |
| **Test Scenario** | Admin generates PDF report |
| **Pre-conditions** | Orders exist |
| **Test Steps** | 1. Set date range<br>2. Click "Download PDF" button |
| **Expected Result** | PDF report opens in new tab for printing/saving |
| **Status** | ☐ Pass / ☐ Fail |

---

## Module 13: Session Management

### TC-SESS-001: Session Timeout
| Field | Description |
|-------|-------------|
| **Test Case ID** | TC-SESS-001 |
| **Module** | Session |
| **Test Scenario** | User session expires after inactivity |
| **Pre-conditions** | User logged in |
| **Test Steps** | 1. Login to system<br>2. Leave inactive for 1 hour<br>3. Try to perform action |
| **Expected Result** | User auto-logged out, redirect to login page with "Session expired" message |
| **Status** | ☐ Pass / ☐ Fail |

### TC-SESS-002: Session Activity Reset
| Field | Description |
|-------|-------------|
| **Test Case ID** | TC-SESS-002 |
| **Module** | Session |
| **Test Scenario** | User activity resets session timer |
| **Pre-conditions** | User logged in |
| **Test Steps** | 1. Login to system<br>2. Perform activities (click, scroll, type)<br>3. Continue using system |
| **Expected Result** | Session timer resets with each activity, user stays logged in |
| **Status** | ☐ Pass / ☐ Fail |

---

## Test Summary Table

| Module | Total Test Cases | Pass | Fail | Pending |
|--------|-----------------|------|------|---------|
| User Authentication | 6 | ☐ | ☐ | 6 |
| Customer Menu & Ordering | 4 | ☐ | ☐ | 4 |
| Cart Management | 4 | ☐ | ☐ | 4 |
| Order Processing | 4 | ☐ | ☐ | 4 |
| Payment Integration (ToyyibPay) | 4 | ☐ | ☐ | 4 |
| Review System | 5 | ☐ | ☐ | 5 |
| Coupon System | 2 | ☐ | ☐ | 2 |
| Daily Specials | 3 | ☐ | ☐ | 3 |
| User Profile Management | 2 | ☐ | ☐ | 2 |
| Staff Dashboard | 5 | ☐ | ☐ | 5 |
| Admin Dashboard | 7 | ☐ | ☐ | 7 |
| Financial Reports | 5 | ☐ | ☐ | 5 |
| Session Management | 2 | ☐ | ☐ | 2 |
| **TOTAL** | **53** | ☐ | ☐ | **53** |

---

## Test Environment

| Component | Details |
|-----------|---------|
| **Operating System** | Windows 10/11 |
| **Web Server** | Apache (XAMPP) / Railway (Production) |
| **Database** | MySQL (XAMPP/Railway) |
| **PHP Version** | 8.x |
| **Frontend Hosting** | Vercel |
| **Payment Gateway** | ToyyibPay (Sandbox/Production) |
| **Browser** | Google Chrome (Latest) |
| **Screen Resolution** | 1920x1080 / Mobile Responsive |

---

## Prepared By

| Field | Details |
|-------|---------|
| **Tester Name** | __________________ |
| **Date** | 11 January 2026 |
| **Signature** | __________________ |

---

*Document End*

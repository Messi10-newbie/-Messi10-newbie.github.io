# Campus Bites — Setup Instructions

A PHP + MySQL food pre-ordering app. Follow these steps to run it on your machine.

## What you need
- **XAMPP** (with PHP 8.x and MySQL) — download from https://www.apachefriends.org

## 1. Install & start XAMPP
1. Install XAMPP.
2. Open the **XAMPP Control Panel** and click **Start** for both **Apache** and **MySQL**.

## 2. Copy the project files
1. Copy the `app` folder into `C:\xampp\htdocs\`.
2. (Optional) Rename it to something like `campusbites`, so the path is `C:\xampp\htdocs\campusbites`.

## 3. Create the database
1. Open http://localhost/phpmyadmin in your browser.
2. Click **New** in the left sidebar and create a database named exactly: `food_court`
3. Select the `food_court` database, go to the **Import** tab.
4. Choose the `database.sql` file (included with the project) and click **Import / Go**.

## 4. Run the app
Open in your browser:

    http://localhost/campusbites/

(use whatever folder name you chose in step 2)

If you see a "Database Connection Failed" page, MySQL isn't running or the database wasn't imported — re-check steps 1 and 3.

## 5. Email receipts (optional)
The app emails an order receipt after checkout. This is configured in `app/_base.php` under the "Email (Gmail SMTP)" section.

- To **enable** it: set `MAIL_USER` to a Gmail address and `MAIL_PASS` to a Gmail **App Password** (16 characters, created at https://myaccount.google.com/apppasswords — NOT your normal Gmail password).
- To **disable** it: leave `MAIL_PASS` as an empty string `''`. The app works fine without email.

⚠️ **Note:** many college/office WiFi networks block outgoing email (SMTP ports). If emails don't arrive, test on a mobile hotspot.

## Default configuration (app/_base.php)
| Setting | Default |
|---|---|
| DB host | `localhost` |
| DB name | `food_court` |
| DB user | `root` |
| DB password | *(empty — XAMPP default)* |

If your MySQL has a root password, update `DB_PASS` in `app/_base.php`.

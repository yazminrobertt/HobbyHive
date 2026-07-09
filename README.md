# HobbyHive: After-School Activity Booking System

HobbyHive is a web-based platform that connects parents with coaches offering after-school activities such as sports and performing arts. It centralizes activity discovery, booking, and management, so parents no longer need to search across multiple websites or contact coaches individually just to compare classes.

This project was developed as a Diploma Final Year Project at Universiti Teknikal Malaysia Melaka (UTeM), Faculty of Information and Communication Technology, under the supervision of Ts. Dr. Noraswaliza binti Abdullah.

---

## Problem It Solves

Before HobbyHive, there was no centralized platform for after-school activities, which created several challenges:
- Parents had to visit multiple websites to compare location, schedule, and pricing
- Pricing information often wasn't available without directly contacting coaches
- Parents struggled to discover activities available near them
- Coaches had no centralized way to list services, manage enquiries, or compete with other providers

## Objectives

- Let coaches manage data about their services — activities, location, time, category, and price — and track sales from listed services
- Help parents search, discover, and book activities based on location and category
- Generate visual reports (e.g. pie charts) for coaches showing bookings across their activity categories

## Users

- **Parents** — discover, compare, and book after-school activities for their children
- **Coaches** — list and manage their services, handle bookings, and monitor performance through visual reports

## Modules

- **User Registration and Login** — account creation and authentication for both parents and coaches
- **Manage Activities** *(coach only)* — add, update, and remove listed services
- **Manage Portfolio** *(coach only)* — add and manage achievements, accreditations, and certificates, visible to parents
- **Search Services/Activities** *(parent)* — filter activities by location and category
- **Booking Class** *(parent)* — book monthly, yearly, or trial classes, including child information
- **Confirmation** *(parent)* — review booking details before payment
- **Payment** *(parent)* — deduct class cost from an in-app parent e-wallet, which can be reloaded via card or online banking
- **Service Rating and Review** — parents can rate and review coaches after completing a class
- **Generate Reports** *(coach)* — visual performance reports (e.g. pie charts) based on bookings per activity category
- **Wallet Management** — for both parents (top-ups, payments) and coaches (earnings)

## Tech Stack

| Component | Technology |
|---|---|
| Frontend | HTML, CSS, Bootstrap |
| Backend | PHP |
| Database | MySQL (via phpMyAdmin) |
| Local server environment | XAMPP |
| Development environment | Visual Studio Code |

## Getting Started

### Prerequisites
- [XAMPP](https://www.apachefriends.org/) (Apache + MySQL + PHP)

### Setup

1. Clone the repo into your XAMPP `htdocs` folder:
   ```bash
   cd C:\xampp\htdocs
   git clone https://github.com/yazminrobertt/HobbyHive.git
   ```
2. Start **Apache** and **MySQL** from the XAMPP Control Panel.
3. Open phpMyAdmin (`http://localhost/phpmyadmin`) and import the project's database (see project files for the `.sql` file).
4. Update database connection details in the project's config file if needed (default XAMPP setup uses `root` with no password).
5. Visit the project in your browser:
   ```
   http://localhost/HobbyHive/
   ```

## Acknowledgments

Developed by Nurul Yazmin Binti Ridzwan Robert as a Diploma Final Year Project at the Faculty of Information and Communication Technology, Universiti Teknikal Malaysia Melaka (UTeM), under the supervision of Ts. Dr. Noraswaliza binti Abdullah.

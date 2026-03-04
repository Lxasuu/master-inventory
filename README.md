# Meta Inventory Application

A customized inventory management system built for **Politeknik META Industri**. This application helps track PC conditions, locations, and application status with a premium dashboard and real-time mapping.

## 🚀 Features

- **Dynamic Map Visualization**: Interactive map of Lantai 2 and Lantai 3 showing PC distribution.
- **Master Data Management**: Manage PCs, Locations, Conditions, and System Applications.
- **Standardized Profile Logic**: Centralized user profile and photo management.
- **Role-Based Access**: Secure login for Admin, PIC, and Users.
- **Activity Logs**: Track system changes and updates.

## 🛠️ Tech Stack

- **Backend**: PHP 8.x
- **Database**: MySQL (MariaDB)
- **Frontend**: Morvin Admin Template (Bootstrap 5, SCSS)
- **Visuals**: ApexCharts & HTML5 Canvas

## 📦 Installation

1. **Clone the repository** to your local server (e.g., Laragon or XAMPP).
2. **Setup Database**:
   - Create a database named `meta_inventory_sql`.
   - Import `rebuild_database.sql` to populate the schema and master data.
3. **Configuration**:
   - Update `dist/config/db.php` with your database credentials.
4. **Access**:
   - Default URL: `http://localhost/HTML/dist/`
   - Default Admin: `admin` / `admin123`

---
© 2026 Politeknik META Industri
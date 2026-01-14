# eDoc Modern

A lightweight e-channelling web project (HTML/CSS/JS/PHP) for booking doctor appointments.

## What's included
- Frontend: `index.html`, `login.html`, `register.html`, CSS in `css/style.css`, JS in `js/utils.js`.
- Backend endpoints in `api/` and `auth/` (PHP).
- Admin/doctor/patient dashboards in respective folders.

## Prepare for GitHub
This repo is prepared to be published to GitHub. I added a `docs/` copy of the site so you can enable GitHub Pages from the `docs/` folder.

To create a GitHub repo and push from this machine (replace `<your-repo-url>`):

```powershell
git init
git add .
git commit -m "Initial project import"
git branch -M main
git remote add origin <your-repo-url>
git push -u origin main
```

After pushing, enable GitHub Pages in the repository settings and set the source to the `docs/` folder.

## Notes
- Sensitive files such as credentials should be stored outside the repo and added to `.gitignore`.
- Review `docs/index.html` to confirm paths and images are correct for Pages.

---
Generated automatically to prepare this project for GitHub Pages.
# Edoc Modern - E-Channeling System

> **✅ PROJECT STATUS: COMPLETE AND READY FOR USE**

A modern, premium doctor appointment and e-channeling system with stunning UI built using PHP, MySQL, HTML, CSS, and JavaScript.

## 🎯 What's Included

✅ **5 Completed Pages**:
- Landing page with hero section
- Login/Registration system
- Admin dashboard (full CRUD)
- Doctor dashboard
- Patient dashboard

✅ **14 PHP API Endpoints**:
- Authentication (login, register, logout)
- Admin APIs (stats, doctors, sessions, appointments, patients)
- Doctor APIs (appointments)
- Patient APIs (doctors, sessions, appointments)

✅ **800+ Lines of Premium CSS**:
- Glassmorphism design system
- Smooth animations
- Responsive layouts
- Modern color palette

✅ **5 Database Tables**:
- users, doctors, patients, sessions, appointments
- Fully relational with CASCADE deletes
- Test data included

## 🚀 Quick Start

### Prerequisites
- **XAMPP** (Apache + MySQL)
- **PHP 7.3+**  (included with XAMPP)
- Modern web browser

### Setup (5 Minutes)

1. **Start XAMPP**: Open Control Panel, start Apache and MySQL

2. **Create Database**:
   ```
   Visit: http://localhost/phpmyadmin
   Create database: edoc_modern
   Import: database/schema.sql
   ```

3. **Deploy Files**:
   ```
   Copy edoc-modern folder to: C:\xampp\htdocs\
   ```

4. **Access App**:
   ```
   http://localhost/edoc-modern/
   ```

## 🔑 Demo Credentials

| Role | Email | Password |
|------|-------|----------|
| **Admin** | admin@edoc.com | 123 |
| **Doctor** | doctor@edoc.com | 123 |
| **Patient** | patient@edoc.com | 123 |

## Features

- 🎨 **Premium Modern UI** with glassmorphism and smooth animations
- 👤 **Three User Roles**: Admin, Doctor, and Patient
- 📅 **Session Management**: Doctors can schedule availability
- 🏥 **Appointment Booking**: Patients can browse and book appointments
- 📊 **Dashboard Analytics**: Real-time statistics and insights
- 📱 **Fully Responsive**: Works on all devices

## Tech Stack

- **Frontend**: HTML5, CSS3, Vanilla JavaScript
- **Backend**: PHP 7.3+
- **Database**: MySQL 5.7+
- **Server**: Apache (XAMPP recommended)

## Quick Start

### Prerequisites

- XAMPP (Apache 2.4+ and MySQL 5.7+)
- PHP 7.3 or higher

### Installation

1. **Start XAMPP**
   - Open XAMPP Control Panel
   - Start Apache and MySQL

2. **Setup Database**
   - Visit http://localhost/phpmyadmin
   - Create a new database named `edoc_modern`
   - Import `database/schema.sql`

3. **Copy Files**
   - Copy the `edoc-modern` folder to `C:\xampp\htdocs\`

4. **Access Application**
   - Open browser and visit: http://localhost/edoc-modern/

## Default Credentials

### Admin Dashboard
- Email: admin@edoc.com
- Password: 123

### Doctor Dashboard
- Email: doctor@edoc.com
- Password: 123

### Patient Dashboard
- Email: patient@edoc.com
- Password: 123

## Project Structure

```
edoc-modern/
├── index.html              # Landing page
├── login.html             # Login page
├── register.html          # Registration page
├── admin/                 # Admin dashboard pages
├── doctor/                # Doctor dashboard pages
├── patient/               # Patient dashboard pages
├── api/                   # PHP backend API
├── css/                   # Stylesheets
├── js/                    # JavaScript files
└── database/              # SQL schema and migrations
```

## Features by Role

### Administrator
- Add, edit, and delete doctors
- Schedule doctor sessions
- View all patients
- Manage all appointments

### Doctor
- View appointments
- View scheduled sessions
- View patient details
- Manage account settings

### Patient
- Browse available doctors
- Book appointments online
- View booking history
- Manage account settings

## License

MIT License

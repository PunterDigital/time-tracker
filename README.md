# Time Tracker

Time Tracker is a Laravel-based application designed to help you manage your projects and track time efficiently. It offers an intuitive interface to organize projects, assign tasks, and keep a record of the time spent on different activities.

## Live Server

Try Time Tracker for free on our hosted application! [Click here](https://time-tracker.punterdigital.com/) to access the live server.

## Features

- **Project Management:** Create, edit, and organize projects.
- **Time Tracking:** Start and stop time tracking for specific tasks and projects.
- **Role-based Access Control:** Manage users, roles, and permissions.
- **Reporting:** Get a breakdown of time spent on different projects and tasks.
- **Intuitive UI:** A user-friendly interface that's easy to navigate.

## Installation

Follow these steps to set up the Time Tracker application on your local machine:

### Prerequisites

- PHP >= 8.0
- Composer
- MySQL or another compatible database system

### Step 1: Clone the Repository

Clone the repository:

```bash
git clone https://github.com/PunterDigital/time-tracker
```

### Step 2: Install Dependencies

Navigate to the project directory and install the required dependencies:

```bash
cd time-tracker
composer install
npm install
```

### Step 3: Run the instal script

Run the installation script to setup the application:

```bash
php artisan app:install
```

### Step 4: Start the Development Server

Start the Laravel development server:

```bash
npm run build
php artisan serve
```

The application should now be accessible at \`http://127.0.0.1:8000\`.

## Default Admin Credentials

Login as an administrator using the following credentials:

- **Email:** admin@admin.com
- **Password:** admin

## Contributing

We welcome contributions to Time Tracker! Please see our [CONTRIBUTING.md](CONTRIBUTING.md) file for more information.

## License

Time Tracker is open-source software licensed under the [MIT license](LICENSE.md).

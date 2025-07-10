# AutoAssist

AutoAssist is a web-based platform designed to streamline roadside assistance services. It connects users with mechanics and service providers, allowing for efficient request management, car registration, and administrative oversight.

## Features

- User registration and login
- Car registration and management
- Service request creation and tracking
- Mechanic assignment and navigation
- Admin dashboard for managing users, mechanics, services, and withdrawals

## Project Structure

- `*.html` — Frontend pages for users, admins, and mechanics
- `*.php` — Backend scripts for handling authentication, requests, and admin actions
- `style.css` — Main stylesheet for consistent UI
- `auto_assist_schema.sql` — Database schema

## STEPS

1. **Clone the repository**  
   ```
   git clone <repository-url>
   ```

2. **Set up the database**  
   - Import `auto_assist_schema.sql` into your MySQL server.

3. **Configure XAMPP**  
   - Place the project folder in your XAMPP `htdocs` directory.
   - Start Apache and MySQL from the XAMPP control panel.

4. **Access the application**  
   - Open your browser and navigate to `http://localhost/AutoAssist/home.html` (or the relevant entry point).

## Usage

- **Users:** Sign up, register your car, and request assistance.
- **Mechanics:** View and accept assigned requests, navigate to user locations.
- **Admins:** Manage users, mechanics, services, and withdrawals via the admin dashboard.

## Contributing

Pull requests are welcome. For major changes, please open an issue first to discuss what you would like to change.


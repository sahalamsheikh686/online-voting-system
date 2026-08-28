# Online Voting System

A web-based online voting system for secure elections with email verification.

<p align="center">
  <a href="https://laravel.com" target="_blank">
    <img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo">
  </a>
</p>

## About Project
A Laravel-based Online Voting System for secure and transparent elections.  
This project ensures:
- Election-wise candidate and voter management
- One vote per user with strict validation  
- Real-time results after polls close  
- Secure authentication for Admin, Host, and Voter, with email OTP verification

## Deploying on Railway
This repo includes a Docker-based `railway.json` config, and the app is Docker-ready out of the box.

1. Push the project to GitHub.
2. In Railway, create a new project from this repo (Railway auto-detects the `Dockerfile`).
3. Add a Postgres database to the project (Railway → New → Database → PostgreSQL). Railway injects `DATABASE_URL` into the web service automatically.
4. Set the environment variables below on the web service.
5. The container runs migrations at startup and binds to Railway's `$PORT` automatically.

Set these environment variables on the Railway service:

```env
APP_NAME=Online Voting System
APP_ENV=production
APP_DEBUG=false
APP_KEY=<output of: php artisan key:generate --show>
DB_CONNECTION=pgsql
SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=database
```

`DATABASE_URL` does not need to be set manually — Railway's Postgres plugin injects it automatically, and `config/database.php` reads it out of the box.

To send real emails (OTP verification, rejection notices, password reset), also set:

```env
MAIL_MAILER=smtp
MAIL_HOST=<smtp host>
MAIL_PORT=587
MAIL_USERNAME=<smtp username>
MAIL_PASSWORD=<smtp password>
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=<from address>
```

On first deploy, the startup script creates a default admin if one does not exist:

```text
Contact number: 9800000000
Password: admin12345
```

You can override these with `DEFAULT_ADMIN_CONTACT` and `DEFAULT_ADMIN_PASSWORD`
in Railway's environment variables.

## License
This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

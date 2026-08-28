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

## Deploying on Render
This repo includes a Docker-based `render.yaml` Blueprint for Render.

1. Push the project to GitHub.
2. In Render, create a new Blueprint from the repo.
3. Render creates a web service and Postgres database, then injects `DB_URL`.
4. The container runs migrations at startup and binds to Render's `$PORT`.

For a manual Render web service, use Docker and set these environment variables:

```env
APP_ENV=production
APP_DEBUG=false
DB_CONNECTION=pgsql
DB_URL=<your Render Postgres internal connection string>
SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=database
```

Set `APP_KEY` to the output of:

```bash
php artisan key:generate --show
```

On first deploy, the startup script creates a default admin if one does not exist:

```text
Contact number: 9800000000
Password: admin12345
```

You can override these with `DEFAULT_ADMIN_CONTACT` and `DEFAULT_ADMIN_PASSWORD`
in Render.

## License
This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

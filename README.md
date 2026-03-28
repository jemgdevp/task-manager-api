# Task Manager API Laravel 12

This is a simple Task Manager API built with Laravel 12. It provides endpoints for managing tasks, including creating, updating, deleting, and retrieving tasks. The API also includes user authentication and authorization features.

## API Documentation

- UI docs (local by default): `/docs/api`
- OpenAPI JSON: `/docs/api.json`

## Local setup

### Requirements
- Composer >= 2.9
- Node.js >= 22
- pnpm >= 10

## Environment variables
- Copy `.env.example` to `.env` and update the values as needed.

## Email configuration
- By default, the application uses the `log` mail driver, which logs email content to the application log file. To send real emails, update the `MAIL_MAILER` and related settings in the `.env` file with your email service provider's configuration.
- In my case i used ReSend for testing email sending in development, which provides a local SMTP server and a web interface to view sent emails.
- Example ReSend configuration in `.env`:
```dotenv
MAIL_MAILER=smtp
MAIL_HOST=smtp.resend.dev
MAIL_PORT=587
MAIL_USERNAME=your_resend_username
MAIL_PASSWORD=your_resend_password
MAIL_ENCRYPTION=tls 
```

### Install and run
1. Install dependencies:
	- `composer install`
2. Start development server:
	- `composer dev`

### Build
- `pnpm build`

### Tests
- `php artisan test`    

### Generate OpenAPI file

- Analyze docs generation: `php artisan scramble:analyze`
- Export JSON spec: `php artisan scramble:export --path=storage/api-docs/api-docs.json`

### Docs access in production

- `SCRAMBLE_PUBLIC_DOCS=true` to allow public access to `/docs/api`.
- `SCRAMBLE_PUBLIC_DOCS=false` and `SCRAMBLE_ALLOWED_EMAILS=user@domain.com,admin@domain.com` to restrict access by authenticated user email.

## Docs and API Reference
- [API Reference](https://api.taskmanager.jemg.dev/api/docs)

## License

This software framework is open-sourced software licensed under the [MIT license](LICENSE).

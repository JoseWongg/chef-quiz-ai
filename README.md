# ChefQuizAI — Developer README

AI‑powered web application for **continuous food‑safety training and assessment** of chefs. Trainers generate case‑scenario quizzes with detailed feedback, assign them to chefs, and track results.

> Tech stack: **Symfony 6.4 (PHP)**, **MySQL** with **Doctrine ORM**, **Twig + Bootstrap**, **Symfony UX Importmap** (no Node build required), **GuzzleHTTP**, **OpenAI API (GPT‑4)**, deployed to **Heroku**.

## Features

- **Quiz generation** via OpenAI (case scenario, 4 options, per‑option feedback).  
- **Human‑in‑the‑loop editing & approval** of each question and full quiz.  
- **Assignment** to individual chefs or groups; **deadlines** & pass criteria.  
- **Taking quizzes** with progress indicator and **rich feedback**.  
- **Repository & history** of generated/assigned quizzes.  
- **User management & RBAC**: *Trainer* and *Chef* roles.

## Repository layout (key folders)

```
src/
  Controller/        # Account, Security, GenerateQuizz, QuizRepository, MyQuizzes, Home
  Entity/            # User, Quiz, Question, Option, AssignedQuiz, FoodSafetyBestPractices
  Form/              # Login & Registration forms
  Services/          # OpenAIService, PasswordHasher, AssignedQuizPreviewFormattingService
templates/           # Twig views (base layout, pages, modals)
migrations/          # Doctrine migrations
public/              # front controller (index.php), .htaccess
assets/              # importmap assets (no bundler required)
```

## Prerequisites

- **PHP 8.2+** (CLI) with extensions: `pdo_mysql`, `mbstring`, `intl`, `ctype`, `openssl`, `xml`, `curl`, `json`.  
- **Composer 2.6+**  
- **MySQL 8.x** (or MariaDB 10.6+)  
- **Symfony CLI** (recommended)  
- **OpenAI API key** (Chat Completions)

### System packages (quick install)

**Ubuntu/Debian**
```bash
sudo apt update
sudo apt install php php-cli php-mysql php-intl php-mbstring php-xml php-curl composer mysql-server git
```

**macOS**
```bash
brew install php composer mysql git
xcode-select --install
```

**Windows**
- Install PHP (e.g., via Scoop/Chocolatey or XAMPP), Composer, and MySQL.
- Ensure `php.exe` is on PATH; enable required extensions in `php.ini`.

## Local setup

```bash
git clone <your-fork-or-repo-url>.git
cd ChefQuizAI

# 1) Install PHP deps
composer install

# 2) Configure environment
cp .env .env.local
# Edit .env.local and set (examples below)
# APP_SECRET=change_me_32_chars
# DATABASE_URL="mysql://user:pass@127.0.0.1:3306/chefquizai?serverVersion=8.0"
# OPENAI_API_KEY=sk-...

# 3) Create DB schema
php bin/console doctrine:database:create --if-not-exists
php bin/console doctrine:migrations:migrate -n

# 4) Run the dev server
symfony serve              # or: php -S 127.0.0.1:8000 -t public
```

Visit **http://127.0.0.1:8000**

## Configuration

All configuration is env‑driven via `.env.local` and `config/`:

- `DATABASE_URL` — MySQL DSN
- `OPENAI_API_KEY` — used by `App\Services\OpenAIService`
- `APP_SECRET` — 32‑char secret for cookies/CSRF/password hashing
- Optional: tweak per‑page sizes, defaults, and role/registration rules in controllers/services as needed.

### Roles

The app uses **ROLE_TRAINER** and **ROLE_USER (Chef)**. Registration assigns roles per business rule in the registration flow; adjust this rule in `src/Controller/RegistrationController.php` if your deployment needs a different policy (e.g., trainer whitelist or admin‑only grants).

## Common commands

```bash
# Clear cache
php bin/console cache:clear

# Validate & run migrations
php bin/console doctrine:schema:validate
php bin/console doctrine:migrations:migrate -n

# Reset DB (development only)
php bin/console doctrine:database:drop --force
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate -n

# Create a first trainer (example via raw SQL)
mysql -u user -p chefquizai -e "UPDATE user SET roles='["ROLE_TRAINER"]' WHERE email='trainer@example.com'"
```

## Debugging with VS Code

- Install the **PHP Debug** extension.  
- Create `.vscode/launch.json`:

```json
{
  "version": "0.2.0",
  "configurations": [
    { "name": "Listen for Xdebug", "type": "php", "request": "launch", "port": 9003 }
  ]
}
```

- Run `symfony serve`, set a breakpoint, and start debugging in VS Code.

## Testing

```bash
# PHPUnit
./vendor/bin/phpunit
# or via Symfony bridge
php bin/phpunit
```

Add feature/integration tests under `tests/` (controllers, services, entities).

## Deployment (Heroku example)

1. Provision **JawsDB MySQL** (or another MySQL add‑on).  
2. Set config vars: `APP_SECRET`, `DATABASE_URL`, `OPENAI_API_KEY` (and any others).  
3. Enable automatic deploys from `main`.  
4. Run DB migrations on release: add a release phase or run manually:
   ```bash
   php bin/console doctrine:migrations:migrate -n
   ```

> The app uses **Importmap** assets; no Node build step is required.

## Security notes

- Never commit `.env.local` or real API keys.  
- Rotate any secrets if they were ever exposed.  
- Always enable HTTPS in production.  

## Troubleshooting

- **`SQLSTATE[HY000] [2002]`** → MySQL isn’t reachable. Check `DATABASE_URL`, port, and that the DB is running.
- **500 on quiz generation** → verify `OPENAI_API_KEY`; ensure outbound HTTPS is allowed; check logs.
- **Assets or CSS not loading** → confirm `APP_ENV=dev` locally; ensure `public/` is served; clear cache.
- **Login issues** → check session storage permissions and `APP_SECRET` length (32 chars).

---

## Documentation

Additional project documentation is available in the repository:

- **report.pdf** — *AI-capable web-based Application for the Continuous Food Safety Training and Assessment of Chefs*  
  This document provides an in-depth overview of the project, including: 
  - Methodology, specifications, and design.  
  - Development process (Agile sprints), implementation details, and testing.  
  - Evaluation, conclusions, and references.  

Use this report as a detailed reference alongside the README for setup and usage.


## Authorship & Contact
Developed by **Jose Wong**  
j.wong@mail.com  
https://www.linkedin.com/in/jose-wongg  
https://github.com/JoseWongg  

## License
MIT — see the [LICENSE](LICENSE) file for details.
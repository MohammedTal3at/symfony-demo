
# Symfony Docker Setup

This setup runs a Symfony application with Docker, including PHP, Nginx, MySQL, and phpMyAdmin.

## Requirements

- **Docker** and **Docker Compose** installed.

## Getting Started

### 1. Clone the Repository

```bash
git clone https://github.com/your-repository/symfony-docker.git
cd symfony-docker
```

### 2. Set Up Environment Variables

Create a `.env` file with the following:

```env
APP_PORT=8080
MYSQL_ROOT_PASSWORD=root_password
MYSQL_DATABASE=symfony_db
MYSQL_USER=symfony_user
MYSQL_PASSWORD=symfony_password
PMA_PORT=8081
MYSQL_PORT=3306
```

### 3. Build and Start Containers

Run the following to start the containers:

```bash
docker-compose up -d --build
docker-compose exec php composer install

```

### 4. Migrate Database Migrations

```bash
docker-compose exec php php bin/console doctrine:migrations:migrate
```

### 5. Access the Application

- **Symfony App**: Open `http://localhost:8080` (or your configured `APP_PORT`).
- **phpMyAdmin**: Access at `http://localhost:8081` (or your configured `PMA_PORT`).

### 6. Run Fixtures (Populate Database)

Run the fixtures (for some dummy data for countries table):

```bash
docker-compose exec php php bin/console doctrine:fixtures:load
```

### 7. Access Onboarding Form

Navigate to `http://localhost:8080/onboarding-step-1` to access the onboarding form.

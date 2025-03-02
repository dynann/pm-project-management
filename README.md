# Laravel Docker Setup

## 📌 Project Overview
This project sets up a **Laravel** application using **Docker** with **Nginx**, **PHP-FPM**, and **PostgreSQL**. It includes configurations for both **development** and **production** environments.

---

## 📂 Project Structure
```
PM-project-mangement/
│── .docker/
│   ├── nginx/
│   │   ├── default.conf
│   ├── php/
│   │   ├── Dockerfile
│   │   ├── php.ini
│   ├── postgres/
│   │   ├── Dockerfile
│   │   ├── init.sql
│── .dockerignore
│── .env
│── .env.production
│── .env.development
│── docker-compose.yml
│── docker-compose.override.yml  (for development)
│── docker-compose.prod.yml
│── app/ (Laravel application)
│── database/
│── vendor/
│── storage/
│── public/
│── bootstrap/
│── composer.json
│── package.json
│── artisan
│── server.php
│── README.md
```

---

## 🚀 Getting Started

### 1️⃣ Prerequisites
Ensure you have the following installed on your machine:
- **Docker**: [Install Docker](https://docs.docker.com/get-docker/)
- **Docker Compose**: [Install Docker Compose](https://docs.docker.com/compose/install/)

### 2️⃣ Clone the Repository
```sh
git clone https://github.com/your-repo/laravel-docker.git
cd laravel-docker
```

### 3️⃣ Setup Environment Variables
Copy the environment file and update as needed:
```sh
cp .env.development .env  # For development
cp .env.production .env  # For production
```

### 4️⃣ Build & Run Containers

#### For Development:
```sh
docker-compose up -d
```

#### For Production:
```sh
docker-compose -f docker-compose.yml -f docker-compose.prod.yml up -d
```

### 5️⃣ Install Laravel Dependencies
```sh
docker-compose exec app composer install
```

### 6️⃣ Run Migrations
```sh
docker-compose exec app php artisan migrate
```

### 7️⃣ Generate Application Key
```sh
docker-compose exec app php artisan key:generate
```

### 8️⃣ Access Application
- **App (Nginx)**: [http://localhost:8080](http://localhost:8080)
- **PostgreSQL**: `docker-compose exec postgres psql -U laravel_user -d laravel_db`

---

## 🎯 Useful Commands

| Command | Description |
|---------|-------------|
| `docker-compose up -d` | Start containers in the background |
| `docker-compose down` | Stop and remove containers |
| `docker-compose restart` | Restart all services |
| `docker-compose logs -f` | View real-time logs |
| `docker-compose exec app php artisan migrate` | Run Laravel migrations |
| `docker-compose exec app php artisan tinker` | Open Laravel Tinker (interactive shell) |
| `docker-compose exec postgres psql -U laravel_user -d laravel_db` | Connect to PostgreSQL |

---

## 🛠 Debugging & Troubleshooting
- **Check Logs**: Run `docker-compose logs -f`
- **Restart Containers**: Run `docker-compose restart`
- **Rebuild Containers**: Run `docker-compose up --build`
- **Clear Laravel Cache**:
  ```sh
  docker-compose exec app php artisan cache:clear
  docker-compose exec app php artisan config:clear
  docker-compose exec app php artisan route:clear
  docker-compose exec app php artisan view:clear
  ```

---

## 📜 License
This project is licensed under the MIT License.

---

## 🤝 Contributing
Feel free to contribute by opening an issue or a pull request!

---

## 📧 Contact
For questions or support, contact [yeantouch12345@gmail.com](mailto:yoeurn.yan@realwat.net).


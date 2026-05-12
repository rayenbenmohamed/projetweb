 HR Integration Platform - Backend (Symfony)
## 📌 Project Overview
This project is a comprehensive **Human Resources and Recruitment Management System** built with Symfony 6.4. It serves as the central hub for managing recruitment processes, employee contracts, and internal communication. It is designed to work in tandem with a Java-based desktop application, sharing a unified database and security logic.
## 🚀 Key Features
### 1. Recruitment Management
*   **Job Offers**: Create, update, and manage job listings with detailed requirements.
*   **Applications**: Track candidate applications, CVs, and cover letters.
*   **Interview Scheduling**: Integrated interview management with potential Google Calendar synchronization.
*   **AI-Powered CV Parsing**: Extract data from candidate resumes automatically.
### 2. Contract & Employee Management
*   **Digital Contracts**: Generate and manage employee contracts (CDI, CDD, etc.).
*   **PDF Generation**: Export contracts and offer letters using `Dompdf`.
*   **Enterprise Profiles**: Manage multiple company profiles and their respective branches.
### 3. Communication & Real-time Features
*   **Internal Chat**: Real-time messaging system between users using Symfony Mercure.
*   **Notification System**: In-app notifications for application status updates and interview reminders.
*   **Community Forum**: A platform for employees to share knowledge and discuss topics.
*   **Friendship System**: Manage connections and networking within the platform.
### 4. Advanced Integrations
*   **Cloudinary**: Secure image and document storage for profile pictures and resumes.
*   **Google Auth**: OAuth2 integration for secure login.
*   **Twilio**: (Optional) SMS notifications for urgent alerts.
## 🛠 Tech Stack
*   **Framework**: Symfony 6.4
*   **PHP**: >= 8.1
*   **Database**: MySQL / MariaDB (via Doctrine ORM)
*   **Templating**: Twig
*   **Security**: Symfony Security Bundle with BCrypt hashing (compatible with Java desktop app).
*   **Real-time**: Symfony Mercure
*   **File Management**: Cloudinary API
*   **PDF**: Dompdf & Smalot PDF Parser
## 📦 Installation & Setup
### Prerequisites
*   PHP 8.1 or higher
*   Composer
*   MySQL/MariaDB
*   Symfony CLI (recommended)
### Steps
1.  **Clone the repository**:
    ```bash
    git clone https://github.com/your-username/hr-integration-symfony.git
    cd hr-integration-symfony
    ```
2.  **Install dependencies**:
    ```bash
    composer install
    ```
3.  **Configure environment**:
    Create a `.env.local` file and update your database credentials:
    ```env
    DATABASE_URL="mysql://root:password@127.0.0.1:3306/devjava1?serverVersion=8.0.32&charset=utf8mb4"
    CLOUDINARY_URL=cloudinary://api_key:api_secret@cloud_name
    ```
4.  **Database setup**:
    ```bash
    php bin/console doctrine:database:create
    php bin/console doctrine:migrations:migrate
    ```
5.  **Run the server**:
    ```bash
    symfony serve
    ```
## 🏗 Project Structure
*   `src/Controller`: Handles web requests and API endpoints.
*   `src/Entity`: Doctrine entities representing the database schema.
*   `src/Repository`: Custom database queries.
*   `src/Service`: Business logic (PDF generation, AI parsing, etc.).
*   `templates/`: Twig templates for the frontend interface.
## 🤝 Collaboration
This project is part of a larger ecosystem including a **JavaFX Desktop Application**. Both applications share the `devjava1` database to ensure data consistency across platforms.
---
*Created with ❤️ for the Integration Project.*

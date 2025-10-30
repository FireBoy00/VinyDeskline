# VinyDeskline

## Setup Instructions

After cloning the repository, follow these steps to set up the project:

1. Install Node.js dependencies:
    ```bash
    npm install
    ```

2. Install PHP dependencies:
    ```bash
    composer install
    ```

3. Copy the example environment file:
    ```bash
    cp .env.example .env
    ```

4. Generate the application key:
    ```bash
    php artisan key:generate
    ```

5. Run database migrations (press Enter for default "yes" when prompted):
    ```bash
    php artisan migrate
    ```
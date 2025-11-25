#!/bin/bash

# Start Laravel development server with custom PHP configuration for video uploads
echo "Starting Laravel development server with custom PHP configuration..."
echo "Upload limits: 100MB files, 120MB POST data"
echo "Server will be available at: http://127.0.0.1:8000"
echo ""

# Check if the custom php.ini exists
if [ ! -f "php-dev.ini" ]; then
    echo "Error: php-dev.ini not found in current directory"
    exit 1
fi

# Start the server with custom configuration
php -c php-dev.ini artisan serve --host=127.0.0.1 --port=8000
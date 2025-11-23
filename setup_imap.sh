#!/bin/bash

# IMAP Password Reset Email Retrieval Setup Script

echo "Setting up IMAP Password Reset Email Retrieval System..."

# Check if composer is installed
if ! command -v composer &> /dev/null
then
    echo "Composer is not installed. Please install Composer first."
    exit 1
fi

# Install the required package
echo "Installing webklex/laravel-imap package..."
composer require webklex/laravel-imap

# Copy the configuration file
echo "Copying IMAP configuration file..."
cp vendor/webklex/laravel-imap/src/config/imap.php config/

# Add IMAP routes to web.php
echo "Adding IMAP routes to routes/web.php..."
cat >> routes/web.php << 'EOL'

// IMAP Email Checking Routes (Admin only)
Route::middleware(['auth', 'role:administrateur'])->prefix('imap')->name('imap.')->group(function () {
    Route::get('/check-password-resets', [\App\Http\Controllers\IMAPController::class, 'showForm'])->name('check-password-resets-form');
    Route::post('/check-password-resets', [\App\Http\Controllers\IMAPController::class, 'checkPasswordResetEmails'])->name('check-password-resets');
});
EOL

# Add scheduled task to Kernel.php
echo "Adding scheduled task to app/Console/Kernel.php..."
sed -i '' '/protected function schedule(Schedule $schedule)/,/appendOutputTo(storage_path/a\
        // Check for password reset emails every 5 minutes\
        $schedule->command('\''imap:check-password-resets --notify'\'')\
                 ->everyFiveMinutes()\
                 ->withoutOverlapping()\
                 ->appendOutputTo(storage_path('\''logs/password-reset-checks.log'\''));' app/Console/Kernel.php

echo "Creating log directory if it doesn't exist..."
mkdir -p storage/logs

echo "Setup complete!"
echo ""
echo "Next steps:"
echo "1. Update your .env file with your Gmail credentials:"
echo "   IMAP_HOST=imap.gmail.com"
echo "   IMAP_PORT=993"
echo "   IMAP_ENCRYPTION=ssl"
echo "   IMAP_VALIDATE_CERT=true"
echo "   IMAP_USERNAME=your-email@gmail.com"
echo "   IMAP_PASSWORD=your-app-password"
echo ""
echo "2. Follow the instructions in IMAP_SETUP.md for detailed configuration"
echo "3. Run 'php artisan config:cache' to cache the new configuration"
echo "4. Access the web interface at /imap/check-password-resets"
<?php
require 'vendor/autoload.php'; // For MongoDB library if using Composer
$config = parse_ini_file('/var/www/private/db-config.ini');
$uri = $config['mongodb_uri'];
$client = new MongoDB\Client($uri);

echo "Connected to MongoDB!";
?>
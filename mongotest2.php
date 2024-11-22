<?php
require 'vendor/autoload.php'; // For MongoDB library if using Composer

$client = new MongoDB\Client("mongodb+srv://2301823:UsagiParkdecagram4@cluster0.ugdqd.mongodb.net/?retryWrites=true&w=majority&appName=Cluster0");

echo "Connected to MongoDB!";
?>
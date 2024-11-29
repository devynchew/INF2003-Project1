<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require 'vendor/autoload.php'; // Include Composer autoloader for MongoDB

use MongoDB\Client;
use Exception;
use MongoDB\Driver\ServerApi;
$config = parse_ini_file('/var/www/private/db-config.ini');
$uri = $config['mongodb_uri'];
$client = new Client($uri);

// Access the database and collection
$db = $client->selectDatabase('somethingqlo'); // Replace with your database name
$productsCollection = $db->selectCollection('products');

$products = [
    [
        'product_id' => 1,
        'name' => 'AIRism Cotton Crew Neck Short Sleeve T-shirt',
        'description' => 'test',
        'category' => ['name' => 'Top', 'description' => 'T-shirt, shirts, polo shirts'],
        'gender' => 'Male',
        'image_url' => 'https://example.com/images/men/airism_crew_neck_tshirt.png',
        'price' => 14.90,
        'colors' => ['Red', 'Blue'],
        'sizes' => ['S', 'M', 'L']
    ],
    [
        'product_id' => 2,
        'name' => 'Dry Sweat Jacket',
        'description' => '',
        'category' => ['name' => 'Outerwear', 'description' => 'Jackets, hoodies'],
        'gender' => 'Unisex',
        'image_url' => 'https://example.com/images/unisex/dry_sweat_jacket.png',
        'price' => 49.90,
        'colors' => ['Red', 'Blue'],
        'sizes' => ['S', 'M', 'L']
    ],
    [
        'product_id' => 3,
        'name' => 'Ultra Stretch Jeans',
        'description' => 'NULL',
        'category' => ['name' => 'Bottom', 'description' => 'Jeans, loungewear, shorts'],
        'gender' => 'Male',
        'image_url' => 'https://example.com/images/men/ultra_stretch_jeans.png',
        'price' => 59.90,
        'colors' => ['Red', 'Blue'],
        'sizes' => ['S', 'M', 'L']
    ],
    [
        'product_id' => 4,
        'name' => 'Stretch Slim Fit Shorts',
        'description' => 'NULL',
        'category' => ['name' => 'Bottom', 'description' => 'Jeans, loungewear, shorts'],
        'gender' => 'Male',
        'image_url' => 'https://example.com/images/men/stretch_slim_fit_shorts.png',
        'price' => 29.90,
        'colors' => ['Red', 'Blue'],
        'sizes' => ['S', 'M', 'L']
    ],
    [
        'product_id' => 5,
        'name' => 'Casual Slim Shirt',
        'description' => 'NULL',
        'category' => ['name' => 'Top', 'description' => 'T-shirt, shirts, polo shirts'],
        'gender' => 'Female',
        'image_url' => 'https://example.com/images/women/casual_slim_shirt.png',
        'price' => 10.90,
        'colors' => ['Red', 'Blue'],
        'sizes' => ['S', 'M', 'L']
    ],
    [
        'product_id' => 6,
        'name' => 'Warhol Sweatpants',
        'description' => 'Comfortable, elastic waistband.',
        'category' => ['name' => 'Bottom', 'description' => 'Jeans, loungewear, shorts'],
        'gender' => 'Unisex',
        'image_url' => 'https://example.com/images/unisex/warhol_sweatpants.png',
        'price' => 25.00,
        'colors' => ['Black', 'Gray'],
        'sizes' => ['M', 'L', 'XL']
    ],
        [
        "product_id" => 7, 
        "name" => "Dry Pique Striped Polo Shirt",
        "description" => "''",
        "category" => ['name' => 'Top', 'description'=> 'T-shirt'],
        "gender" => "Male",
        "image_url" => "https://example.com/images/men/dry_piqued_striped_polo_shirt.png",
        "price" => 29.90,
        "colors" => ['Blue', 'Red', 'Black', 'Purple', 'Maroon', 'White', 'Green', 'Brown'],
        "sizes" => ['S', 'M', 'L', 'XL']
    ],
    [
        "product_id" => 8, 
        "name" => "Fleece Full-Zip Jacket",
        "description" => "''",
        "category" => ['name' => 'Outerwear', 'description' => 'Jackets'],
        "gender" => "Unisex",
        "image_url" => "https://example.com/images/unisex/fleece_full_zip_jacket.png",
        "price" => 39.90,
        "colors" => ['Blue', 'Red', 'Black', 'Purple', 'Maroon', 'White', 'Green', 'Brown'],
        "sizes" => ['S', 'M', 'L', 'XL']
    ],
    [
        "product_id" => 9, 
        "name" => "Wide Straight Jeans",
        "description" => "''",
        "category" => ['name'=> 'Bottom', 'description'=> 'Jeans'],
        "gender" => "Female",
        "image_url" => "https://example.com/images/women/wide_straight_jeans.png",
        "price" => 21.90,
        "colors" => ['Blue', 'Red', 'Black', 'Purple', 'Maroon', 'White', 'Green', 'Brown'],
        "sizes" => ['S', 'M', 'L', 'XL']
    ],
    [
        "product_id" => 10, 
        "name" => "Cotton Easy Shorts (Striped)",
        "description" => "''",
        "category" => ['name'=> 'Bottom', 'description'=> 'Jeans'],
        "gender" => "Female",
        "image_url" => "https://example.com/images/women/cotton_easy_shorts.png",
        "price" => 17.90,
        "colors" => ['Blue', 'Red', 'Black', 'Purple', 'Maroon', 'White', 'Green', 'Brown'],
        "sizes" => ['S', 'M', 'L', 'XL']
    ],
    [
        "product_id" => 11, 
        "name" => "Smart Ankle Pants",
        "description" => "''",
        "category" => ['name'=> 'Bottom', 'description'=> 'Jeans'],
        "gender" => "Female",
        "image_url" => "https://example.com/images/women/smart_ankle_pants.png",
        "price" => 39.90,
        "colors" => ['Blue', 'Red', 'Black', 'Purple', 'Maroon', 'White', 'Green', 'Brown'],
        "sizes" => ['S', 'M', 'L', 'XL']
    ],
    [
        "product_id" => 12, 
        "name" => "Soft Knitted Fleece Ribbed High Neck T-Shirt",
        "description" => "''",
        "category" => ['name'=> 'Top', 'description'=> 'T-shirt'],
        "gender" => "Female",
        "image_url" => "https://example.com/images/women/ribbed_neck_tshirt.png",
        "price" => 29.90,
        "colors" => ['Blue', 'Red', 'Black', 'Purple', 'Maroon', 'White', 'Green', 'Brown'],
        "sizes" => ['S', 'M', 'L', 'XL']
    ],
    [
        "product_id" => 13, 
        "name" => "Pointelle Henley Neck T-Shirt",
        "description" => "''",
        "category" => ['name'=> 'Top', 'description'=> 'T-shirt'],
        "gender" => "Female",
        "image_url" => "https://example.com/images/women/neck_tshirt.png",
        "price" => 19.90,
        "colors" => ['Blue', 'Red', 'Black', 'Purple', 'Maroon', 'White', 'Green', 'Brown'],
        "sizes" => ['S', 'M', 'L', 'XL']
    ],
];

// Insert each user into the MongoDB collection
foreach ($products as $product) {
    $productsCollection->insertOne($product);
}

echo "product have been successfully inserted into the MongoDB collection!";
?>
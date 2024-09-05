<?php

// Dummy data
$products = [
    ['id' => 1, 'name' => 'Product 1', 'price' => 10],
    ['id' => 2, 'name' => 'Product 2', 'price' => 20],
    ['id' => 3, 'name' => 'Product 3', 'price' => 30],
];

// Function to return all products
function getAllProducts() {
    global $products;
    return $products;
}

// Function to return a specific product by ID
function getProductById($id) {
    global $products;
    foreach ($products as $product) {
        if ($product['id'] == $id) {
            return $product;
        }
    }
    return null;
}

// Check request method
$method = $_SERVER['REQUEST_METHOD'];

// Handle GET requests
if ($method === 'GET') {
    // Check if specific product ID is requested
    if (isset($_GET['id'])) {
        $productId = $_GET['id'];
        $product = getProductById($productId);
        if ($product) {
            echo json_encode($product);
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'Product not found']);
        }
    } else {
        // Return all products if no specific ID is requested
        echo json_encode(getAllProducts());
    }
} else {
    // Handle unsupported methods
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
}

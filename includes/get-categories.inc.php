<?php
// includes/get-categories.inc.php
session_start();
require_once 'dbh.inc.php';
require_once 'category-functions.inc.php';

if (!isset($_SESSION["userid"]) || !in_array($_SESSION["role"], ["admin", "editor"])) {
    http_response_code(403);
    exit(json_encode(['error' => 'Unauthorized']));
}

header('Content-Type: application/json');

// If ID is provided, get single category
if (isset($_GET['id'])) {
    $categoryId = $_GET['id'];
    $stmt = $pdo->prepare("SELECT * FROM Categories WHERE CategoryID = ?");
    $stmt->execute([$categoryId]);
    $category = $stmt->fetch();
    
    if ($category) {
        echo json_encode($category);
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'Category not found']);
    }
} else {
    // Otherwise get all categories
    echo json_encode(getAllCategories($pdo));
}
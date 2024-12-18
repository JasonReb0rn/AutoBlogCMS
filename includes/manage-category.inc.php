<?php
// includes/manage-category.inc.php
session_start();
require_once 'dbh.inc.php';
require_once 'category-functions.inc.php';

if (!isset($_SESSION["userid"]) || !in_array($_SESSION["role"], ["admin", "editor"])) {
    http_response_code(403);
    exit(json_encode(['error' => 'Unauthorized']));
}

header('Content-Type: application/json');

$action = $_POST['action'] ?? '';
$response = ['success' => false, 'error' => 'Invalid action'];

switch ($action) {
    case 'create':
        $name = $_POST['name'] ?? '';
        $slug = $_POST['slug'] ?? '';
        $response = createCategory($pdo, $name, $slug);
        break;
        
    case 'update':
        $id = $_POST['categoryId'] ?? '';
        $name = $_POST['name'] ?? '';
        $slug = $_POST['slug'] ?? '';
        $response = updateCategory($pdo, $id, $name, $slug);
        break;
        
    case 'delete':
        $id = $_POST['categoryId'] ?? '';
        $response = deleteCategory($pdo, $id);
        break;
}

echo json_encode($response);
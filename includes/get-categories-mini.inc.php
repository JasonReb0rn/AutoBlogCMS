<?php
session_start();
require_once 'dbh.inc.php';

if (!isset($_SESSION["userid"]) || !in_array($_SESSION["role"], ["admin", "editor"])) {
    http_response_code(403);
    exit();
}

try {
    $stmt = $pdo->query("SELECT CategoryID, Name, Slug, CreatedAt FROM Categories ORDER BY CreatedAt DESC LIMIT 10");
    $categories = $stmt->fetchAll();
    
    header('Content-Type: application/json');
    echo json_encode($categories);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error']);
}
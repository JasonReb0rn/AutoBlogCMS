<?php
// post.php

// Define root path if not already defined
if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', __DIR__ . '/');
}

require_once 'includes/dbh.inc.php';
require_once 'includes/cache-manager.inc.php';

// Set session cookie parameters to make it accessible across all directories
session_set_cookie_params(0, '/');
session_start();

$postId = $_GET['id'] ?? null;
if (!$postId) {
    header('Location: /');
    exit;
}

$cache = new CacheManager();

// Try to get from cache first
$cachedContent = $cache->get($postId);
if ($cachedContent !== null) {
    // Execute PHP code in cached content
    eval('?>' . $cachedContent);
    exit;
}

// If not in cache, get from database
try {
    // Get post data
    $stmt = $pdo->prepare("
        SELECT p.*, u.Username as AuthorName
        FROM Posts p
        JOIN Users u ON p.UserID = u.UserID
        WHERE p.PostID = ? AND p.Status = 'published'
    ");
    $stmt->execute([$postId]);
    $post = $stmt->fetch();
    
    if (!$post) {
        header('HTTP/1.0 404 Not Found');
        exit('Post not found');
    }
    
    // Get categories
    $stmt = $pdo->prepare("
        SELECT c.*
        FROM Categories c
        JOIN PostCategories pc ON c.CategoryID = pc.CategoryID
        WHERE pc.PostID = ?
    ");
    $stmt->execute([$postId]);
    $categories = $stmt->fetchAll();
    
    // Get tags
    $stmt = $pdo->prepare("
        SELECT t.*
        FROM Tags t
        JOIN PostTags pt ON t.TagID = pt.TagID
        WHERE pt.PostID = ?
    ");
    $stmt->execute([$postId]);
    $tags = $stmt->fetchAll();
    
    // Start output buffering
    ob_start();
    
    // Include template
    require 'templates/article.php';
    
    // Get rendered content
    $content = ob_get_clean();
    
    // Save to cache
    $cache->set($postId, $content);
    
    // Output the content
    echo $content;
    
} catch (PDOException $e) {
    error_log("Error displaying post: " . $e->getMessage());
    header('HTTP/1.0 500 Internal Server Error');
    exit('An error occurred');
}
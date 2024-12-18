<?php
// includes/manage-post.inc.php
session_start();
require_once 'dbh.inc.php';
require_once 'cache-manager.inc.php';

if (!isset($_SESSION["userid"]) || !in_array($_SESSION["role"], ["admin", "editor"])) {
    http_response_code(403);
    exit(json_encode(['error' => 'Unauthorized']));
}

header('Content-Type: application/json');

try {
    $pdo->beginTransaction();
    
    // Insert the post
    $stmt = $pdo->prepare("
        INSERT INTO Posts (
            UserID, 
            Title, 
            Content, 
            FeaturedImage, 
            FeaturedImageCaption,
            Status
        )
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    
    $stmt->execute([
        $_SESSION["userid"],
        $_POST["title"],
        $_POST["content"],
        $_POST["featuredImage"] ?? null,
        $_POST["featuredImageCaption"] ?? null,
        $_POST["status"]
    ]);
    
    $postId = $pdo->lastInsertId();
    
    // Handle categories
    if (!empty($_POST["category"])) {
        $stmt = $pdo->prepare("
            INSERT INTO PostCategories (PostID, CategoryID)
            VALUES (?, ?)
        ");
        $stmt->execute([$postId, $_POST["category"]]);
    }
    
    // Handle tags
    if (!empty($_POST["tags"])) {
        $tags = json_decode($_POST["tags"]);
        
        foreach ($tags as $tagName) {
            // Try to find existing tag
            $stmt = $pdo->prepare("
                SELECT TagID FROM Tags 
                WHERE Name = ?
            ");
            $stmt->execute([$tagName]);
            $tag = $stmt->fetch();
            
            if (!$tag) {
                // Create new tag
                $stmt = $pdo->prepare("
                    INSERT INTO Tags (Name, Slug)
                    VALUES (?, ?)
                ");
                $stmt->execute([
                    $tagName,
                    createSlug($tagName)
                ]);
                $tagId = $pdo->lastInsertId();
            } else {
                $tagId = $tag['TagID'];
            }
            
            // Create post-tag relationship
            $stmt = $pdo->prepare("
                INSERT INTO PostTags (PostID, TagID)
                VALUES (?, ?)
            ");
            $stmt->execute([$postId, $tagId]);
        }
    }
    
    $pdo->commit();

    // Invalidate cache for this new post
    $cache = new CacheManager();
    $cache->invalidate($postId);

    echo json_encode(['success' => true, 'postId' => $postId]);
    
} catch (PDOException $e) {
    $pdo->rollBack();
    error_log("Error creating post: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Database error occurred']);
}

// Helper function to create URL-friendly slugs
function createSlug($text) {
    $text = strtolower($text);
    $text = preg_replace('/[^a-z0-9-]/', '-', $text);
    $text = preg_replace('/-+/', '-', $text);
    return trim($text, '-');
}
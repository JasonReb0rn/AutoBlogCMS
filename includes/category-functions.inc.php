<?php

function getAllCategories($pdo) {
    try {
        $stmt = $pdo->query("SELECT * FROM Categories ORDER BY Name ASC");
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Error fetching categories: " . $e->getMessage());
        return [];
    }
}

function createCategory($pdo, $name, $slug = '') {
    try {
        // Generate slug if not provided
        if (empty($slug)) {
            $slug = createSlug($name);
        }
        
        // Validate slug format
        $slug = validateSlug($slug);
        
        $stmt = $pdo->prepare("INSERT INTO Categories (Name, Slug) VALUES (?, ?)");
        $stmt->execute([$name, $slug]);
        return ['success' => true, 'id' => $pdo->lastInsertId()];
    } catch (PDOException $e) {
        error_log("Error creating category: " . $e->getMessage());
        return ['success' => false, 'error' => 'Failed to create category'];
    }
}

function updateCategory($pdo, $id, $name, $slug = '') {
    try {
        // Generate slug if not provided
        if (empty($slug)) {
            $slug = createSlug($name);
        }
        
        // Validate slug format
        $slug = validateSlug($slug);
        
        $stmt = $pdo->prepare("UPDATE Categories SET Name = ?, Slug = ? WHERE CategoryID = ?");
        $stmt->execute([$name, $slug, $id]);
        return ['success' => true];
    } catch (PDOException $e) {
        error_log("Error updating category: " . $e->getMessage());
        return ['success' => false, 'error' => 'Failed to update category'];
    }
}

function deleteCategory($pdo, $id) {
    try {
        $stmt = $pdo->prepare("DELETE FROM Categories WHERE CategoryID = ?");
        $stmt->execute([$id]);
        return ['success' => true];
    } catch (PDOException $e) {
        error_log("Error deleting category: " . $e->getMessage());
        return ['success' => false, 'error' => 'Failed to delete category'];
    }
}

function createSlug($text) {
    // Convert to lowercase
    $text = strtolower($text);
    // Replace spaces and other characters with hyphens
    $text = preg_replace('/[^a-z0-9-_]/', '-', $text);
    // Remove multiple consecutive hyphens
    $text = preg_replace('/-+/', '-', $text);
    // Remove leading and trailing hyphens
    $text = trim($text, '-');
    return $text;
}

function validateSlug($slug) {
    // Remove any characters that aren't a-z, 0-9, hyphen, or underscore
    $slug = preg_replace('/[^a-z0-9-_]/', '', strtolower($slug));
    return $slug;
}

function getCategoriesWithPostCount($pdo) {
    try {
        $stmt = $pdo->query("
            SELECT 
                c.*,
                COUNT(DISTINCT p.PostID) as post_count
            FROM Categories c
            LEFT JOIN PostCategories pc ON c.CategoryID = pc.CategoryID
            LEFT JOIN Posts p ON pc.PostID = p.PostID AND p.Status = 'published'
            GROUP BY c.CategoryID
            ORDER BY c.Name ASC
        ");
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Error fetching categories with post count: " . $e->getMessage());
        return [];
    }
}
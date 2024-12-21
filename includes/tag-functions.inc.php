<?php

function getAllTags($pdo) {
    try {
        $stmt = $pdo->query("SELECT * FROM Tags ORDER BY Name ASC");
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Error fetching tags: " . $e->getMessage());
        return [];
    }
}

function getTagsWithPostCount($pdo) {
    try {
        $stmt = $pdo->query("
            SELECT 
                t.*,
                COUNT(DISTINCT p.PostID) as post_count
            FROM Tags t
            LEFT JOIN PostTags pt ON t.TagID = pt.TagID
            LEFT JOIN Posts p ON pt.PostID = p.PostID AND p.Status = 'published'
            GROUP BY t.TagID
            ORDER BY t.Name ASC
        ");
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Error fetching tags with post count: " . $e->getMessage());
        return [];
    }
}

function createTag($pdo, $name, $slug = '') {
    try {
        // Generate slug if not provided
        if (empty($slug)) {
            $slug = createSlug($name);
        }
        
        // Validate slug format
        $slug = validateSlug($slug);
        
        $stmt = $pdo->prepare("INSERT INTO Tags (Name, Slug) VALUES (?, ?)");
        $stmt->execute([$name, $slug]);
        return ['success' => true, 'id' => $pdo->lastInsertId()];
    } catch (PDOException $e) {
        error_log("Error creating tag: " . $e->getMessage());
        return ['success' => false, 'error' => 'Failed to create tag'];
    }
}

function updateTag($pdo, $id, $name, $slug = '') {
    try {
        // Generate slug if not provided
        if (empty($slug)) {
            $slug = createSlug($name);
        }
        
        // Validate slug format
        $slug = validateSlug($slug);
        
        $stmt = $pdo->prepare("UPDATE Tags SET Name = ?, Slug = ? WHERE TagID = ?");
        $stmt->execute([$name, $slug, $id]);
        return ['success' => true];
    } catch (PDOException $e) {
        error_log("Error updating tag: " . $e->getMessage());
        return ['success' => false, 'error' => 'Failed to update tag'];
    }
}

function deleteTag($pdo, $id) {
    try {
        $stmt = $pdo->prepare("DELETE FROM Tags WHERE TagID = ?");
        $stmt->execute([$id]);
        return ['success' => true];
    } catch (PDOException $e) {
        error_log("Error deleting tag: " . $e->getMessage());
        return ['success' => false, 'error' => 'Failed to delete tag'];
    }
}

// Note: createSlug and validateSlug functions can be shared with category-functions.inc.php
// Consider moving these to a separate utilities file if they're used in multiple places
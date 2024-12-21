<?php
function getSearchResults($pdo, $searchTerm, $page = 1, $postsPerPage = 10) {
    $offset = ($page - 1) * $postsPerPage;
    
    try {
        // Simple search query
        $stmt = $pdo->prepare("
            SELECT 
                p.PostID,
                p.Title,
                p.CreatedAt,
                p.Content,
                p.FeaturedImage,
                p.FeaturedImageCaption,
                u.Username as AuthorName,
                GROUP_CONCAT(DISTINCT c.Name) as Categories
            FROM Posts p
            LEFT JOIN Users u ON p.UserID = u.UserID
            LEFT JOIN PostCategories pc ON p.PostID = pc.PostID
            LEFT JOIN Categories c ON pc.CategoryID = c.CategoryID
            WHERE p.Status = 'published' 
            AND (p.Title LIKE ? OR p.Content LIKE ?)
            GROUP BY p.PostID
            ORDER BY p.CreatedAt DESC
            LIMIT ?, ?
        ");
        
        $searchPattern = "%$searchTerm%";
        $stmt->execute([$searchPattern, $searchPattern, $offset, $postsPerPage]);
        $results = $stmt->fetchAll();
        
        // Count total for pagination
        $countStmt = $pdo->prepare("
            SELECT COUNT(DISTINCT p.PostID) 
            FROM Posts p
            WHERE p.Status = 'published' 
            AND (p.Title LIKE ? OR p.Content LIKE ?)
        ");
        $countStmt->execute([$searchPattern, $searchPattern]);
        $totalPosts = $countStmt->fetchColumn();
        
        return [
            'posts' => $results,
            'total' => $totalPosts,
            'pages' => ceil($totalPosts / $postsPerPage)
        ];
    } catch (PDOException $e) {
        error_log("Search error: " . $e->getMessage());
        return ['posts' => [], 'total' => 0, 'pages' => 0];
    }
}
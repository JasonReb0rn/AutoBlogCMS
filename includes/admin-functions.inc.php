<?php
function getDashboardStats($pdo) {
    try {
        $stats = [];
        
        // Total Posts
        $stmt = $pdo->query("SELECT COUNT(*) FROM Posts");
        $stats['totalPosts'] = $stmt->fetchColumn();
        
        // Total Users
        $stmt = $pdo->query("SELECT COUNT(*) FROM Users");
        $stats['totalUsers'] = $stmt->fetchColumn();
        
        // Total Comments
        $stmt = $pdo->query("SELECT COUNT(*) FROM Comments");
        $stats['totalComments'] = $stmt->fetchColumn();
        
        // Pending Comments
        $stmt = $pdo->query("SELECT COUNT(*) FROM Comments WHERE ModerationStatus = 'pending'");
        $stats['pendingComments'] = $stmt->fetchColumn();
        
        return $stats;
    } catch (PDOException $e) {
        error_log("Error fetching dashboard stats: " . $e->getMessage());
        return [
            'totalPosts' => 0,
            'totalUsers' => 0,
            'totalComments' => 0,
            'pendingComments' => 0
        ];
    }
}
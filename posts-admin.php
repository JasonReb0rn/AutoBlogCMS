<?php
session_start();
require_once 'includes/dbh.inc.php';

if (!isset($_SESSION["userid"]) || !in_array($_SESSION["role"], ["admin", "editor"])) {
    header("location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Post Management - Blog CMS</title>
    <link rel="stylesheet" href="css/admin-style.css">
    <link rel="stylesheet" href="/style.css">
</head>
<body>
    <div class="admin-container">
        <?php include 'admin-header.php'; ?>
        
        <main class="admin-main">
            <section class="dashboard-section">
                <h2>Post Management</h2>
                <div class="action-bar">
                    <button onclick="window.location.href='create-post.php'" class="btn btn-primary">Create New Post</button>
                </div>
                <div class="table-responsive">
                    <table id="postsTable">
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Author</th>
                                <th>Status</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Populated via AJAX -->
                        </tbody>
                    </table>
                </div>
                <div class="pagination">
                    <button id="prevPage" class="btn">Previous</button>
                    <span id="pageInfo">Page 1</span>
                    <button id="nextPage" class="btn">Next</button>
                </div>
            </section>
        </main>
    </div>

    <script src="js/post-management.js"></script>
</body>
</html>
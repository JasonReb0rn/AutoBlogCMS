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
    <title>Category Management - Blog CMS</title>
    <link rel="stylesheet" href="css/admin-style.css">
    <link rel="stylesheet" href="/style.css">
</head>
<body>
    <div class="admin-container">
        <?php include 'admin-header.php'; ?>
        
        <main class="admin-main">
            <section class="dashboard-section">
                <h2>Category Management</h2>
                <div class="action-bar">
                    <button onclick="showCategoryModal()" class="btn btn-primary">Add New Category</button>
                </div>
                <div class="table-responsive">
                    <table id="categoriesTable">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Slug</th>
                                <th>Created At</th>
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

    <!-- Category Modal -->
    <div id="categoryModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2>Category Details</h2>
            <form id="categoryForm">
                <input type="hidden" id="categoryId" name="categoryId">
                <div class="form-group">
                    <label for="categoryName">Name</label>
                    <input type="text" id="categoryName" name="name" required maxlength="50">
                    <small class="form-text">Maximum 50 characters allowed</small>
                </div>
                <div class="form-group">
                    <label for="categorySlug">Slug (optional)</label>
                    <input type="text" id="categorySlug" name="slug" pattern="[a-z0-9_\-]*">
                    <small>Leave blank to auto-generate from name. Only lowercase letters, numbers, hyphens, and underscores allowed.</small>
                </div>
                <button type="submit" class="btn btn-primary">Save Category</button>
            </form>
        </div>
    </div>

    <script src="js/category-management.js"></script>
</body>
</html>
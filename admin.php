<?php
session_start();
require_once 'includes/dbh.inc.php';
require_once 'includes/admin-functions.inc.php';

// Check if user is logged in and has appropriate role
if (!isset($_SESSION["userid"]) || !in_array($_SESSION["role"], ["admin", "editor"])) {
    header("location: login.php");
    exit();
}

// Fetch overview statistics
$stats = getDashboardStats($pdo);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Blog CMS</title>
    <link rel="stylesheet" href="css/admin-style.css">
    <link rel="stylesheet" href="/style.css">
</head>
<body>
    <div class="admin-container">
        <nav class="admin-nav">
            
            <a href="/home.php" class="logo">Blog CMS Admin</a>
            <ul>
                <li><a href="#overview" class="active">Overview</a></li>
                <li><a href="#users">Users</a></li>
                <li><a href="#posts">Posts</a></li>
                <li><a href="#comments">Comments</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </nav>

        <main class="admin-main">
            <section id="overview" class="dashboard-section">
                <h2>Dashboard Overview</h2>
                <div class="stats-grid">
                    <div class="stat-card">
                        <h3>Total Posts</h3>
                        <p><?php echo $stats['totalPosts']; ?></p>
                    </div>
                    <div class="stat-card">
                        <h3>Total Users</h3>
                        <p><?php echo $stats['totalUsers']; ?></p>
                    </div>
                    <div class="stat-card">
                        <h3>Total Comments</h3>
                        <p><?php echo $stats['totalComments']; ?></p>
                    </div>
                    <div class="stat-card">
                        <h3>Pending Comments</h3>
                        <p><?php echo $stats['pendingComments']; ?></p>
                    </div>
                </div>
            </section>

            <section id="users" class="dashboard-section">
                <h2>User Management</h2>
                <div class="action-bar">
                    <button onclick="showUserModal()" class="btn btn-primary">Add New User</button>
                </div>
                <div class="table-responsive">
                    <table id="usersTable">
                        <thead>
                            <tr>
                                <th>Username</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Created At</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Populated via AJAX -->
                        </tbody>
                    </table>
                </div>
            </section>

            <section id="posts" class="dashboard-section">
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
            </section>

            <section id="categories" class="dashboard-section">
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
            </section>
        </main>
    </div>

    <!-- User Modal -->
    <div id="userModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2>User Details</h2>
            <form id="userForm">
                <input type="hidden" id="userId" name="userId">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" required>
                </div>
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" required>
                </div>
                <div class="form-group">
                    <label for="role">Role</label>
                    <select id="role" name="role">
                        <option value="user">User</option>
                        <option value="editor">Editor</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">Save User</button>
            </form>
        </div>
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
                    <input type="text" id="categoryName" name="name" required>
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

    <script src="js/admin.js"></script>
    <script src="js/category-management.js"></script>
</body>
</html>
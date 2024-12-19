<?php
session_start();
require_once 'includes/dbh.inc.php';
require_once 'includes/category-functions.inc.php';

// Check if user is logged in and has appropriate role
if (!isset($_SESSION["userid"]) || !in_array($_SESSION["role"], ["admin", "editor"])) {
    header("location: login.php");
    exit();
}

// Check if post ID is provided
if (!isset($_GET['id'])) {
    header("location: admin.php#posts");
    exit();
}

$postId = $_GET['id'];

// Fetch post data
try {
    $stmt = $pdo->prepare("
        SELECT p.*, u.Username as AuthorName 
        FROM Posts p
        JOIN Users u ON p.UserID = u.UserID
        WHERE p.PostID = ?
    ");
    $stmt->execute([$postId]);
    $post = $stmt->fetch();

    if (!$post) {
        header("location: admin.php#posts");
        exit();
    }

    // Fetch post categories
    $stmt = $pdo->prepare("
        SELECT CategoryID 
        FROM PostCategories 
        WHERE PostID = ?
    ");
    $stmt->execute([$postId]);
    $postCategory = $stmt->fetch();

    // Fetch post tags
    $stmt = $pdo->prepare("
        SELECT t.Name 
        FROM Tags t
        JOIN PostTags pt ON t.TagID = pt.TagID
        WHERE pt.PostID = ?
    ");
    $stmt->execute([$postId]);
    $tags = $stmt->fetchAll(PDO::FETCH_COLUMN);

    // Fetch all categories for dropdown
    $categories = getAllCategories($pdo);

} catch (PDOException $e) {
    error_log("Error fetching post data: " . $e->getMessage());
    header("location: admin.php#posts");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Post - Blog CMS</title>
    <link rel="stylesheet" href="css/admin-style.css">
    <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/tagify/4.17.9/tagify.css" rel="stylesheet">
    <link rel="stylesheet" href="css/create-post.css">
    <link rel="stylesheet" href="/style.css">
</head>
<body>
    <div class="admin-container">
        <nav class="admin-nav">
            <div class="logo">Blog CMS</div>
            <ul>
                <li><a href="admin.php">Dashboard</a></li>
                <li><a href="admin.php#posts">Posts</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </nav>

        <main class="admin-main">
            <div class="post-form">
                <h1>Edit Post</h1>
                
                <form id="editPostForm" onsubmit="return submitPost(event)">
                    <input type="hidden" name="postId" value="<?php echo htmlspecialchars($post['PostID']); ?>">
                    
                    <div class="form-group">
                        <label for="title">Post Title</label>
                        <input type="text" id="title" name="title" required 
                               value="<?php echo htmlspecialchars($post['Title']); ?>">
                    </div>

                    <div class="form-group">
                        <label for="category">Category</label>
                        <select id="category" name="category" required>
                            <option value="">Select a category</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?php echo htmlspecialchars($category['CategoryID']); ?>"
                                    <?php echo ($postCategory && $postCategory['CategoryID'] == $category['CategoryID']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($category['Name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="tags">Tags</label>
                        <input id="tags" name="tags" value="<?php echo htmlspecialchars(implode(', ', $tags)); ?>">
                    </div>

                    <div class="form-group">
                        <label>Status</label>
                        <select id="status" name="status" required>
                            <option value="draft" <?php echo $post['Status'] === 'draft' ? 'selected' : ''; ?>>Draft</option>
                            <option value="published" <?php echo $post['Status'] === 'published' ? 'selected' : ''; ?>>Published</option>
                            <option value="archived" <?php echo $post['Status'] === 'archived' ? 'selected' : ''; ?>>Archived</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Featured Image</label>
                        <div class="featured-image-container">
                            <div class="upload-controls">
                                <input type="file" id="featuredImageFile" accept="image/*" style="display: none;">
                                <button type="button" class="btn-secondary" onclick="document.getElementById('featuredImageFile').click()">
                                    Choose Image
                                </button>
                                <input type="text" id="featuredImage" name="featuredImage" readonly 
                                       value="<?php echo htmlspecialchars($post['FeaturedImage'] ?? ''); ?>"
                                       placeholder="Upload an image or enter URL">
                            </div>
                            <?php if (!empty($post['FeaturedImage'])): ?>
                            <figure id="imagePreviewWrapper" class="blog-image-wrapper medium center">
                                <img id="imagePreview" class="blog-content-image" 
                                     src="<?php echo htmlspecialchars($post['FeaturedImage']); ?>" 
                                     alt="Featured image">
                                <figcaption id="imageCaption" class="blog-image-caption">
                                    <?php echo htmlspecialchars($post['FeaturedImageCaption'] ?? ''); ?>
                                </figcaption>
                            </figure>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Content</label>
                        <div class="editor-container">
                            <div id="editor"></div>
                        </div>
                    </div>

                    <div class="buttons-container">
                        <button type="submit" class="btn-primary">Update Post</button>
                    </div>
                </form>
            </div>
        </main>
    </div>

    <script src="/js/image-modal.js"></script>
    <script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/tagify/4.17.9/tagify.min.js"></script>
    <script>
        // Initialize Quill
        var quill = new Quill('#editor', {
            theme: 'snow',
            modules: {
                toolbar: [
                    [{ 'header': [1, 2, 3, 4, 5, 6, false] }],
                    ['bold', 'italic', 'underline', 'strike'],
                    ['blockquote', 'code-block'],
                    [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                    [{ 'script': 'sub'}, { 'script': 'super' }],
                    [{ 'indent': '-1'}, { 'indent': '+1' }],
                    [{ 'color': [] }, { 'background': [] }],
                    ['link', 'image'],
                    ['clean']
                ]
            }
        });

        // Set initial content
        quill.root.innerHTML = <?php echo json_encode($post['Content']); ?>;

        // Initialize Tagify
        var input = document.querySelector('input[name=tags]');
        var tagify = new Tagify(input, {
            maxTags: 10,
            dropdown: {
                maxItems: 20,
                classname: "tags-look",
                enabled: 0,
                closeOnSelect: false
            }
        });

        function submitPost(event) {
            event.preventDefault();
            
            const formData = new FormData(event.target);
            formData.append('content', quill.root.innerHTML);
            formData.append('action', 'update');
            
            // Get featured image caption if it exists
            const captionElement = document.getElementById('imageCaption');
            if (captionElement && captionElement.textContent) {
                formData.append('featuredImageCaption', captionElement.textContent);
            }
            
            // Get tags as array
            const tags = tagify.value.map(tag => tag.value);
            formData.append('tags', JSON.stringify(tags));
            
            fetch('includes/manage-post.inc.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.location.href = 'admin.php#posts';
                } else {
                    alert(data.error || 'An error occurred while updating the post');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while updating the post');
            });
            
            return false;
        }

        // Featured Image Upload Handler
        document.getElementById('featuredImageFile').addEventListener('change', async (event) => {
            const file = event.target.files[0];
            if (!file) return;

            const formData = new FormData();
            formData.append('image', file);

            try {
                const response = await fetch('includes/upload-image.inc.php', {
                    method: 'POST',
                    body: formData
                });
                const result = await response.json();

                if (result.success) {
                    document.getElementById('featuredImage').value = result.url;
                    const preview = document.getElementById('imagePreview');
                    const previewWrapper = document.getElementById('imagePreviewWrapper');
                    preview.src = result.url;
                    previewWrapper.style.display = 'block';
                } else {
                    alert('Failed to upload image');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Error uploading image');
            }
        });
    </script>
</body>
</html>
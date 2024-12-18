<?php
session_start();
require_once 'includes/dbh.inc.php';
require_once 'includes/category-functions.inc.php';

// Check if user is logged in and has appropriate role
if (!isset($_SESSION["userid"]) || !in_array($_SESSION["role"], ["admin", "editor"])) {
    header("location: login.php");
    exit();
}

// Fetch all categories
$categories = getAllCategories($pdo);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create New Post - Blog CMS</title>
    <link rel="stylesheet" href="css/admin-style.css">
    <!-- Quill CSS -->
    <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
    <!-- Tagify CSS -->
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
                <h1>Create New Post</h1>
                
                <form id="createPostForm" onsubmit="return submitPost(event)">
                    <div class="form-group">
                        <label for="title">Post Title</label>
                        <input type="text" id="title" name="title" required>
                    </div>

                    <div class="form-group">
                        <label for="category">Category</label>
                        <select id="category" name="category" required>
                            <option value="">Select a category</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?php echo htmlspecialchars($category['CategoryID']); ?>">
                                    <?php echo htmlspecialchars($category['Name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="tags">Tags</label>
                        <input id="tags" name="tags">
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
                                       placeholder="Upload an image or enter URL">
                            </div>
                            <figure id="imagePreviewWrapper" class="blog-image-wrapper medium center" style="display: none;">
                                <img id="imagePreview" class="blog-content-image" alt="Preview">
                                <figcaption id="imageCaption" class="blog-image-caption"></figcaption>
                            </figure>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Content</label>
                        <div class="editor-container">
                            <div id="editor"></div>
                        </div>
                    </div>

                    <div class="buttons-container">
                        <button type="button" class="btn-secondary" onclick="saveDraft()">Save as Draft</button>
                        <button type="submit" class="btn-primary">Publish Post</button>
                    </div>
                </form>
            </div>
        </main>
    </div>

    <script src="/js/image-modal.js"></script>

    <!-- Quill JS -->
    <script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>
    <!-- Tagify JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/tagify/4.17.9/tagify.min.js"></script>
    <script>
        
        // Define custom image blot with additional features
        const BlockEmbed = Quill.import('blots/block/embed');

        class BlogImageBlot extends BlockEmbed {
            static create(value) {
                // Create figure element (since that's our tagName)
                const node = super.create();
                node.className = `blog-image-wrapper ${value.size || 'medium'} ${value.alignment || 'center'}`;
                if (value.enlargeable !== false) {
                    node.setAttribute('data-enlargeable', 'true');
                }
            
                // Create and append image
                const img = document.createElement('img');
                img.setAttribute('src', value.src);
                img.setAttribute('class', 'blog-content-image');
                img.setAttribute('alt', value.alt || '');
                node.appendChild(img);
            
                // Add caption if provided
                if (value.caption) {
                    const caption = document.createElement('figcaption');
                    caption.textContent = value.caption;
                    caption.className = 'blog-image-caption';
                    node.appendChild(caption);
                }
            
                return node;
            }
        
            static value(node) {
                const img = node.querySelector('.blog-content-image');
                const caption = node.querySelector('.blog-image-caption');
                return {
                    src: img.getAttribute('src'),
                    alt: img.getAttribute('alt'),
                    caption: caption ? caption.textContent : null,
                    size: node.className.match(/\b(small|medium|large|full)\b/)?.[0] || 'medium',
                    alignment: node.className.match(/\b(left|center|right)\b/)?.[0] || 'center',
                    enlargeable: node.getAttribute('data-enlargeable') === 'true'
                };
            }
        }

        BlogImageBlot.blotName = 'blog-image';
        BlogImageBlot.tagName = 'figure';

        // Register the custom blot
        Quill.register(BlogImageBlot);

        // Image toolbar handlers
        function imageHandler() {
            const input = document.createElement('input');
            input.setAttribute('type', 'file');
            input.setAttribute('accept', 'image/*');
            input.click();
        
            input.onchange = async () => {
                const file = input.files[0];
                const formData = new FormData();
                formData.append('image', file);
            
                try {
                    const response = await fetch('includes/upload-image.inc.php', {
                        method: 'POST',
                        body: formData
                    });
                    const result = await response.json();

                    if (result.success) {
                        // Show image options dialog
                        showImageOptionsDialog(result.url);
                    } else {
                        alert('Failed to upload image');
                    }
                } catch (error) {
                    console.error('Error:', error);
                    alert('Error uploading image');
                }
            };
        }

        function showImageOptionsDialog(imageUrl) {
            // Create dialog
            const dialog = document.createElement('div');
            dialog.className = 'image-options-dialog';
            dialog.innerHTML = `
                <div class="dialog-content">
                    <h3>Image Options</h3>
                    <div class="form-group">
                        <label>Alt Text:</label>
                        <input type="text" id="image-alt" placeholder="Describe the image">
                    </div>
                    <div class="form-group">
                        <label>Caption:</label>
                        <input type="text" id="image-caption" placeholder="Optional caption">
                    </div>
                    <div class="form-group">
                        <label>Size:</label>
                        <select id="image-size">
                            <option value="small">Small</option>
                            <option value="medium" selected>Medium</option>
                            <option value="large">Large</option>
                            <option value="full">Full Width</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Alignment:</label>
                        <select id="image-alignment">
                            <option value="left">Left</option>
                            <option value="center" selected>Center</option>
                            <option value="right">Right</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>
                            <input type="checkbox" id="image-enlargeable" checked>
                            Enable click to enlarge
                        </label>
                    </div>
                    <div class="dialog-buttons">
                        <button class="btn-cancel">Cancel</button>
                        <button class="btn-insert">Insert Image</button>
                    </div>
                </div>
            `;
        
            document.body.appendChild(dialog);
        
            // Handle dialog buttons
            const cancelBtn = dialog.querySelector('.btn-cancel');
            const insertBtn = dialog.querySelector('.btn-insert');
        
            cancelBtn.onclick = () => document.body.removeChild(dialog);

            insertBtn.onclick = () => {
                const range = quill.getSelection(true);
                const imageData = {
                    src: imageUrl,
                    alt: document.getElementById('image-alt').value,
                    caption: document.getElementById('image-caption').value,
                    size: document.getElementById('image-size').value,
                    alignment: document.getElementById('image-alignment').value,
                    enlargeable: document.getElementById('image-enlargeable').checked
                };

                quill.insertEmbed(range.index, 'blog-image', imageData, Quill.sources.USER);
                quill.setSelection(range.index + 1, Quill.sources.SILENT);
                document.body.removeChild(dialog);
            };
        }

        // Initialize Quill with custom image handler
        var quill = new Quill('#editor', {
            theme: 'snow',
            modules: {
                toolbar: {
                    container: [
                        [{ 'header': [1, 2, 3, 4, 5, 6, false] }],
                        ['bold', 'italic', 'underline', 'strike'],
                        ['blockquote', 'code-block'],
                        [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                        [{ 'script': 'sub'}, { 'script': 'super' }],
                        [{ 'indent': '-1'}, { 'indent': '+1' }],
                        [{ 'color': [] }, { 'background': [] }],
                        ['link', 'image'],
                        ['clean']
                    ],
                    handlers: {
                        image: imageHandler
                    }
                }
            }
        });

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

        function previewImage(url) {
            const preview = document.getElementById('imagePreview');
            if (url) {
                preview.src = url;
                preview.style.display = 'block';
            } else {
                preview.style.display = 'none';
            }
        }

        function submitPost(event) {
            event.preventDefault();
                
            const formData = new FormData(event.target);
            formData.append('content', quill.root.innerHTML);
            formData.append('status', 'published');
                
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
                    alert(data.error || 'An error occurred while saving the post');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while saving the post');
            });
            
            return false;
        }

        function saveDraft() {
            const form = document.getElementById('createPostForm');
            const formData = new FormData(form);
            formData.append('content', quill.root.innerHTML);
            formData.append('status', 'draft');

            // Get featured image caption if it exists
            const captionElement = document.getElementById('imageCaption');
            if (captionElement && captionElement.textContent) {
                formData.append('featuredImageCaption', captionElement.textContent);
            }

            const tags = tagify.value.map(tag => tag.value);
            formData.append('tags', JSON.stringify(tags));

            fetch('includes/manage-post.inc.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Post saved as draft');
                    window.location.href = 'admin.php#posts';
                } else {
                    alert(data.error || 'An error occurred while saving the draft');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while saving the draft');
            });
        }
    </script>

    <script>
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
                showFeaturedImageOptions(result.url);
            } else {
                alert('Failed to upload image');
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Error uploading image');
        }
    });

    function showFeaturedImageOptions(imageUrl) {
        // Create dialog
        const dialog = document.createElement('div');
        dialog.className = 'image-options-dialog';
        dialog.innerHTML = `
            <div class="dialog-content">
                <h3>Featured Image Options</h3>
                <div class="form-group">
                    <label>Alt Text:</label>
                    <input type="text" id="featured-image-alt" placeholder="Describe the image">
                </div>
                <div class="form-group">
                    <label>Caption:</label>
                    <input type="text" id="featured-image-caption" placeholder="Optional caption">
                </div>
                <div class="dialog-buttons">
                    <button class="btn-cancel">Cancel</button>
                    <button class="btn-insert">Set Featured Image</button>
                </div>
            </div>
        `;

        document.body.appendChild(dialog);

        // Handle dialog buttons
        const cancelBtn = dialog.querySelector('.btn-cancel');
        const insertBtn = dialog.querySelector('.btn-insert');

        cancelBtn.onclick = () => document.body.removeChild(dialog);

        insertBtn.onclick = () => {
            const altText = document.getElementById('featured-image-alt').value;
            const caption = document.getElementById('featured-image-caption').value;

            // Update hidden input and preview
            document.getElementById('featuredImage').value = imageUrl;
            const preview = document.getElementById('imagePreview');
            const previewWrapper = document.getElementById('imagePreviewWrapper');
            const captionElement = document.getElementById('imageCaption');

            preview.src = imageUrl;
            preview.alt = altText;
            captionElement.textContent = caption;
            previewWrapper.style.display = caption ? 'block' : 'none';

            document.body.removeChild(dialog);
        };
    }
    </script>

</body>
</html>
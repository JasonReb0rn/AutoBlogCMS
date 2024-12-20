// js/post-management.js
document.addEventListener('DOMContentLoaded', function() {
    const isFullPage = window.location.pathname.includes('posts-admin.php');
    
    if (isFullPage) {
        let currentPage = 1;
        loadPosts(currentPage);
        
        document.getElementById('prevPage').addEventListener('click', () => {
            if (currentPage > 1) {
                currentPage--;
                loadPosts(currentPage);
            }
        });
        
        document.getElementById('nextPage').addEventListener('click', () => {
            currentPage++;
            loadPosts(currentPage);
        });
    } else {
        // We're on the dashboard, load mini version
        loadMiniPosts();
    }
});

function loadPosts(page) {
    const offset = (page - 1) * 50;
    fetch(`includes/get-posts.inc.php?offset=${offset}&limit=50`)
        .then(response => response.json())
        .then(data => {
            const tbody = document.querySelector('#postsTable tbody');
            if (!tbody) return;
            
            tbody.innerHTML = '';
            
            if (data.posts && Array.isArray(data.posts)) {
                data.posts.forEach(post => {
                    const tr = document.createElement('tr');
                    tr.innerHTML = `
                        <td>
                            <a href="/blog/post/${post.PostID}/${createSlug(post.Title)}" target="_blank">
                                ${post.Title}
                            </a>
                        </td>
                        <td>${post.Username}</td>
                        <td>${post.Status}</td>
                        <td>${post.CreatedAt}</td>
                        <td>
                            <button onclick="editPost(${post.PostID})" class="btn btn-secondary">Edit</button>
                            <button onclick="deletePost(${post.PostID})" class="btn btn-danger">Delete</button>
                        </td>
                    `;
                    tbody.appendChild(tr);
                });
            }
        })
        .catch(error => console.error('Error loading posts:', error));
}

function loadMiniPosts() {
    fetch('includes/get-posts-mini.inc.php')
        .then(response => response.json())
        .then(data => {
            const tbody = document.querySelector('#postsTable tbody');
            if (!tbody) return;

            tbody.innerHTML = '';
            
            if (Array.isArray(data)) {
                data.forEach(post => {
                    const tr = document.createElement('tr');
                    tr.innerHTML = `
                        <td>
                            <a href="/blog/post/${post.PostID}/${createSlug(post.Title)}" target="_blank">
                                ${post.Title}
                            </a>
                        </td>
                        <td>${post.Username}</td>
                        <td>${post.Status}</td>
                        <td>${post.CreatedAt}</td>
                        <td>
                            <button onclick="editPost(${post.PostID})" class="btn btn-secondary">Edit</button>
                            <button onclick="deletePost(${post.PostID})" class="btn btn-danger">Delete</button>
                        </td>
                    `;
                    tbody.appendChild(tr);
                });

                if (data.length === 10) {
                    const tfoot = document.createElement('tfoot');
                    tfoot.innerHTML = `
                        <tr>
                            <td colspan="5" style="text-align: center;">
                                <a href="posts-admin.php" class="btn btn-primary">View All Posts</a>
                            </td>
                        </tr>
                    `;
                    tbody.parentNode.appendChild(tfoot);
                }
            }
        })
        .catch(error => console.error('Error loading posts:', error));
}

// Keep these functions in global scope for the onclick handlers
window.editPost = function(postId) {
    window.location.href = `edit-post.php?id=${postId}`;
};

window.deletePost = function(postId) {
    if (!confirm('Are you sure you want to delete this post?')) {
        return;
    }
    
    const formData = new FormData();
    formData.append('action', 'delete');
    formData.append('postId', postId);
    
    fetch('includes/manage-post.inc.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const isFullPage = window.location.pathname.includes('posts-admin.php');
            if (isFullPage) {
                loadPosts(1);
            } else {
                loadMiniPosts();
            }
        } else {
            alert(data.error || 'An error occurred');
        }
    })
    .catch(error => console.error('Error deleting post:', error));
};

function createSlug(text) {
    return text.toLowerCase()
        .replace(/[^a-z0-9-]+/g, '-')
        .replace(/-+/g, '-')
        .replace(/^-|-$/g, '');
}

// js/category-management.js
document.addEventListener('DOMContentLoaded', function() {
    const isFullPage = window.location.pathname.includes('categories-admin.php');
    
    if (isFullPage) {
        let currentPage = 1;
        loadCategories(currentPage);
        
        document.getElementById('prevPage').addEventListener('click', () => {
            if (currentPage > 1) {
                currentPage--;
                loadCategories(currentPage);
            }
        });
        
        document.getElementById('nextPage').addEventListener('click', () => {
            currentPage++;
            loadCategories(currentPage);
        });
    } else {
        // We're on the dashboard, load mini version
        loadMiniCategories();
    }
    
    // Modal functionality
    const modal = document.getElementById('categoryModal');
    if (modal) {
        const closeButtons = modal.getElementsByClassName('close');
        
        Array.from(closeButtons).forEach(button => {
            button.onclick = function() {
                modal.style.display = "none";
            }
        });
        
        window.onclick = function(event) {
            if (event.target === modal) {
                modal.style.display = "none";
            }
        }
        
        const categoryForm = document.getElementById('categoryForm');
        if (categoryForm) {
            categoryForm.addEventListener('submit', function(e) {
                e.preventDefault();
                saveCategory(e);
            });
        }

        // Auto-generate slug from name
        const nameInput = document.getElementById('categoryName');
        const slugInput = document.getElementById('categorySlug');
        if (nameInput && slugInput) {
            nameInput.addEventListener('input', function(e) {
                if (!slugInput.dataset.manually_edited) {
                    slugInput.value = createSlug(e.target.value);
                }
            });

            slugInput.addEventListener('input', function(e) {
                this.dataset.manually_edited = 'true';
            });
        }
    }
});

function loadCategories(page) {
    const offset = (page - 1) * 50;
    fetch(`includes/get-categories.inc.php?offset=${offset}&limit=50`)
        .then(response => response.json())
        .then(data => {
            const tbody = document.querySelector('#categoriesTable tbody');
            if (!tbody) return;

            tbody.innerHTML = '';
            
            if (data.categories && Array.isArray(data.categories)) {
                data.categories.forEach(category => {
                    const tr = document.createElement('tr');
                    tr.innerHTML = `
                        <td>${category.Name}</td>
                        <td>${category.Slug}</td>
                        <td>${category.CreatedAt}</td>
                        <td>
                            <button onclick="editCategory(${category.CategoryID})" class="btn btn-secondary">Edit</button>
                            <button onclick="deleteCategory(${category.CategoryID})" class="btn btn-danger">Delete</button>
                        </td>
                    `;
                    tbody.appendChild(tr);
                });
            }
        })
        .catch(error => console.error('Error loading categories:', error));
}

function loadMiniCategories() {
    fetch('includes/get-categories-mini.inc.php')
        .then(response => response.json())
        .then(data => {
            const tbody = document.querySelector('#categoriesTable tbody');
            if (!tbody) return;

            tbody.innerHTML = '';
            
            if (Array.isArray(data)) {
                data.forEach(category => {
                    const tr = document.createElement('tr');
                    tr.innerHTML = `
                        <td>${category.Name}</td>
                        <td>${category.Slug}</td>
                        <td>${category.CreatedAt}</td>
                        <td>
                            <button onclick="editCategory(${category.CategoryID})" class="btn btn-secondary">Edit</button>
                            <button onclick="deleteCategory(${category.CategoryID})" class="btn btn-danger">Delete</button>
                        </td>
                    `;
                    tbody.appendChild(tr);
                });

                if (data.length === 10) {
                    const tfoot = document.createElement('tfoot');
                    tfoot.innerHTML = `
                        <tr>
                            <td colspan="4" style="text-align: center;">
                                <a href="categories-admin.php" class="btn btn-primary">View All Categories</a>
                            </td>
                        </tr>
                    `;
                    tbody.parentNode.appendChild(tfoot);
                }
            }
        })
        .catch(error => console.error('Error loading categories:', error));
}

// Keep these functions in global scope for the onclick handlers
window.showCategoryModal = function(categoryId = null) {
    const modal = document.getElementById('categoryModal');
    const form = document.getElementById('categoryForm');
    const slugInput = document.getElementById('categorySlug');
    
    if (!modal || !form || !slugInput) return;
    
    // Reset the manually_edited flag
    slugInput.dataset.manually_edited = '';
    
    if (categoryId) {
        // Edit mode - fetch category details
        fetch(`includes/get-categories.inc.php?id=${categoryId}`)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Category not found');
                }
                return response.json();
            })
            .then(data => {
                if (data.categories && data.categories[0]) {
                    const category = data.categories[0];
                    document.getElementById('categoryId').value = category.CategoryID;
                    document.getElementById('categoryName').value = category.Name;
                    document.getElementById('categorySlug').value = category.Slug;
                }
            })
            .catch(error => {
                console.error('Error fetching category:', error);
                alert('Error loading category details');
                modal.style.display = "none";
            });
    } else {
        // Create mode - clear form
        form.reset();
        document.getElementById('categoryId').value = '';
    }
    
    modal.style.display = "block";
};

window.editCategory = function(categoryId) {
    showCategoryModal(categoryId);
};

window.deleteCategory = function(categoryId) {
    if (!confirm('Are you sure you want to delete this category?')) {
        return;
    }
    
    const formData = new FormData();
    formData.append('action', 'delete');
    formData.append('categoryId', categoryId);
    
    fetch('includes/manage-category.inc.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const isFullPage = window.location.pathname.includes('categories-admin.php');
            if (isFullPage) {
                loadCategories(1);
            } else {
                loadMiniCategories();
            }
        } else {
            alert(data.error || 'An error occurred');
        }
    })
    .catch(error => console.error('Error deleting category:', error));
};

window.saveCategory = function(e) {
    const formData = new FormData(e.target);
    const categoryId = document.getElementById('categoryId').value;
    
    formData.append('action', categoryId ? 'update' : 'create');
    if (categoryId) {
        formData.append('categoryId', categoryId);
    }
    
    fetch('includes/manage-category.inc.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const modal = document.getElementById('categoryModal');
            if (modal) {
                modal.style.display = "none";
            }
            
            const isFullPage = window.location.pathname.includes('categories-admin.php');
            if (isFullPage) {
                loadCategories(1);
            } else {
                loadMiniCategories();
            }
        } else {
            let errorDiv = document.getElementById('categoryFormError');
            if (!errorDiv) {
                errorDiv = document.createElement('div');
                errorDiv.id = 'categoryFormError';
                errorDiv.classList.add('error-message');
                document.getElementById('categoryForm').prepend(errorDiv);
            }
            
            errorDiv.textContent = data.error || 'An error occurred while saving the category';
            errorDiv.style.display = 'block';
        }
    })
    .catch(error => console.error('Error saving category:', error));
};
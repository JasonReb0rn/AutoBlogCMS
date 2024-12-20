// js/category-management.js
document.addEventListener('DOMContentLoaded', function() {
    loadCategories();
    
    document.getElementById('categoryForm').addEventListener('submit', function(e) {
        e.preventDefault();
        saveCategory(e);
    });
    
    // Auto-generate slug from name
    document.getElementById('categoryName').addEventListener('input', function(e) {
        const slugInput = document.getElementById('categorySlug');
        // Only auto-generate if the slug field hasn't been manually edited
        if (!slugInput.dataset.manually_edited) {
            slugInput.value = e.target.value.toLowerCase()
                .replace(/[^a-z0-9-_]/g, '-')
                .replace(/-+/g, '-')
                .replace(/^-|-$/g, '');
        }
    });

    // Has the slug been manually edited?
    document.getElementById('categorySlug').addEventListener('input', function(e) {
        this.dataset.manually_edited = 'true';
    });

});

function loadCategories() {
    fetch('includes/get-categories.inc.php')
        .then(response => response.json())
        .then(data => {
            const tbody = document.querySelector('#categoriesTable tbody');
            tbody.innerHTML = '';
            
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
        })
        .catch(error => console.error('Error loading categories:', error));
}

function showCategoryModal(categoryId = null) {
    const modal = document.getElementById('categoryModal');
    const form = document.getElementById('categoryForm');
    const slugInput = document.getElementById('categorySlug');
    
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
            .then(category => {
                if (category && category.Name) {  // Check if we got valid data
                    document.getElementById('categoryId').value = category.CategoryID;
                    document.getElementById('categoryName').value = category.Name;
                    document.getElementById('categorySlug').value = category.Slug;
                    // Mark as manually edited since we're loading an existing slug
                    slugInput.dataset.manually_edited = 'true';
                } else {
                    throw new Error('Invalid category data');
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
}

function editCategory(categoryId) {
    if (categoryId) {
        showCategoryModal(categoryId);
    } else {
        console.error('No category ID provided for edit');
    }
}

function saveCategory(e) {
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
            document.getElementById('categoryModal').style.display = "none";
            loadCategories();
        } else {
            // Create or get error message element
            let errorDiv = document.getElementById('categoryFormError');
            if (!errorDiv) {
                errorDiv = document.createElement('div');
                errorDiv.id = 'categoryFormError';
                errorDiv.classList.add('error-message');
                document.getElementById('categoryForm').prepend(errorDiv);
            }
            
            // Show specific error message
            errorDiv.textContent = data.error || 'An error occurred while saving the category';
            errorDiv.style.display = 'block';
        }
    })
    .catch(error => console.error('Error saving category:', error));
}

function deleteCategory(categoryId) {
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
            loadCategories();
        } else {
            alert(data.error || 'An error occurred');
        }
    })
    .catch(error => console.error('Error deleting category:', error));
}
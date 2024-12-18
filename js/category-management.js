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
        if (!slugInput.value) {
            slugInput.value = e.target.value.toLowerCase()
                .replace(/[^a-z0-9-_]/g, '-')
                .replace(/-+/g, '-')
                .replace(/^-|-$/g, '');
        }
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
                        <button onclick="editCategory(${category.CategoryID})" class="btn-edit">Edit</button>
                        <button onclick="deleteCategory(${category.CategoryID})" class="btn-delete">Delete</button>
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
            alert(data.error || 'An error occurred');
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
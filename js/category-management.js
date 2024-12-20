
// js/category-management.js
document.addEventListener('DOMContentLoaded', function() {
    let currentPage = 1;
    const isFullPage = window.location.pathname.includes('categories-admin.php');
    
    if (isFullPage) {
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
    const closeButtons = document.getElementsByClassName('close');
    
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
    
    document.getElementById('categoryForm').addEventListener('submit', function(e) {
        e.preventDefault();
        saveCategory(e);
    });

    // Auto-generate slug from name
    document.getElementById('categoryName').addEventListener('input', function(e) {
        const slugInput = document.getElementById('categorySlug');
        if (!slugInput.dataset.manually_edited) {
            slugInput.value = createSlug(e.target.value);
        }
    });

    document.getElementById('categorySlug').addEventListener('input', function(e) {
        this.dataset.manually_edited = 'true';
    });
});

function loadCategories(page) {
    const offset = (page - 1) * 50;
    fetch(`includes/get-categories.inc.php?offset=${offset}&limit=50`)
        .then(response => response.json())
        .then(data => {
            const tbody = document.querySelector('#categoriesTable tbody');
            tbody.innerHTML = '';
            
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

            document.getElementById('pageInfo').textContent = `Page ${page}`;
            document.getElementById('prevPage').disabled = page === 1;
            document.getElementById('nextPage').disabled = data.categories.length < 50;
        })
        .catch(error => console.error('Error loading categories:', error));
}

function loadMiniCategories() {
    fetch('includes/get-categories-mini.inc.php')
        .then(response => response.json())
        .then(categories => {
            const tbody = document.querySelector('#categoriesTable tbody');
            tbody.innerHTML = '';
            
            categories.forEach(category => {
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

            if (categories.length === 10) {
                const tfoot = document.createElement('tfoot');
                tfoot.innerHTML = `
                    <tr>
                        <td colspan="4">
                            <a href="categories-admin.php" class="btn btn-primary">View All Categories</a>
                        </td>
                    </tr>
                `;
                tbody.parentNode.appendChild(tfoot);
            }
        })
        .catch(error => console.error('Error loading categories:', error));
}
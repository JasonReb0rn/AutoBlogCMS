// js/user-management.js
document.addEventListener('DOMContentLoaded', function() {
    const isFullPage = window.location.pathname.includes('users-admin.php');
    
    if (isFullPage) {
        let currentPage = 1;
        loadUsers(currentPage);
        
        document.getElementById('prevPage').addEventListener('click', () => {
            if (currentPage > 1) {
                currentPage--;
                loadUsers(currentPage);
            }
        });
        
        document.getElementById('nextPage').addEventListener('click', () => {
            currentPage++;
            loadUsers(currentPage);
        });
    } else {
        // We're on the dashboard, load mini version
        loadMiniUsers();
    }
    
    // Modal functionality
    const modal = document.getElementById('userModal');
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
        
        const userForm = document.getElementById('userForm');
        if (userForm) {
            userForm.addEventListener('submit', function(e) {
                e.preventDefault();
                saveUser();
            });
        }
    }
});

function loadUsers(page) {
    const offset = (page - 1) * 50;
    fetch(`includes/get-users.inc.php?offset=${offset}&limit=50`)
        .then(response => response.json())
        .then(data => {
            const tbody = document.querySelector('#usersTable tbody');
            if (!tbody) return;

            tbody.innerHTML = '';
            
            if (data.users && Array.isArray(data.users)) {
                data.users.forEach(user => {
                    const tr = document.createElement('tr');
                    tr.innerHTML = `
                        <td>${user.Username}</td>
                        <td>${user.Email}</td>
                        <td>${user.Role}</td>
                        <td>${user.CreatedAt}</td>
                        <td>
                            <button onclick="editUser(${user.UserID})" class="btn btn-secondary">Edit</button>
                            <button onclick="deleteUser(${user.UserID})" class="btn btn-danger">Delete</button>
                        </td>
                    `;
                    tbody.appendChild(tr);
                });

                const pageInfo = document.getElementById('pageInfo');
                if (pageInfo) {
                    pageInfo.textContent = `Page ${page}`;
                }
                const prevButton = document.getElementById('prevPage');
                if (prevButton) {
                    prevButton.disabled = page === 1;
                }
                const nextButton = document.getElementById('nextPage');
                if (nextButton) {
                    nextButton.disabled = data.users.length < 50;
                }
            }
        })
        .catch(error => console.error('Error loading users:', error));
}

function loadMiniUsers() {
    fetch('includes/get-users-mini.inc.php')
        .then(response => response.json())
        .then(data => {
            const tbody = document.querySelector('#usersTable tbody');
            if (!tbody) return;

            tbody.innerHTML = '';
            
            if (Array.isArray(data)) {
                data.forEach(user => {
                    const tr = document.createElement('tr');
                    tr.innerHTML = `
                        <td>${user.Username}</td>
                        <td>${user.Email}</td>
                        <td>${user.Role}</td>
                        <td>${user.CreatedAt}</td>
                        <td>
                            <button onclick="editUser(${user.UserID})" class="btn btn-secondary">Edit</button>
                            <button onclick="deleteUser(${user.UserID})" class="btn btn-danger">Delete</button>
                        </td>
                    `;
                    tbody.appendChild(tr);
                });

                // Add "View More" button if we have exactly 10 records
                if (data.length === 10) {
                    const tfoot = document.createElement('tfoot');
                    tfoot.innerHTML = `
                        <tr>
                            <td colspan="5">
                                <a href="users-admin.php" class="btn btn-primary">View All Users</a>
                            </td>
                        </tr>
                    `;
                    tbody.parentNode.appendChild(tfoot);
                }
            }
        })
        .catch(error => console.error('Error loading users:', error));
}

function showUserModal(userId = null) {
    const modal = document.getElementById('userModal');
    const form = document.getElementById('userForm');
    
    if (!modal || !form) return;
    
    if (userId) {
        // Edit mode - fetch user details
        fetch(`includes/get-users.inc.php?id=${userId}`)
            .then(response => response.json())
            .then(data => {
                if (data.users && data.users[0]) {
                    const user = data.users[0];
                    document.getElementById('userId').value = user.UserID;
                    document.getElementById('username').value = user.Username;
                    document.getElementById('email').value = user.Email;
                    document.getElementById('role').value = user.Role;
                }
            });
    } else {
        // Create mode - clear form
        form.reset();
        document.getElementById('userId').value = '';
    }
    
    modal.style.display = "block";
}

function editUser(userId) {
    showUserModal(userId);
}

function deleteUser(userId) {
    if (!confirm('Are you sure you want to delete this user?')) {
        return;
    }
    
    const formData = new FormData();
    formData.append('action', 'delete');
    formData.append('userId', userId);
    
    fetch('includes/manage-user.inc.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const isFullPage = window.location.pathname.includes('users-admin.php');
            if (isFullPage) {
                loadUsers(1);
            } else {
                loadMiniUsers();
            }
        } else {
            alert(data.error || 'An error occurred');
        }
    })
    .catch(error => console.error('Error deleting user:', error));
}

function saveUser() {
    const form = document.getElementById('userForm');
    if (!form) return;
    
    const formData = new FormData(form);
    const userId = document.getElementById('userId').value;
    
    formData.append('action', userId ? 'update' : 'create');
    if (userId) {
        formData.append('userId', userId);
    }
    
    fetch('includes/manage-user.inc.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const modal = document.getElementById('userModal');
            if (modal) {
                modal.style.display = "none";
            }
            
            const isFullPage = window.location.pathname.includes('users-admin.php');
            if (isFullPage) {
                loadUsers(1);
            } else {
                loadMiniUsers();
            }
        } else {
            alert(data.error || 'An error occurred');
        }
    })
    .catch(error => console.error('Error saving user:', error));
}
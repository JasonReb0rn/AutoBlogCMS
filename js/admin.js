// js/admin.js
document.addEventListener('DOMContentLoaded', function() {
    loadUsers();
    loadPosts();
    
    // Modal functionality for all modals
    const modals = document.getElementsByClassName('modal');
    const closeButtons = document.getElementsByClassName('close');
    
    // Add click handler for all close buttons
    Array.from(closeButtons).forEach(button => {
        button.onclick = function() {
            const modal = button.closest('.modal');
            if (modal) {
                modal.style.display = "none";
            }
        }
    });
    
    // Close modal when clicking outside
    window.onclick = function(event) {
        if (event.target.classList.contains('modal')) {
            event.target.style.display = "none";
        }
    }
    
    // Form submit handlers
    document.getElementById('userForm').addEventListener('submit', function(e) {
        e.preventDefault();
        saveUser();
    });
});

function showUserModal(userId = null) {
    const modal = document.getElementById('userModal');
    const form = document.getElementById('userForm');
    
    if (userId) {
        // Edit mode - fetch user details
        fetch(`includes/get-users.inc.php?id=${userId}`)
            .then(response => response.json())
            .then(user => {
                document.getElementById('userId').value = user.UserID;
                document.getElementById('username').value = user.Username;
                document.getElementById('email').value = user.Email;
                document.getElementById('role').value = user.Role;
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

function saveUser() {
    const formData = new FormData(document.getElementById('userForm'));
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
            document.getElementById('userModal').style.display = "none";
            loadUsers();
        } else {
            alert(data.error || 'An error occurred');
        }
    })
    .catch(error => console.error('Error saving user:', error));
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
            loadUsers();
        } else {
            alert(data.error || 'An error occurred');
        }
    })
    .catch(error => console.error('Error deleting user:', error));
}

function loadUsers() {
    fetch('includes/get-users.inc.php')
        .then(response => response.json())
        .then(data => {
            const tbody = document.querySelector('#usersTable tbody');
            tbody.innerHTML = '';
            
            data.forEach(user => {
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td>${user.Username}</td>
                    <td>${user.Email}</td>
                    <td>${user.Role}</td>
                    <td>${user.CreatedAt}</td>
                    <td>
                        <button onclick="editUser(${user.UserID})" class="btn-edit">Edit</button>
                        <button onclick="deleteUser(${user.UserID})" class="btn-delete">Delete</button>
                    </td>
                `;
                tbody.appendChild(tr);
            });
        })
        .catch(error => console.error('Error loading users:', error));
}

function editPost(postId) {
    // Redirect to edit-post.php with the post ID
    window.location.href = `edit-post.php?id=${postId}`;
}

function deletePost(postId) {
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
            loadPosts();
        } else {
            alert(data.error || 'An error occurred');
        }
    })
    .catch(error => console.error('Error deleting post:', error));
}

function loadPosts() {
    fetch('includes/get-posts.inc.php')
        .then(response => response.json())
        .then(data => {
            const tbody = document.querySelector('#postsTable tbody');
            tbody.innerHTML = '';
            
            data.forEach(post => {
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td>${post.Title}</td>
                    <td>${post.Username}</td>
                    <td>${post.Status}</td>
                    <td>${post.CreatedAt}</td>
                    <td>
                        <button onclick="editPost(${post.PostID})" class="btn-edit">Edit</button>
                        <button onclick="deletePost(${post.PostID})" class="btn-delete">Delete</button>
                    </td>
                `;
                tbody.appendChild(tr);
            });
        })
        .catch(error => console.error('Error loading posts:', error));
}
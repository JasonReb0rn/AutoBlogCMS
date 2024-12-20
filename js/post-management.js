// js/post-management.js
document.addEventListener('DOMContentLoaded', function() {
    let currentPage = 1;
    const isFullPage = window.location.pathname.includes('posts-admin.php');
    
    if (isFullPage) {
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
            tbody.innerHTML = '';
            
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

            document.getElementById('pageInfo').textContent = `Page ${page}`;
            document.getElementById('prevPage').disabled = page === 1;
            document.getElementById('nextPage').disabled = data.posts.length < 50;
        })
        .catch(error => console.error('Error loading posts:', error));
}

function loadMiniPosts() {
    fetch('includes/get-posts-mini.inc.php')
        .then(response => response.json())
        .then(posts => {
            const tbody = document.querySelector('#postsTable tbody');
            tbody.innerHTML = '';
            
            posts.forEach(post => {
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

            if (posts.length === 10) {
                const tfoot = document.createElement('tfoot');
                tfoot.innerHTML = `
                    <tr>
                        <td colspan="5">
                            <a href="posts-admin.php" class="btn btn-primary">View All Posts</a>
                        </td>
                    </tr>
                `;
                tbody.parentNode.appendChild(tfoot);
            }
        })
        .catch(error => console.error('Error loading posts:', error));
}

// Helper function for creating URL-friendly slugs
function createSlug(text) {
    return text.toLowerCase()
        .replace(/[^a-z0-9-]+/g, '-')
        .replace(/-+/g, '-')
        .replace(/^-|-$/g, '');
}
// search.js
document.addEventListener('DOMContentLoaded', function() {
    const searchBar = document.getElementById('search-bar');
    if (!searchBar) return;

    let searchTimeout;
    const searchDelay = 300; // milliseconds

    searchBar.addEventListener('input', function(e) {
        clearTimeout(searchTimeout);
        const searchTerm = this.value.trim();
        
        // Clear results if search is empty
        if (searchTerm === '') {
            window.location.href = window.location.pathname; // Reset to current page without search
            return;
        }
        
        // Add loading indicator
        this.classList.add('loading');
        
        // Debounce search
        searchTimeout = setTimeout(() => {
            performSearch(searchTerm);
        }, searchDelay);
    });
});

function performSearch(searchTerm) {
    fetch(`/includes/search-ajax.inc.php?q=${encodeURIComponent(searchTerm)}`)
        .then(response => response.json())
        .then(data => {
            const postsGrid = document.querySelector('.posts-grid');
            
            if (data.error || data.posts.length === 0) {
                postsGrid.innerHTML = `
                    <div class="no-posts">
                        <p>No posts found matching "${searchTerm}"</p>
                    </div>`;
                return;
            }
            
            // Update posts grid with search results
            postsGrid.innerHTML = data.posts.map(post => `
                <article class="post-card">
                    ${post.image ? `
                        <a href="${post.url}" class="post-thumbnail-link">
                            <img src="${post.image}" 
                                 alt="${post.title}"
                                 class="post-thumbnail">
                        </a>
                    ` : ''}
                    
                    <div class="post-content">
                        <h2>
                            <a href="${post.url}">${post.title}</a>
                        </h2>
                        
                        <div class="post-meta">
                            <span class="post-date">${post.date}</span>
                            <span class="post-author">by ${post.author}</span>
                            ${post.categories.length > 0 ? `
                                <span class="post-categories">
                                    ${post.categories.map(category => `
                                        <a class="article-category-tag" href="/category/${category.toLowerCase().replace(/\s+/g, '-')}">
                                            ${category}
                                        </a>
                                    `).join(' ')}
                                </span>
                            ` : ''}
                        </div>
                            
                        <div class="post-excerpt">
                            ${post.excerpt || ''}
                        </div>
                            
                        <a href="${post.url}" class="read-more">
                            Read More
                        </a>
                    </div>
                </article>
            `).join('');
            
            // Update URL with search term
            const url = new URL(window.location);
            url.searchParams.set('q', searchTerm);
            window.history.pushState({}, '', url);

            // Update pagination if needed
            if (data.pages > 1) {
                updateSearchPagination(data.currentPage, data.pages, searchTerm);
            } else {
                // Remove pagination if it exists
                const pagination = document.querySelector('.pagination');
                if (pagination) pagination.remove();
            }
        })
        .catch(error => {
            console.error('Search error:', error);
            document.querySelector('.posts-grid').innerHTML = `
                <div class="no-posts">
                    <p>An error occurred while searching. Please try again.</p>
                </div>
            `;
        })
        .finally(() => {
            document.getElementById('search-bar').classList.remove('loading');
        });
}

function updateSearchPagination(currentPage, totalPages, searchTerm) {
    const pagination = document.createElement('nav');
    pagination.className = 'pagination';
    
    if (currentPage > 1) {
        pagination.innerHTML += `
            <a href="?q=${searchTerm}&page=${currentPage - 1}" class="page-link">Previous</a>
        `;
    }
    
    for (let i = 1; i <= totalPages; i++) {
        if (
            i === 1 || 
            i === totalPages || 
            (i >= currentPage - 2 && i <= currentPage + 2)
        ) {
            pagination.innerHTML += `
                <a href="?q=${searchTerm}&page=${i}" 
                   class="page-link ${i === currentPage ? 'active' : ''}">${i}</a>
            `;
        } else if (
            i === currentPage - 3 || 
            i === currentPage + 3
        ) {
            pagination.innerHTML += '<span class="page-ellipsis">...</span>';
        }
    }
    
    if (currentPage < totalPages) {
        pagination.innerHTML += `
            <a href="?q=${searchTerm}&page=${currentPage + 1}" class="page-link">Next</a>
        `;
    }
    
    const existingPagination = document.querySelector('.pagination');
    if (existingPagination) {
        existingPagination.replaceWith(pagination);
    } else {
        document.querySelector('.blog-container').after(pagination);
    }
}
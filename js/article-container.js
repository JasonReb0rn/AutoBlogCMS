// article-container.js

let postsData;
let commentsData;
let repliesData;

// Wait for the DOM content to be fully loaded
document.addEventListener("DOMContentLoaded", function() {

    fetchPosts();

    var articleContainer = document.getElementById('blog-post-container');
    var postId = articleContainer.dataset.postId;

    // Find the form element
    var commentForm = document.getElementById('comment-form');

    // Check if commentForm exists before attaching the event listener
    if (commentForm) {
        // Attach an event listener for the form's submit event
        commentForm.addEventListener('submit', function(event) {
            // Get the post_id from the blog-post-container
            var postId = document.getElementById('blog-post-container').getAttribute('data-post-id');
            // Set the post_id value into the hidden input field within the form
            document.getElementById('form-post-id').value = postId;
            // Check if post_id is successfully set
            if (!postId) {
                // If post_id is not set, prevent the form from submitting
                event.preventDefault();
                console.error('post_id is not set. Form submission aborted.');
            }
        });
    }

    //TODO
    //ADD FUNCTION 'drawComments()' 

    fetchComments(postId);

    drawCategories();

});

/*
// Add an event listener to the search input field
document.getElementById('search-bar').addEventListener('input', function () {
    const searchTerm = this.value.toLowerCase();
    console.log('Search term:', searchTerm);
    drawPosts(searchTerm);
  });
*/

// Function to fetch posts from server
function fetchPosts() {
    if (shouldFetchNewPostData()) {
        console.log("Posts data not cached! Fetching new data.");
        let xhr = new XMLHttpRequest();
        xhr.open("GET", "../includes/get-blog-posts.inc.php", true);
        xhr.onreadystatechange = function () {
            if (xhr.readyState === 4 && xhr.status === 200) {
                // Store data and timestamp in local storage
                localStorage.setItem('postsData', xhr.responseText);
                localStorage.setItem('lastFetchTime', new Date().getTime().toString());
                handlePostsData(xhr.responseText);
            }
        };
        xhr.send();
    } else {
        // Use cached data
        console.log("Using cached posts data.");
        handlePostsData(localStorage.getItem('postsData'));
    }
}

// Helper function to check if data should be fetched
function shouldFetchNewPostData() {
    const lastFetch = localStorage.getItem('lastFetchTimeBlog');
    if (!lastFetch) {
        return true; // No data has been fetched before
    }
    const now = new Date().getTime();
    const oneHour = 1000 * 60 * 60; // milliseconds in one hour
    return now - parseInt(lastFetch) > oneHour; // Check if last fetch was more than an hour ago
}

// Function to handle posts data
function handlePostsData(response) {
    // Parse the JSON data into a JavaScript object
    postsData = JSON.parse(response);
}

function fetchComments(postId) {
    let xhr = new XMLHttpRequest();
    let url = "../includes/get-blog-comments.inc.php";
    // Append parameters to the URL
    if (postId) {
        url += "?postid=" + encodeURIComponent(postId);
    }
    xhr.open("GET", url, true);
    xhr.onreadystatechange = function () {
        if (xhr.readyState === 4 && xhr.status === 200) {
            // Store data and timestamp in local storage
            // localStorage.setItem('commentsData', xhr.responseText);
            // localStorage.setItem('lastFetchTime', new Date().getTime().toString());
            handleCommentsData(xhr.responseText);
        }
    };
    xhr.send();
}

// Function to handle posts data
function handleCommentsData(response) {
    // Parse the JSON data into a JavaScript object
    commentsData = JSON.parse(response);

    // Check if commentsData has at least 1 entry
    if (commentsData.length > 0) {
        drawComments(); // Call drawComments() if there's at least one entry
    }
}

function drawComments() {
    const commentsContainer = document.getElementById('comments');
    commentsContainer.innerHTML = ''; // Clear previous comments

    commentsData.forEach(comment => {
        // Create elements for the comment structure
        const commentContainer = document.createElement('div');
        commentContainer.classList.add('comment-container');

        const commentDiv = document.createElement('div');
        commentDiv.classList.add('comment');

        const commentMeta = document.createElement('div');
        commentMeta.classList.add('comment-meta');

        const profileImg = document.createElement('img');
        profileImg.src = 'https://champschance.s3.us-east-2.amazonaws.com/assets/avatars/' + comment.usersAvatar;
        profileImg.alt = 'Profile Photo';
        profileImg.classList.add('profile-img');

        const metaInfoContainer = document.createElement('div');
        metaInfoContainer.classList.add('meta-info-container');

        const authorSpan = document.createElement('span');
        authorSpan.classList.add('comment-author');
        authorSpan.textContent = comment.usersUid; // Update to match your data

        const dateSpan = document.createElement('span');
        dateSpan.classList.add('comment-date');
        dateSpan.textContent = comment.formatted_created_at; // Update to match your data

        const commentContent = document.createElement('div');
        commentContent.classList.add('comment-content');

        const commentText = document.createElement('p');
        commentText.textContent = comment.content; // Update to match your data

        // Append elements to their respective parent containers
        metaInfoContainer.appendChild(authorSpan);
        metaInfoContainer.appendChild(dateSpan);
        commentMeta.appendChild(profileImg);
        commentMeta.appendChild(metaInfoContainer);
        commentContent.appendChild(commentText);
        commentDiv.appendChild(commentMeta);
        commentDiv.appendChild(commentContent);
        commentContainer.appendChild(commentDiv);

        // Append comment container to comments container
        commentsContainer.appendChild(commentContainer);
    });
}


function drawCategories() {
    const categoriesSet = new Set(); // Using a Set to automatically deduplicate categories
    postsData.forEach(post => {
        categoriesSet.add(post.category);
    });

    const categoriesList = Array.from(categoriesSet); // Convert Set to array
    const formattedCategories = categoriesList.map(category => {
        // Formatting category (turning 'blog_post' into 'Blog Post')
        const words = category.split('_');
        const formattedCategory = words.map(word => word.charAt(0).toUpperCase() + word.slice(1)).join(' ');
        return formattedCategory;
    });

    const categoriesContainer = document.getElementById('blog-categories-list');
    categoriesContainer.innerHTML = ''; // Clear previous content

    formattedCategories.forEach(category => {
        const listItem = document.createElement('li');
        const link = document.createElement('a');
        link.href = '../blog.php?search=' + categoriesList[formattedCategories.indexOf(category)]; // Set the link with the original unformatted category
        link.textContent = category;
        // Add event listener to filter posts by category on click
        link.addEventListener('click', function () {
            drawPosts(category.toLowerCase().replace(/\s+/g, '_'));
        });
        listItem.appendChild(link);
        categoriesContainer.appendChild(listItem);
    });
}

// Function to format category
function formattedCategory(categoryString) {
    // Split the string by underscore
    const words = categoryString.split('_');
    // Capitalize the first letter of each word and join with a space
    const formattedCategory = words.map(word => word.charAt(0).toUpperCase() + word.slice(1)).join(' ');
    return formattedCategory;
}
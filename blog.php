<?php
require_once 'includes/dbh.inc.php';
require_once 'includes/cache-manager.inc.php';

// Set session cookie parameters to make it accessible across all directories
session_set_cookie_params(0, '/');
session_start();

// Pagination settings
$postsPerPage = 10;
$currentPage = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($currentPage - 1) * $postsPerPage;

try {
    // Get total number of published posts
    $stmt = $pdo->query("SELECT COUNT(*) FROM Posts WHERE Status = 'published'");
    $totalPosts = $stmt->fetchColumn();
    $totalPages = ceil($totalPosts / $postsPerPage);
    
    // Get posts for current page
    $stmt = $pdo->prepare("
        SELECT 
            p.PostID,
            p.Title,
            p.CreatedAt,
            p.FeaturedImage,
            p.FeaturedImageCaption,
            LEFT(p.Content, 300) as Excerpt,
            u.Username as AuthorName,
            GROUP_CONCAT(DISTINCT c.Name) as Categories
        FROM Posts p
        LEFT JOIN Users u ON p.UserID = u.UserID
        LEFT JOIN PostCategories pc ON p.PostID = pc.PostID
        LEFT JOIN Categories c ON pc.CategoryID = c.CategoryID
        WHERE p.Status = 'published'
        GROUP BY p.PostID
        ORDER BY p.CreatedAt DESC
        LIMIT ? OFFSET ?
    ");
    
    $stmt->execute([$postsPerPage, $offset]);
    $posts = $stmt->fetchAll();
    
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blog</title>
    <link rel="stylesheet" href="/css/blog-styles.css">
    <link rel="stylesheet" href="/style.css">
</head>
<body>
    <?php
        include_once 'header.php';
    ?>
    <script src="/js/breadcrumbs.js"></script>

    <div class="blog-header-container">
        <div class="blog-header">
            <h1>Blog Posts</h1>
        </div>
    </div>

    <div class="blog-container">

        <!-- Blog Posts -->
        <div class="posts-grid">
            <?php foreach ($posts as $post):
                // Create URL-friendly slug from title
                $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $post['Title'])));
            ?>
                <article class="post-card">
                    <?php if ($post['FeaturedImage']): ?>

                        <a href="/blog/post/<?php echo $post['PostID']; ?>/<?php echo $slug; ?>" class="post-thumbnail-link">
                            <img src="<?php echo htmlspecialchars($post['FeaturedImage']); ?>" 
                                 alt="<?php echo htmlspecialchars($post['Title']); ?>"
                                 class="post-thumbnail">
                        </a>

                    <?php endif; ?>
                    
                    <div class="post-content">
                        <h2>
                            <a href="/blog/post/<?php echo $post['PostID']; ?>/<?php echo $slug; ?>">
                                <?php echo htmlspecialchars($post['Title']); ?>
                            </a>
                        </h2>
                        
                        <div class="post-meta">
                            <span class="post-date">
                                <?php echo date('F j, Y', strtotime($post['CreatedAt'])); ?>
                            </span>
                            <span class="post-author">
                                by <?php echo htmlspecialchars($post['AuthorName']); ?>
                            </span>
                            <?php if ($post['Categories']): ?>
                                <span class="post-categories">
                                    <?php 
                                    $categories = explode(',', $post['Categories']);
                                    foreach ($categories as $index => $category): 
                                        $categorySlug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $category)));
                                    ?>
                                        <a class="article-category-tag" href="/category/<?php echo $categorySlug; ?>">
                                            <?php echo htmlspecialchars(trim($category)); ?>
                                        </a><?php echo $index < count($categories) - 1 ? ' ' : ''; ?>
                                    <?php endforeach; ?>
                                </span>
                            <?php endif; ?>
                        </div>
                        
                        <div class="post-excerpt">
                            <?php 
                            // Clean the excerpt and add ellipsis
                            $excerpt = $post['Excerpt'];
                            // Add spaces around block-level elements
                            $excerpt = preg_replace('/<\/?(?:p|div|h[1-6]|ul|ol|li|blockquote|pre|table|tr|th|td)[^>]*>/i', ' $0 ', $excerpt);
                            // Replace <br>, <br/>, and <br /> tags with a space
                            $excerpt = preg_replace('/<br\s*\/?>/i', ' ', $excerpt);
                            // Remove all HTML tags
                            $excerpt = strip_tags($excerpt);
                            // Replace multiple spaces (including newlines and tabs) with a single space
                            $excerpt = preg_replace('/\s+/', ' ', $excerpt);
                            // Trim whitespace
                            $excerpt = trim($excerpt);

                            // Check if the excerpt ends with a complete sentence
                            $endsWithSentence = preg_match('/[.!?]\s*$/', $excerpt);
                            // Check if the last word is complete (not cut off)
                            $lastChar = substr($excerpt, -1);
                            $endsWithCompleteWord = preg_match('/\s/', $lastChar) || preg_match('/[.!?,]/', $lastChar);

                            // Add ellipsis if the excerpt doesn't end naturally
                            if (!$endsWithSentence || !$endsWithCompleteWord) {
                                // Find the last complete word
                                $pos = strrpos($excerpt, ' ');
                                if ($pos !== false) {
                                    $excerpt = substr($excerpt, 0, $pos) . '...';
                                } else {
                                    $excerpt .= '...';
                                }
                            }

                            echo $excerpt;
                            ?>
                        </div>
                        
                        <a href="/blog/post/<?php echo $post['PostID']; ?>/<?php echo $slug; ?>" class="read-more">
                            Read More
                        </a>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>

        <!-- Sidebar -->
        <div class="blog-sidebar">

            <!-- Search -->
            <div class="blog-search-container">
               <input type="text" id="search-bar" placeholder="Search...">
            </div>

            <!-- Categories -->
            <div class="blog-categories">
                <h2>Categories</h2>
                <ul class="blog-categories-list">
                    <?php 
                    require_once 'includes/category-functions.inc.php';
                    $categories = getCategoriesWithPostCount($pdo);
                    foreach ($categories as $category): 
                        $slug = htmlspecialchars($category['Slug']);
                        $name = htmlspecialchars($category['Name']);
                        $count = (int)$category['post_count'];
                    ?>
                        <li class="blog-category">
                            <a class="category-link" href="/category/<?php echo $slug; ?>">
                                <span class="category-name"><?php echo $name; ?></span> <span class="category-count"><?php echo $count; ?></span>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <!-- Ad Box -->
            <div class="sidebar-sponsor-container">
                <img src="https://champschance.s3.us-east-2.amazonaws.com/assets/sponsor_spot.png" alt="Ads support us so much!">
            </div>
        
        </div>


    </div>

    <?php if ($totalPages > 1): ?>
        <nav class="pagination">
            <?php if ($currentPage > 1): ?>
                <a href="/blog/page/<?php echo ($currentPage - 1); ?>" class="page-link">
                    Previous
                </a>
            <?php endif; ?>
            
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <?php if (
                    $i == 1 || 
                    $i == $totalPages || 
                    ($i >= $currentPage - 2 && $i <= $currentPage + 2)
                ): ?>
                    <a href="/blog/page/<?php echo $i; ?>"
                       class="page-link <?php echo $i === $currentPage ? 'active' : ''; ?>">
                        <?php echo $i; ?>
                    </a>
                <?php elseif (
                    $i == $currentPage - 3 || 
                    $i == $currentPage + 3
                ): ?>
                    <span class="page-ellipsis">...</span>
                <?php endif; ?>
            <?php endfor; ?>
            
            <?php if ($currentPage < $totalPages): ?>
                <a href="/blog/page/<?php echo ($currentPage + 1); ?>" class="page-link">
                    Next
                </a>
            <?php endif; ?>
        </nav>
    <?php endif; ?>

    <?php
        include_once 'footer.php';
    ?>
</body>
</html>
<?php
} catch (PDOException $e) {
    error_log("Error in blog.php: " . $e->getMessage());
    echo "An error occurred while loading the blog posts.";
}
?>
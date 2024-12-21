<?php
require_once 'includes/dbh.inc.php';
require_once 'includes/cache-manager.inc.php';
require_once 'includes/tag-functions.inc.php';

session_set_cookie_params(0, '/');
session_start();

$slug = $_GET['slug'] ?? '';
if (empty($slug)) {
    header('Location: /');
    exit;
}

// Pagination settings
$postsPerPage = 10;
$currentPage = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($currentPage - 1) * $postsPerPage;

try {
    // Get tag information
    $stmt = $pdo->prepare("SELECT * FROM Tags WHERE Slug = ?");
    $stmt->execute([$slug]);
    $tag = $stmt->fetch();
    
    if (!$tag) {
        header('HTTP/1.0 404 Not Found');
        exit('Tag not found');
    }

    // Check for search query
    $searchTerm = $_GET['q'] ?? '';
    if (!empty($searchTerm)) {
        // Use the search handler with tag filter
        require_once 'includes/search-handler.inc.php';
        $searchResults = getSearchResults($pdo, $searchTerm, $currentPage, $postsPerPage, [
            'tagId' => $tag['TagID']
        ]);
        $posts = $searchResults['posts'];
        $totalPosts = $searchResults['total'];
        $totalPages = $searchResults['pages'];
    } else {
        // Original tag posts query code
        $stmt = $pdo->prepare("
            SELECT COUNT(DISTINCT p.PostID) 
            FROM Posts p
            JOIN PostTags pt ON p.PostID = pt.PostID
            WHERE pt.TagID = ? AND p.Status = 'published'
        ");
        $stmt->execute([$tag['TagID']]);
        $totalPosts = $stmt->fetchColumn();
        $totalPages = ceil($totalPosts / $postsPerPage);
    }
    
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
            GROUP_CONCAT(DISTINCT c.Name) as Categories,
            GROUP_CONCAT(DISTINCT t.Name) as Tags
        FROM Posts p
        LEFT JOIN Users u ON p.UserID = u.UserID
        LEFT JOIN PostCategories pc ON p.PostID = pc.PostID
        LEFT JOIN Categories c ON pc.CategoryID = c.CategoryID
        LEFT JOIN PostTags pt ON p.PostID = pt.PostID
        LEFT JOIN Tags t ON pt.TagID = t.TagID
        WHERE p.Status = 'published' AND pt.TagID = ?
        GROUP BY p.PostID
        ORDER BY p.CreatedAt DESC
        LIMIT ? OFFSET ?
    ");
    
    $stmt->execute([$tag['TagID'], $postsPerPage, $offset]);
    $posts = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Posts tagged with: <?php echo htmlspecialchars($tag['Name']); ?></title>
    <link rel="stylesheet" href="/css/blog-styles.css">
    <link rel="stylesheet" href="/style.css">
</head>
<body>
    <?php include_once 'header.php'; ?>
    
    <script src="/js/breadcrumbs.js"></script>

    <div class="blog-header-container">
        <div class="blog-header">
            <h1>Posts tagged with: <?php echo htmlspecialchars($tag['Name']); ?></h1>
        </div>
    </div>

    <div class="blog-container">
        <!-- Posts Grid -->
        <div class="posts-grid">
            <?php if (empty($posts)): ?>
                <div class="no-posts">
                    <p>No posts found with this tag.</p>
                </div>
            <?php else: ?>
                <?php foreach ($posts as $post):
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
                                <?php if ($post['Tags']): ?>
                                    <span class="post-tags">
                                        <?php 
                                        $tags = explode(',', $post['Tags']);
                                        foreach ($tags as $index => $t): 
                                            $tagSlug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $t)));
                                        ?>
                                            <a class="tag" href="/tag/<?php echo $tagSlug; ?>">
                                                <?php echo htmlspecialchars(trim($t)); ?>
                                            </a><?php echo $index < count($tags) - 1 ? ' ' : ''; ?>
                                        <?php endforeach; ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                            
                            <div class="post-excerpt">
                                <?php 
                                $excerpt = strip_tags($post['Excerpt']);
                                if (strlen($excerpt) >= 300) {
                                    $excerpt = substr($excerpt, 0, strrpos(substr($excerpt, 0, 300), ' ')) . '...';
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
            <?php endif; ?>
        </div>

        <!-- Sidebar -->
        <div class="blog-sidebar">
            <!-- Search -->
            <div class="blog-search-container">
               <input type="text" id="search-bar" placeholder="Search...">
            </div>

            <!-- Tags -->
            <div class="blog-categories">
                <h2>Popular Tags</h2>
                <div class="tag-cloud">
                    <?php 
                    $tags = getTagsWithPostCount($pdo);
                    foreach ($tags as $t): 
                        $tagSlug = htmlspecialchars($t['Slug']);
                        $name = htmlspecialchars($t['Name']);
                        $count = (int)$t['post_count'];
                        $isActive = $t['TagID'] === $tag['TagID'];
                    ?>
                        <a href="/tag/<?php echo $tagSlug; ?>" 
                           class="tag <?php echo $isActive ? 'active' : ''; ?>">
                            <?php echo $name; ?> (<?php echo $count; ?>)
                        </a>
                    <?php endforeach; ?>
                </div>
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
                                <span class="category-name"><?php echo $name; ?></span>
                                <span class="category-count"><?php echo $count; ?></span>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <!-- Ad Box -->
            <div class="sidebar-sponsor-container">
                <img src="/img/sponsor_spot.png" alt="Ads support us so much!">
            </div>
        </div>
    </div>

    <?php if ($totalPages > 1): ?>
        <nav class="pagination">
            <?php if ($currentPage > 1): ?>
                <a href="/tag/<?php echo $tag['Slug']; ?>/page/<?php echo ($currentPage - 1); ?>" class="page-link">Previous</a>
            <?php endif; ?>
            
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <?php if (
                    $i == 1 || 
                    $i == $totalPages || 
                    ($i >= $currentPage - 2 && $i <= $currentPage + 2)
                ): ?>
                    <a href="/tag/<?php echo $tag['Slug']; ?>/page/<?php echo $i; ?>" 
                       class="page-link <?php echo $i === $currentPage ? 'active' : ''; ?>"><?php echo $i; ?></a>
                <?php elseif (
                    $i == $currentPage - 3 || 
                    $i == $currentPage + 3
                ): ?>
                    <span class="page-ellipsis">...</span>
                <?php endif; ?>
            <?php endfor; ?>
            
            <?php if ($currentPage < $totalPages): ?>
                <a href="/tag/<?php echo $tag['Slug']; ?>/page/<?php echo ($currentPage + 1); ?>" class="page-link">Next</a>
            <?php endif; ?>
        </nav>
    <?php endif; ?>

    <?php include_once 'footer.php'; ?>
    <script src="/js/search.js"></script>
</body>
</html>
<?php
} catch (PDOException $e) {
    error_log("Error in tag.php: " . $e->getMessage());
    echo "An error occurred while loading the tagged posts.";
}
?>
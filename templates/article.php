<!-- templates/post.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($post['Title']); ?></title>
    <link rel="stylesheet" href="/css/blog-styles.css">
    <link rel="stylesheet" href="/style.css">
</head>
<body>
    <!-- PRESERVED_INCLUDE_START: header.php -->
    <?php include_once 'header.php'; ?>
    <!-- PRESERVED_INCLUDE_END -->
    
    <script src="/js/breadcrumbs.js"></script>

    <div class="blog-container">

        <article class="post">
            <div class="blog-post-header">
                <h1><?php echo htmlspecialchars($post['Title']); ?></h1>
                <div class="meta">
                    Posted on <?php echo date('F j, Y', strtotime($post['CreatedAt'])); ?>
                    in 
                    <?php foreach ($categories as $category): ?>
                        <a class="article-category-tag" href="/category/<?php echo htmlspecialchars($category['Slug']); ?>">
                            <?php echo htmlspecialchars($category['Name']); ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
                    
            <?php if ($post['FeaturedImage']): ?>
                <figure class="blog-image-wrapper full center" <?php echo !empty($post['FeaturedImageCaption']) ? 'data-enlargeable="true"' : ''; ?>>
                    <img src="<?php echo htmlspecialchars($post['FeaturedImage']); ?>" 
                         alt="<?php echo htmlspecialchars($post['Title']); ?>"
                         class="blog-content-image">
                    <?php if (!empty($post['FeaturedImageCaption'])): ?>
                        <figcaption class="blog-image-caption">
                            <?php echo htmlspecialchars($post['FeaturedImageCaption']); ?>
                        </figcaption>
                    <?php endif; ?>
                </figure>
            <?php endif; ?>
            
            <div class="content">
                <?php echo $post['Content']; ?>
            </div>
            
            <?php if (!empty($tags)): ?>
                <div class="tags">
                    Tags:
                    <?php foreach ($tags as $tag): ?>
                        <a href="/tag/<?php echo htmlspecialchars($tag['Slug']); ?>"
                           class="tag"><?php echo htmlspecialchars($tag['Name']); ?></a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </article>

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
                    // Get categories with post counts
                    require_once ROOT_PATH . 'includes/category-functions.inc.php';
                    $sidebarCategories = getCategoriesWithPostCount($pdo);
                    foreach ($sidebarCategories as $category): 
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

    
    
    <!-- PRESERVED_INCLUDE_START: footer.php -->
    <?php include_once 'footer.php'; ?>
    <!-- PRESERVED_INCLUDE_END -->

    <script src="/js/image-modal.js"></script>
</body>
</html>
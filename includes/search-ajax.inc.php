<?php
require_once 'dbh.inc.php';
require_once 'search-handler.inc.php';

header('Content-Type: application/json');

$searchTerm = $_GET['q'] ?? '';
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;

if (empty($searchTerm)) {
    echo json_encode(['error' => 'No search term provided']);
    exit;
}

$results = getSearchResults($pdo, $searchTerm, $page);

// Debug output
error_log("Search term: " . $searchTerm);
error_log("Results found: " . count($results['posts']));

// Format the results for JSON output
$formattedResults = [
    'posts' => array_map(function($post) {
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $post['Title'])));
        
        // Create excerpt from Content
        $excerpt = strip_tags($post['Content']);
        if (strlen($excerpt) > 300) {
            $excerpt = substr($excerpt, 0, 300);
            $excerpt = substr($excerpt, 0, strrpos($excerpt, ' ')) . '...';
        }
        
        return [
            'id' => $post['PostID'],
            'title' => $post['Title'],
            'excerpt' => $excerpt, // Use our generated excerpt
            'author' => $post['AuthorName'],
            'date' => date('F j, Y', strtotime($post['CreatedAt'])),
            'image' => $post['FeaturedImage'],
            'imageCaption' => $post['FeaturedImageCaption'],
            'categories' => $post['Categories'] ? explode(',', $post['Categories']) : [],
            'tags' => [], // Default to empty array since we don't have tags yet
            'url' => "/blog/post/{$post['PostID']}/$slug"
        ];
    }, $results['posts']),
    'total' => $results['total'],
    'pages' => $results['pages'],
    'currentPage' => $page
];

// Debug the output
error_log("Formatted results: " . json_encode($formattedResults));

echo json_encode($formattedResults);
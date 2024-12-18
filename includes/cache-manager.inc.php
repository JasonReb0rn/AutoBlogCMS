<?php
class CacheManager {
    private $cachePath;
    private $templatePath;
    private $cacheTime = 3600; // Cache lifetime in seconds (1 hour)
    
    public function __construct($cachePath = 'cache/posts/', $templatePath = 'templates/') {
        $this->cachePath = rtrim($cachePath, '/') . '/';
        $this->templatePath = rtrim($templatePath, '/') . '/';
        
        if (!file_exists($this->cachePath)) {
            mkdir($this->cachePath, 0777, true);
        }
    }
    
    public function getCacheKey($postId, $data = []) {
        $key = $postId;
        if (!empty($data)) {
            $key .= '_' . md5(serialize($data));
        }
        return $this->cachePath . $key . '.php';
    }
    
    public function get($postId, $data = []) {
        $cacheFile = $this->getCacheKey($postId, $data);
        
        if ($this->isValidCache($cacheFile)) {
            header('X-Cache: HIT');
            return file_get_contents($cacheFile);
        }
        
        header('X-Cache: MISS');
        return null;
    }
    
    public function set($postId, $content, $data = []) {
        $cacheFile = $this->getCacheKey($postId, $data);
        
        // Add root path constant if it doesn't exist
        $content = "<?php\nif (!defined('ROOT_PATH')) { define('ROOT_PATH', dirname(__DIR__) . '/'); }\n?>\n" . $content;
        
        // Process the content to keep PHP tags intact with correct paths
        $content = preg_replace_callback(
            '/<\!-- PRESERVED_INCLUDE_START: (.*?) -->(.*?)<\!-- PRESERVED_INCLUDE_END -->/s',
            function($matches) {
                return "<?php include_once ROOT_PATH . '{$matches[1]}'; ?>";
            },
            $content
        );
        
        // Process session start
        $content = preg_replace(
            '/<\!-- PRESERVED_SESSION_START -->(.*?)<\!-- PRESERVED_SESSION_END -->/s',
            "<?php\nif (session_status() == PHP_SESSION_NONE) {\n    session_set_cookie_params(0, '/');\n    session_start();\n}\n?>",
            $content
        );
        
        file_put_contents($cacheFile, $content);
        chmod($cacheFile, 0644);
    }
    
    public function invalidate($postId = null) {
        if ($postId === null) {
            array_map('unlink', glob($this->cachePath . '*.php'));
        } else {
            array_map('unlink', glob($this->cachePath . $postId . '*.php'));
        }
    }
    
    private function isValidCache($cacheFile) {
        if (!file_exists($cacheFile)) {
            return false;
        }
        
        $cacheTime = filemtime($cacheFile);
        $templateTime = filemtime($this->templatePath . 'article.php');
        $headerTime = file_exists('header.php') ? filemtime('header.php') : 0;
        $footerTime = file_exists('footer.php') ? filemtime('footer.php') : 0;
        
        if (time() - $cacheTime > $this->cacheTime) {
            return false;
        }
        
        if ($templateTime > $cacheTime || $headerTime > $cacheTime || $footerTime > $cacheTime) {
            return false;
        }
        
        return true;
    }
    
    public function setCacheTime($seconds) {
        $this->cacheTime = $seconds;
    }
}
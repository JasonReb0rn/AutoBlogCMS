<?php
function sanitizeHtml($content) {
    // No need to require HTMLPurifier directly as it's autoloaded through Composer
    $config = HTMLPurifier_Config::createDefault();
    
    // Allow iframes for YouTube
    $config->set('HTML.SafeIframe', true);
    $config->set('URI.SafeIframeRegexp', '%^https://(www\.)?youtube\.com/embed/%');
    
    // Allow div with specific classes for our custom elements
    $config->set('HTML.Allowed', 'p,b,i,u,strong,em,div[class],figure[class|data-enlargeable],img[src|alt|class],figcaption[class],iframe[src|frameborder|allowfullscreen|width|height],a[href|title|class],ul,ol,li,br');
    
    $purifier = new HTMLPurifier($config);
    return $purifier->purify($content);
}
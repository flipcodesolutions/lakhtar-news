<?php

$publicPath = getcwd();

$uri = urldecode(
    parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? ''
);

// Deep link /news?id=... must reach Laravel (public/news is the uploads folder).
if ($uri === '/news' || $uri === '/news/') {
    require_once $publicPath.'/index.php';

    return true;
}

// Serve uploaded media files from /news/filename.ext normally.
if (str_starts_with($uri, '/news/') && is_file($publicPath.$uri)) {
    return false;
}

if ($uri !== '/' && file_exists($publicPath.$uri)) {
    return false;
}

require_once $publicPath.'/index.php';

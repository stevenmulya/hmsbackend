<?php

$uri = urldecode(
    parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH)
);

// Jika yang diminta adalah file fisik (gambar/css) di public, biarkan lewat
if ($uri !== '/' && file_exists(__DIR__.'/public'.$uri)) {
    return false;
}

// Oper ke index.php asli milik Laravel
require_once __DIR__.'/public/index.php';
<?php
// Simple sync script to copy HTML/CSS from theme/admin and theme/front into public/admin-theme and public/theme
$root = realpath(__DIR__ . '/..');
$sourceAdmin = "$root/theme/admin";
$sourceFront = "$root/theme/front";
$destAdmin = "$root/public/admin-theme";
$destFront = "$root/public/theme";

function isPopulated($dir) {
    if (!is_dir($dir)) return false;
    $items = array_diff(scandir($dir), ['.', '..']);
    return !empty($items);
}

function copyDirectory($src, $dst) {
    if (!is_dir($src)) {
        echo "Source directory $src not found.\n";
        return;
    }
    if (!isPopulated($src)) {
        echo "Source directory $src is empty. Ensure you're on the branch containing the uploaded theme files (e.g., main) or copy the HTML assets into place.\n";
        return;
    }
    if (!is_dir($dst)) {
        mkdir($dst, 0777, true);
    }
    $items = scandir($src);
    foreach ($items as $item) {
        if ($item === '.' || $item === '..') continue;
        $srcPath = $src . DIRECTORY_SEPARATOR . $item;
        $dstPath = $dst . DIRECTORY_SEPARATOR . $item;
        if (is_dir($srcPath)) {
            copyDirectory($srcPath, $dstPath);
        } else {
            copy($srcPath, $dstPath);
        }
    }
}

copyDirectory($sourceAdmin, $destAdmin);
copyDirectory($sourceFront, $destFront);

echo "Synced theme assets.\n";

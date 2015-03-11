<?php

require dirname(__FILE__) . '/Data.php';

if (isset($_SERVER['argv']) && ($_SERVER['argv'][0] == $_SERVER['PHP_SELF'])) {
    $dir = "E:/Dropbox/fileTemplates";
    foreach (scandir($dir) as $file) {
        $path = $dir . '/' . $file;
        if (!is_file($path)) continue;
        echo str_pad($file, 24), "\t", Zefram_File_MimeType_Data::detect($path), "\n";
    }
}

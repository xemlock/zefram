#!/usr/bin/env php
<?php

chdir(__DIR__ . '/..');

patch_file('vendor/phpunit/phpunit/src/Framework/TestCase.php', array(
    // Remove return void annotations
    '/\):\s*void(\s*)\{/' => ')$1{',
));

patch_file('vendor/phpunit/phpunit/src/Framework/Assert.php', array(
    // Remove return void annotations
    '/\):\s*void(\s*)\{/' => ')$1{',
    // Remove string type annotation from parameters
    '/\bstring (\$[_A-Za-z][_A-Za-z0-9]*)\b/' => '$1',
));

function patch_file($file, array $replacements) {
    if (file_exists($file)) {
        $contents = file_get_contents($file);
        $patchedContents = preg_replace(array_keys($replacements), array_values($replacements), $contents);

        if ($contents !== $patchedContents) {
            file_put_contents($file, $patchedContents);
            echo "Patched file: {$file}\n";
        } else {
            echo "No changes made: {$file}\n";
        }
    } else {
        echo "File not found: {$file}\n";
    }
}

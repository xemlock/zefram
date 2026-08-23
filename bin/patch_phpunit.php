#!/usr/bin/env php
<?php

chdir(__DIR__ . '/..');

patch_file('vendor/phpunit/phpunit/src/Framework/TestCase.php', array(
    // Remove return type annotation from setUp() and tearDown(), to allow test cases
    // to be executed across phpunit versions
    '/protected function (setUp|tearDown)\(\): void/' => 'protected function $1()',
));

patch_file('vendor/phpunit/phpunit/src/Framework/Assert.php', array(
    // Remove return void annotations
    '/\):\s*void(\s*)\{/' => ')$1{',
    // Remove type annotation from $message parameters
    '/\bstring \$message/' => '$message',
));

function patch_file($file, array $replacements) {
    if (file_exists($file)) {
        $contents = file_get_contents($file);
        $patchedContents = preg_replace(array_keys($replacements), array_values($replacements), $contents);

        if ($contents !== $patchedContents) {
            file_put_contents($file, $patchedContents);
            echo "Patched file {$file}\n";
        } else {
            echo "No changes made\n";
        }
    } else {
        echo "File not found: {$file}\n";
    }
}

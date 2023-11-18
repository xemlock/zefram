#!/usr/bin/env php
<?php

$ROOT_DIR = realpath(__DIR__ . '/..');
$TESTCASE_RELPATH = 'vendor/phpunit/phpunit/src/Framework/TestCase.php';
$TESTCASE_PATH = $ROOT_DIR . '/' . $TESTCASE_RELPATH;

if (file_exists($TESTCASE_PATH)) {
    // Remove return type annotation from setUp() and tearDown(), to allow test cases
    // to be executed in phpunit >= 8.* versions
    $contents = file_get_contents($TESTCASE_PATH);
    $patchedContents = preg_replace('/protected function (setUp|tearDown)\(\): void/', 'protected function $1()', $contents);

    if ($contents !== $patchedContents) {
        file_put_contents($TESTCASE_PATH, $patchedContents);
        echo "Patched file {$TESTCASE_RELPATH}\n";
    } else {
        echo "No changes made\n";
    }
} else {
    echo "File not found: {$TESTCASE_RELPATH}\n";
}

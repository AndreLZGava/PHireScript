<?php
require 'vendor/autoload.php';

use PHPScript\Transpiler;

$transpiler = new Transpiler();

$file = $argv[1];

if (!file_exists($file)) {
    die("File not found: $file\n");
}

$code = file_get_contents($file);

try {
    $result = $transpiler->compile($code);
    echo "\n--- SUCCESSFUL PHP OUTPUT ---\n";
} catch (\Exception $e) {
    echo "\n--- ERROR ---\n";
    echo $code . "\n";
    echo $e->getMessage() . "\n";
}

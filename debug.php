<?php
require 'vendor/autoload.php';

use App\Transpiler;

$transpiler = new Transpiler();

// Caminho do arquivo que você quer testar
$file = $argv[1] ?? 'src/compile/test/Variables.ps';

if (!file_exists($file)) {
    die("Arquivo não encontrado: $file\n");
}

$code = file_get_contents($file);

//echo "\n--- SOURCE PHPSCRIPT ---\n";
//echo $code . "\n";

try {
    $result = $transpiler->compile($code);
    //echo "\n--- SUCCESSFUL PHP OUTPUT ---\n";
    //echo $result . "\n";
} catch (\Exception $e) {
    // O seu Transpiler já imprime o "DEBUG (Generated Code)" no catch
    echo "\n--- ERROR ---\n";
    echo $e->getMessage() . "\n";
}

<?php

$html = "<!DOCTYPE html>
<html>
<head>
<meta charset='UTF-8'>
<title>PHP Internal API</title>
<style>
    body { font-family: monospace; background: #111; color: #eee; }
    table { border-collapse: collapse; width: 100%; }
    th, td { border: 1px solid #333; padding: 6px; }
    th { background: #222; }
    a { color: #4da6ff; text-decoration: none; }
    .deprecated { color: #ff6b6b; font-weight: bold; }
</style>
</head>
<body>

<h1>PHP Internal API</h1>
<table>
<thead>
<tr>
    <th>Type</th>
    <th>Name</th>
    <th>Deprecated</th>
</tr>
</thead>
<tbody>
";

//
// ✅ FUNÇÕES
//

$functions = get_defined_functions()['internal'];
sort($functions);
$count = 0;
foreach ($functions as $func) {
    $ref = new ReflectionFunction($func);

    $deprecated = $ref->isDeprecated()
    ? "<span class='deprecated'>YES 😈</span>"
    : "NO";

    $docUrl = "https://www.php.net/{$func}";
    $count++;
    $html .= "
    <tr>
        <td>Function</td>
        <td><a href='{$docUrl}' target='_blank'>{$func}</a></td>
        <td>{$deprecated}</td>
    </tr>";
}

//
// ✅ CLASSES + MÉTODOS
//

$classes = get_declared_classes();
foreach ($classes as $class) {
    $refClass = new ReflectionClass($class);

    if (!$refClass->isInternal()) {
        continue;
    }

    foreach ($refClass->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
        $name = $class . "::" . $method->getName();

        $deprecated = $method->isDeprecated()
        ? "<span class='deprecated'>YES 😈</span>"
        : "NO";

      // Doc link padrão PHP
        $docUrl = "https://www.php.net/manual/en/class." . strtolower($class) . ".php";
        $count++;
        $html .= "
    <tr>
    <td>Method</td>
    <td><a href='{$docUrl}' target='_blank'>{$name}</a></td>
    <td>{$deprecated}</td>
    </tr>";
    }
}

$html .= "</tbody></table></body><h1>{$count}</h2></html>";

file_put_contents('php_api.html', $html);

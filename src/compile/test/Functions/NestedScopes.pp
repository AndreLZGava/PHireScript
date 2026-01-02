<?php
$item = "global";

function outer() {
    $item = ["list"];

    function inner() {
        $item = 10;
        echo $item; // INT
    };

    $item->push("nested"); // Deve ser ARRAY
    inner();
};

$config = ["user" => ["name" => "André","id" => 1     ],"active" => true ];

echo $item->toUpperCase(); // Deve ser STRING

<?php
$data = ["apple", "banana"]; // Global: ARRAY

function resetData() {
    $data = "cleared"; // Local: STRING ($Shadowing)
    echo $data->toUpperCase(); // Deve virar strtoupper($data)
};

$data->push("orange"); // Deve virar array_push($data,  "orange")
resetData();
echo $data->join(" - "); // Deve virar implode(" - ",  $data)

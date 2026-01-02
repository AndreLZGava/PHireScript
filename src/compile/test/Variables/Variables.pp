<?php
$price = 19.90;
$taxa = 1.05;
$total = $price * $taxa;

$user =(object) [];
$user->nome = "William";

echo "Total value for user: " . $user->nome->toUpperCase() . " is: " . $total;

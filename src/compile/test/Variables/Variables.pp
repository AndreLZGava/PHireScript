<?php
$price = 19.90;
$income = 1.05;
$total = $price * $income;

$user =(object) [];
$user->name = "William";

echo "Total value for user: " . $user->name->toUpperCase() . " is: " . $total;

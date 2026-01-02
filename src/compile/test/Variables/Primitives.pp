<?php
// --- 1-> String Type ---
$userName = "André"; // Inference: string
$idAsString = (string)(12345); // Casting: becomes "12345"

// --- 2-> Int Type ---
$userAge = 25; // Inference: int
$ageFromText = (int)("30"); // Casting: becomes 30

// --- 3-> Float Type ---
$productPrice = 250.99; // Inference: float
$taxValue = (float)("0.15"); // Casting: becomes 0.15

// --- 4-> Bool Type ---
$isUserActive = true; // Inference: bool
$statusFromBinary = (bool)(1); // Casting: becomes true

// --- 5-> Array Type ---
$techStack = ["PHP", "PS", "TS"]; // Inference: array
$singleItemArray = (array)($userName); // Casting: becomes ["André"]

// --- 6-> Object Type ---
$dataContainer = ["id" => 1]; // Inference: object
$objFromMap = (object)(['id' => 1]); // Casting: becomes object with property id

$myObject = ["test" => "test"];


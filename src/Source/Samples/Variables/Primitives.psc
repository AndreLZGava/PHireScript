<?php


    // --- 1. String Type ---

$userName = "André";

    // Inference: String

    // --- 2. Int Type ---

$userAge = 25;

    // Inference: Int

    // --- 3. Float Type ---

$productPrice = 250.99;

    // Inference: Float

    // --- 4. Bool Type ---

$isUserActive = true;

    // Inference: Bool

    // --- 5. Array Type ---

$techStack = ["PHP", "PS", "TS"];

    // --- 6. Object Type ---

$dataContainer = (object) [id => 1];

$myObject = (object) [test => "test"];

    /**
idAsString = String(12345)                 // Casting: becomes "12345"
ageFromText = Int("30")                    // Casting: becomes 30
taxValue = Float("0.15")                   // Casting: becomes 0.15
statusFromBinary = Bool(1)                 // Casting: becomes true
singleItemArray = Array(userName)          // Casting: becomes ["André"]
objFromMap = Object(['id' => 1])           // Casting: becomes object with property id
*/

    /**
idAsString = String(12345)                 // Casting: becomes "12345"
ageFromText = Int("30")                    // Casting: becomes 30
taxValue = Float("0.15")                   // Casting: becomes 0.15
statusFromBinary = Bool(1)                 // Casting: becomes true
singleItemArray = Array(userName)          // Casting: becomes ["André"]
objFromMap = Object(['id' => 1])           // Casting: becomes object with property id
*/


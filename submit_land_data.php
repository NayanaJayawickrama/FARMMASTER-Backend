<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: POST");
header("Content-Type: application/json");

$data = json_decode(file_get_contents("php://input"), true);

if ($data) {
    $ph = floatval($data['phValue'] ?? 0);
    $organic = floatval($data['organicMatter'] ?? 0);
    $nitrogen = floatval($data['nitrogen'] ?? 0);
    $phosphorus = floatval($data['phosphorus'] ?? 0);
    $potassium = floatval($data['potassium'] ?? 0);
    $notes = $data['notes'] ?? '';

    $crops = [];

    // Carrots
    if (
        $ph >= 5.5 && $ph <= 7.0 &&
        $organic >= 2.0 && $organic <= 3.5 &&
        $nitrogen >= 60 && $nitrogen <= 100 &&
        $phosphorus >= 30 && $phosphorus <= 50 &&
        $potassium >= 120 && $potassium <= 180
    ) {
        $crops[] = "Carrots";
    }

    // Cabbage
    if (
        $ph >= 6.0 && $ph <= 7.5 &&
        $organic >= 2.5 && $organic <= 4.0 &&
        $nitrogen >= 80 && $nitrogen <= 120 &&
        $phosphorus >= 40 && $phosphorus <= 60 &&
        $potassium >= 150 && $potassium <= 200
    ) {
        $crops[] = "Cabbage";
    }

    // Tomatoes
    if (
        $ph >= 5.5 && $ph <= 7.5 &&
        $organic >= 3.0 && $organic <= 5.0 &&
        $nitrogen >= 70 && $nitrogen <= 120 &&
        $phosphorus >= 35 && $phosphorus <= 55 &&
        $potassium >= 150 && $potassium <= 250
    ) {
        $crops[] = "Tomatoes";
    }

    // Leeks
    if (
        $ph >= 6.0 && $ph <= 8.0 &&
        $organic >= 2.5 && $organic <= 4.5 &&
        $nitrogen >= 60 && $nitrogen <= 100 &&
        $phosphorus >= 30 && $phosphorus <= 50 &&
        $potassium >= 120 && $potassium <= 180
    ) {
        $crops[] = "Leeks";
    }

    echo json_encode([
        "status" => "success",
        "recommendedCrops" => $crops,
        "data" => [
            "phValue" => $ph,
            "organicMatter" => $organic,
            "nitrogen" => $nitrogen,
            "phosphorus" => $phosphorus,
            "potassium" => $potassium,
            "notes" => $notes
        ]
    ]);
} else {
    echo json_encode([
        "status" => "error",
        "message" => "No data received."
    ]);
}

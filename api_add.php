<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method');
    }


    $data = json_decode(file_get_contents('php://input'), true);
    if (!$data || !isset($data['numer_seryjny'])) {
        echo json_encode(['success' => false, 'message' => 'Invalid data received']);
        exit;
    }

    // Połączenie z bazą danych
    $db = new SQLite3('przeglady_with_coordinates.db');

    if (!$db) {
        echo json_encode(['success' => false, 'message' => 'Unable to connect to the database']);
        exit;
    }

    // Przygotowanie i wykonanie zapytania
    $query = $db->prepare('INSERT INTO przeglady_updated (numer_seryjny, rodzaj_badania, obiekt, firma, lokalizacja, producent_stacji, model_stacji, przeglad_2025, protokol_2025) VALUES (:numer_seryjny, :rodzaj_badania, :obiekt, :firma, :lokalizacja, :producent_stacji, :model_stacji, :przeglad_2025, :protokol_2025)');
    
    $query->bindValue(':numer_seryjny', $data['numer_seryjny'], SQLITE3_TEXT);
    $query->bindValue(':rodzaj_badania', $data['rodzaj_badania'], SQLITE3_TEXT);
    $query->bindValue(':obiekt', $data['obiekt'], SQLITE3_TEXT);
    $query->bindValue(':firma', $data['firma'], SQLITE3_TEXT);
    $query->bindValue(':lokalizacja', $data['lokalizacja'], SQLITE3_TEXT);
    $query->bindValue(':producent_stacji', $data['producent_stacji'], SQLITE3_TEXT);
    $query->bindValue(':model_stacji', $data['model_stacji'], SQLITE3_TEXT);
    $query->bindValue(':przeglad_2025', $data['przeglad_2025'], SQLITE3_TEXT);
    $query->bindValue(':protokol_2025', $data['protokol_2025'], SQLITE3_TEXT);

    if ($result) {
        echo json_encode(['success' => true, 'message' => 'Record added successfully']);
    } else {
        throw new Exception('Failed to add record: ' . $db->lastErrorMsg());
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>

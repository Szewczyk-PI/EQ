<?php
header('Content-Type: application/json');

// Obsługa CORS - umożliwienie zapytań POST z różnych domen
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

// Sprawdź metodę zapytania
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    // Zwróć odpowiedź na zapytanie OPTIONS
    http_response_code(200);
    exit;
}

// Połączenie z bazą danych
$db = new SQLite3('./data/przeglady.db');

if (!$db) {
    echo json_encode(['error' => 'Unable to connect to the database.']);
    exit;
}

// Obsługa zapytań POST dla aktualizacji rekordu
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Odczytaj dane z żądania
    $data = json_decode(file_get_contents('php://input'), true);

    // Upewnij się, że dane są prawidłowe
    if (!$data || !isset($data['numer_seryjny'])) {
        echo json_encode(['success' => false, 'message' => 'Invalid data received']);
        exit;
    }

    // Proste zapytanie do aktualizacji rekordu
    $query = "UPDATE przeglady_updated SET rodzaj_badania = '{$data['rodzaj_badania']}', obiekt = '{$data['obiekt']}', firma = '{$data['firma']}', lokalizacja = '{$data['lokalizacja']}', producent_stacji = '{$data['producent_stacji']}', model_stacji = '{$data['model_stacji']}', przeglad_2022 = '{$data['przeglad_2022']}', przeglad_2023 = '{$data['przeglad_2023']}', przeglad_2024 = '{$data['przeglad_2024']}', przeglad_2025 = '{$data['przeglad_2025']}', protokol_2022 = '{$data['protokol_2022']}', protokol_2023 = '{$data['protokol_2023']}', protokol_2024 = '{$data['protokol_2024']}', protokol_2025 = '{$data['protokol_2025']}' WHERE numer_seryjny = '{$data['numer_seryjny']}'";

    $result = $db->exec($query);

    if ($result) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update record', 'error' => $db->lastErrorMsg()]);
    }
    
    $db->close();
    exit;
}

// Domyślne zapytanie GET do pobrania wszystkich rekordów
$query = 'SELECT * FROM przeglady_updated';
$results = $db->query($query);

// Inicjalizacja tablicy do przechowywania wyników
$records = [];

// Iteracja po wynikach i dodanie ich do tablicy
while ($row = $results->fetchArray(SQLITE3_ASSOC)) {
    $records[] = $row;
}

// Zwrócenie danych jako JSON
echo json_encode($records);

// Zamknięcie połączenia z bazą danych
$db->close();
?>

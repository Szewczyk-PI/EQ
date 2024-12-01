<?php
header('Content-Type: application/json');

// Obsługa CORS
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Połączenie z bazą danych
$db = new SQLite3('./data/przeglady.db');

if (!$db) {
    echo json_encode(['error' => 'Unable to connect to the database.']);
    exit;
}

// Obsługa zapytań POST - aktualizacja rekordu
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    if (!$data || !isset($data['numer_seryjny'])) {
        echo json_encode(['success' => false, 'message' => 'Invalid data received']);
        exit;
    }

    // Przygotowanie zapytania SQL do aktualizacji
    $query = $db->prepare("
    UPDATE przeglady_updated 
    SET latitude = :latitude, longitude = :longitude 
    WHERE numer_seryjny = :numer_seryjny
");

$query->bindValue(':latitude', $data['latitude'], SQLITE3_FLOAT);
$query->bindValue(':longitude', $data['longitude'], SQLITE3_FLOAT);
$query->bindValue(':numer_seryjny', $data['numer_seryjny'], SQLITE3_TEXT);

$result = $query->execute();


    if ($result) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update record', 'error' => $db->lastErrorMsg()]);
    }

    $db->close();
    exit;
}

// Obsługa zapytań GET - zwrócenie lokalizacji bez współrzędnych
$query = 'SELECT rowid, numer_seryjny, lokalizacja FROM przeglady_updated WHERE latitude IS NULL OR longitude IS NULL';
$results = $db->query($query);

$records = [];
while ($row = $results->fetchArray(SQLITE3_ASSOC)) {
    $records[] = $row;
}

// Zwrócenie wyników jako JSON
echo json_encode($records);

$db->close();
?>

<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");

// Sprawdzenie, czy skrypt jest uruchamiany w CLI (Command Line Interface)
if (php_sapi_name() === 'cli') {
    // Jeśli skrypt jest uruchamiany w CLI, wykonaj geokodowanie dla wszystkich rekordów bez współrzędnych
    echo json_encode(['message' => 'Running from CLI']);
} else {
    // W przeciwnym razie obsługa HTTP
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        echo json_encode(['success' => false, 'message' => 'Invalid request method']);
        exit;
    }
    echo json_encode(['message' => 'Running from HTTP request']);
}

// Połączenie z bazą danych
$db = new SQLite3('./data/przeglady.db');

// Pobranie wszystkich rekordów, które nie mają współrzędnych
$query = "SELECT rowid, numer_seryjny, lokalizacja FROM przeglady_updated WHERE latitude IS NULL OR longitude IS NULL";
$result = $db->query($query);
$data = [];

while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
    $data[] = $row;
}

// Przetwarzanie każdego rekordu i próba geokodowania lokalizacji
if (!empty($data)) {
    $geolocator = new NominatimGeoLocator(); // Zakładam, że masz odpowiednią klasę do geokodowania

    foreach ($data as $row) {
        try {
            $geo_location = $geolocator->geocode($row['lokalizacja']);
            if ($geo_location) {
                // Aktualizacja bazy danych współrzędnymi
                $update_query = $db->prepare("UPDATE przeglady_updated SET latitude = :lat, longitude = :lon WHERE rowid = :rowid");
                $update_query->bindValue(':lat', $geo_location['latitude'], SQLITE3_FLOAT);
                $update_query->bindValue(':lon', $geo_location['longitude'], SQLITE3_FLOAT);
                $update_query->bindValue(':rowid', $row['rowid'], SQLITE3_INTEGER);
                $update_query->execute();
                echo json_encode(['success' => true, 'message' => 'Coordinates updated for row: ' . $row['rowid']]);
            }
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Geocoding error for row: ' . $row['rowid'], 'error' => $e->getMessage()]);
        }
    }
} else {
    echo json_encode(['success' => false, 'message' => 'No records found without coordinates']);
}

// Zamknięcie połączenia z bazą danych
$db->close();
?>

<?php
// Przykład klasy geolokacji (geokodowania)
class NominatimGeoLocator
{
    private $endpoint = "https://nominatim.openstreetmap.org/search";

    public function geocode($location)
    {
        $url = $this->endpoint . "?format=json&q=" . urlencode($location);

        // Używamy cURL zamiast file_get_contents, aby dodać nagłówek User-Agent
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_USERAGENT, 'YourAppName/1.0 (your-email@example.com)');
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Opcjonalnie, wyłączenie weryfikacji SSL (niezalecane w produkcji)

        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            throw new Exception('cURL error: ' . curl_error($ch));
        }

        curl_close($ch);

        $data = json_decode($response, true);

        if (!empty($data) && isset($data[0]['lat'], $data[0]['lon'])) {
            return [
                'latitude' => $data[0]['lat'],
                'longitude' => $data[0]['lon']
            ];
        }

        throw new Exception('Unable to geocode location: ' . $location);
    }
}
?>

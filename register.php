<?php
// Datenbank-Verbindungsparameter anpassen
include ('.ht_cred.php');

$servername = "localhost";
$dbname     = "PoGoGifts";

// Verbindung herstellen
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Verbindungsfehler: " . $conn->connect_error);
}

// Debugging only
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


// Verzeichnis für Uploads (sollte existieren und beschreibbar sein)
$uploadDir = "uploads/";

// Formularauswertung, wenn das Formular abgeschickt wurde
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Eingabewerte auslesen und ggf. escapen
    $name        = $conn->real_escape_string($_POST['name']);
    $description = $conn->real_escape_string($_POST['description']);
    $friend      = $conn->real_escape_string($_POST['friend']);
    $latitude    = floatval($_POST['latitude']);
    $longitude   = floatval($_POST['longitude']);
    
    // Bilder verarbeiten (bis zu 4 Bilder)
    $imagePaths = array("", "", "", "");
    for ($i = 1; $i <= 4; $i++) {
        if (isset($_FILES["image$i"]) && $_FILES["image$i"]['error'] == UPLOAD_ERR_OK) {
            $tmpName = $_FILES["image$i"]['tmp_name'];
            // Überprüfen, ob es sich um ein Bild handelt
            if (getimagesize($tmpName) !== false) {
                $ext      = pathinfo($_FILES["image$i"]['name'], PATHINFO_EXTENSION);
                $filename = uniqid("img_", true) . "." . $ext;
                $destination = $uploadDir . $filename;
                if (move_uploaded_file($tmpName, $destination)) {
                    $imagePaths[$i - 1] = $destination;
                }
            }
        }
    }
    
    // Datensatz in die Datenbank einfügen
    $stmt = $conn->prepare("INSERT INTO GiftLoc (name, description, friend, latitude, longitude, image1, image2, image3, image4) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    if ($stmt === false) {
        die("Prepare-Fehler: " . $conn->error);
    }
    $stmt->bind_param("sssddssss", $name, $description, $friend, $latitude, $longitude, $imagePaths[0], $imagePaths[1], $imagePaths[2], $imagePaths[3]);
    
    if ($stmt->execute()) {
        echo "<p>Datensatz erfolgreich eingefügt!</p>";
    } else {
        echo "<p>Fehler: " . $stmt->error . "</p>";
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <!-- Meta-Tag für responsives Design -->
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Neue Postkare anlegen</title>
    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
    <link rel="stylesheet" href="pogo.css" />
</head>
<body>
    <h1>Geo-Daten Eingabe</h1>
    <form action="register.php" method="post" enctype="multipart/form-data">
        <label for="name">Name:</label><br>
        <input type="text" name="name" id="name" required><br><br>
        
        <label for="description">Beschreibung:</label><br>
        <textarea name="description" id="description" required></textarea><br><br>

        <label for="friend">Freund:</label><br>
        <input type="text" name="friend" id="friend" optional></textarea><br><br>
        <div class="form-row">
            <div>
              <!-- Die Koordinatenfelder werden hier als read-only angezeigt und per Karte aktualisiert -->
              <label for="latitude">Breitengrad (Latitude):</label><br>
              <input type="text" name="latitude" id="latitude" required readonly><br><br>
            </div>        
            <div>
                <label for="longitude">Längengrad (Longitude):</label><br>
                <input type="text" name="longitude" id="longitude" required readonly><br><br>
            </div>        
        </div>        
        <p>Verschiebe den Marker auf der Karte, um die exakten Koordinaten zu setzen:</p>
        <div id="map"></div>
        
        <br>
        <label>Bild 1:</label>
        <input type="file" name="image1" accept="image/*"><br><br>
        
        <label>Bild 2:</label>
        <input type="file" name="image2" accept="image/*"><br><br>
        
        <label>Bild 3:</label>
        <input type="file" name="image3" accept="image/*"><br><br>
        
        <label>Bild 4:</label>
        <input type="file" name="image4" accept="image/*"><br><br>
        
        <input type="submit" value="Speichern">
    </form>
    
    <!-- Leaflet JS -->
    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
    <script>
        // Standard-Koordinaten (z.B. Zentrum von Deutschland)
        var initialLat = 48.6666;
        var initialLng = 9.0;
        
        // Setze die initialen Werte in die Input-Felder
        document.getElementById('latitude').value = initialLat;
        document.getElementById('longitude').value = initialLng;
        
        // Initialisiere die Karte
        var map = L.map('map').setView([initialLat, initialLng], 6);
        
        // Füge den OpenStreetMap-Layer hinzu
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap-Mitwirkende'
        }).addTo(map);
        
        // Füge einen verschiebbaren Marker hinzu
        var marker = L.marker([initialLat, initialLng], { draggable: true }).addTo(map);
        
        // Wenn der Marker verschoben wird, aktualisiere die Koordinatenfelder
        marker.on('dragend', function(e) {
            var pos = marker.getLatLng();
            document.getElementById('latitude').value = pos.lat.toFixed(7);
            document.getElementById('longitude').value = pos.lng.toFixed(7);
        });
    </script>
</body>
</html>

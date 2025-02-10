<?php
include ('.ht_cred.php');

// Datenbank-Verbindungsparameter anpassen
$servername = "localhost";
$dbname     = "PoGoGifts";

// Verbindung herstellen
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Verbindungsfehler: " . $conn->connect_error);
}

/* Anlegen der SQL-Tabelle
CREATE TABLE `GiftLoc` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(255) NOT NULL,
  `description` TEXT NOT NULL,
  `friends` TEXT,
  `latitude` DECIMAL(10,7) NOT NULL,
  `longitude` DECIMAL(10,7) NOT NULL,
  `image1` VARCHAR(255) DEFAULT '',
  `image2` VARCHAR(255) DEFAULT '',
  `image3` VARCHAR(255) DEFAULT '',
  `image4` VARCHAR(255) DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
*/

// Debugging only
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);



$sql = "SELECT * FROM GiftLoc";
$result = $conn->query($sql);

$points = array();
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $points[] = $row;
    }
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <!-- Meta-Tag für responsives Design -->
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Pokemon Go Postkarten-Karte</title>
    <link rel='stylesheet' href="pogo.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
</head>
<body>
    <h1>Postkarten-Karte PokemonDad</h1>
    <a href="export.php">KML-Datei exportieren</a>
    <div id="map"></div>
    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
    <script>
    // Karte initialisieren (hier: zentriert über Deutschland)
    var map = L.map('map').setView([48.666, 9.0], 8);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap-Mitwirkende'
    }).addTo(map);
    
    // PHP-Daten als JSON verfügbar machen
    var points = <?php echo json_encode($points); ?>;
    // Ermittele die aktuelle Bildschirmbreite
    var fontSize = window.innerWidth < 1200 ? "26px" : "14px";    
    // Für jeden Punkt einen Marker hinzufügen
    points.forEach(function(point) {
        var marker = L.marker([point.latitude, point.longitude]).addTo(map);
        // Popup-Inhalt zusammenbauen (Name, Beschreibung und ggf. Bilder)
        var popupContent = "<div style='font-size:" + fontSize + ";'> <table><tr><td><strong>" + point.name + "</td></tr><tr></strong><td>" + point.description+"</td><td>" + point.friend+"</td></tr><tr>";
        popwidth=0;
        for (var i = 1; i <= 4; i++) {
            if (point["image" + i] !== "") {
                popupContent += "<td><img src='" + point["image" + i] + "' style='max-width:180px;'/></td>";
                popwidth+=200;
            }
        }
        popupContent += "</tr></table></div>";
        console.log(popupContent);
        // Optionen für das Popup definieren (z.B. maximale Breite auf 300px setzen)
        var popupOptions = {
        maxWidth: popwidth+50, // maximale Breite in Pixel
        minWidth: popwidth  // (optional) minimale Breite in Pixel
    };
    
    // Popup mit den definierten Optionen binden
    marker.bindPopup(popupContent, popupOptions);

        marker.bindPopup(popupContent);
    });
    </script>
</body>
</html>

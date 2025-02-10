<?php
// Damit der Browser das XML als KML interpretiert:
header("Content-type: application/vnd.google-earth.kml+xml");
// Mit dem Content-Disposition-Header wird der Browser angewiesen, die Datei als Download mit dem Namen "export.kml" zu speichern
header("Content-Disposition: attachment; filename=\"pogocards.kml\"");

echo '<?xml version="1.0" encoding="UTF-8"?>';
?>
<kml xmlns="http://www.opengis.net/kml/2.2">
<Document>
    <name>PokemonGo-Postkarten-Orte</name>
    <description>Geopunkte der PoGo-Postkarten mit Informationen und Bildern</description>
<?php
include ('.ht_cred.php');
// Datenbank-Verbindungsparameter anpassen
$servername = "localhost";
$dbname     = "PoGoGifts";
$baseurl    = "https://myserver/mydir/";

// Verbindung herstellen
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Verbindungsfehler: " . $conn->connect_error);
}

$sql = "SELECT * FROM GiftLoc";
$result = $conn->query($sql);

$anzstyles=0;

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        // HTML-sichere Ausgabe für Name und Beschreibung
        $name = htmlspecialchars($row['name']);
        $desc = htmlspecialchars($row['description']) . "<br>" . htmlspecialchars($row['friend']) ;
        // Optional: Bilder in die Beschreibung einfügen
        $imgHTML = "<table><tr>";
        $widthimg=0;
        for ($i = 1; $i <= 4; $i++) {
            if (!empty($row["image$i"])) {
                $imgHTML .= '<td><img src="' . $baseurl . $row["image$i"] . '" style="max-width:200px;" /></td>';
                // $imgHTML .= '<td><img src=' . $baseurl . $row["image$i"] . ' style='  "max-width:200px;\" /></td>';
                $widthimg+=215;
            }
        }
        $imgHTML .= "</tr></table>";
        
        // Die Beschreibung in ein CDATA‑Feld einbetten
        $fullDescription = "<![CDATA[<div style=\"height:520px;width:". $widthimg."px\"> $desc $imgHTML </div>]]>";
        $latitude  = $row['latitude'];
        $longitude = $row['longitude'];
        
        echo "    <Placemark>\n";
        echo "        <name>$name</name>\n";
        echo "        <description>$fullDescription</description>\n";
        echo "        <Point>\n";
        echo "            <coordinates>$longitude,$latitude,0</coordinates>\n";
        echo "        </Point>\n";
        echo "    </Placemark>\n";
        
        $anzstyles+=1;
    }
}
$conn->close();
?>
</Document>
</kml>

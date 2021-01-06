<?php
session_start();

$servername = "rdbms.strato.de";
$username = "U4099678";
$password = "...";
$dbname = "DB4099678";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) { die("Connection failed: " . $conn->connect_error); }

//    $sql = "INSERT INTO `eue_automat_status` (`sessionID`, `Slot0`, `Slot1`, `Slot2`, `Slot3`, `Slot4`) 
//    VALUES ('Nachfuellen', '1', '1', '1', '1', '1' );";
$sql = "INSERT INTO `eue_automat_status` (`sessionID`) VALUES ('Nachfuellen');"; //Verlässt sich auf die Standardwerte in der DB
//echo "SQLStr: $sql\n";

if ($conn->query($sql)) {
//    echo "Erfolgreich ausgefuehrt.\n";
} else {
    header("Location: https://www.eue-turnt.de/schild/meldung.php?text=Fehler%20N2.");
    //    echo "Fehler bei der Abfrage: " . $sql . "<br>" . $conn->error;
}

$conn->close();

//header("Status: 301 Moved Permanently");
header("Location: https://www.eue-turnt.de/schild/meldung.php?text=Nachfuellen%20erfolgreich");


?>

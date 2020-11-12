<?php
$servername = "rdbms.strato.de";
$username = "U4099678";
$password = "...";
$dbname = "DB4099678";

$AnzGesamtSlots = 5;

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) { die("Connection failed: " . $conn->connect_error); }


$sql = "SELECT id, UNIX_TIMESTAMP(zeit) as zeit FROM `eue_automat_feedback` ORDER BY zeit DESC LIMIT 1"; 
$LetzteZeit = 0;
if ($result = $conn->query($sql)) {
    while ($row = $result->fetch_assoc()) {
        $LetzteZeit = $row["zeit"];
    }
    $result->free();
} else {
    echo "Fehler bei der Abfrage: " . $sql . "<br>" . $conn->error;
}

$sql = "SELECT * FROM `eue_automat_status` ORDER BY `zeit` DESC LIMIT 1";
$AnzahlVorhandenerSlots = 0;
if ($result = $conn->query($sql)) {
    while ($row = $result->fetch_assoc()) {
        for ($i=0; $i<$AnzGesamtSlots; $i++) {
            if ($row["Slot".$i]>0) {
                $AnzahlVorhandenerSlots++;
            }
        }
        printf('{"slots":[%d,%d,%d,%d,%d], "rest":%d, "total":%d, "automaten_feedback_zeit":%d, "time":%d}', $row["Slot0"], $row["Slot1"], $row["Slot2"], $row["Slot3"], $row["Slot4"],
            $AnzahlVorhandenerSlots, $AnzGesamtSlots, $LetzteZeit, time());
    }
    $result->free();
} else {
    echo "Fehler bei der Abfrage: " . $sql . "<br>" . $conn->error;
}

$conn->close();

?>
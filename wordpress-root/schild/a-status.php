<?php
$servername = "rdbms.strato.de";
$username = "U4099678";
$password = "...";
$dbname = "DB4099678";

$AnzGesamtSlots = 1;

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) { die("Connection failed: " . $conn->connect_error); }


$sql = "SELECT id, UNIX_TIMESTAMP(zeit) as zeit FROM `eue_automat2_feedback` ORDER BY zeit DESC LIMIT 1"; 
$LetzteZeit = 0;
if ($result = $conn->query($sql)) {
    while ($row = $result->fetch_assoc()) {
        $LetzteZeit = $row["zeit"];
    }
    $result->free();
} else {
    echo "Fehler bei der Abfrage: " . $sql . "<br>" . $conn->error;
}

$sql = "SELECT * FROM `eue_automat2_status` ORDER BY `zeit` DESC LIMIT 1";
if ($result = $conn->query($sql)) {
    while ($row = $result->fetch_assoc()) {
        $mystr = "";
        for ($i=0; $i<$AnzGesamtSlots; $i++) {
            $mystr .= sprintf('%d,', $row["slot".$i]);
        }
        $mystr = substr($mystr, 0, -1); //Delete last comma
        printf('{"slots":[%s], "automaten_feedback_zeit":%d, "time":%d}', $mystr,
            $LetzteZeit, time());
    }
    $result->free();
} else {
    echo "Fehler bei der Abfrage: " . $sql . "<br>" . $conn->error;
}

$conn->close();
?>

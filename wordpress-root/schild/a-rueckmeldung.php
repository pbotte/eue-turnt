<?php
$sicherung = $_GET['sicherung']; //SicherungsHash
$mytime = $_GET['time']; //Zeit des Automaten
$ausgabe = array();


$servername = "rdbms.strato.de";
$username = "U4099678";
$password = "...";
$dbname = "DB4099678";


$SicherheitErfuellt = false;
if (is_numeric($mytime)) {
    $actTime = time();
    $ausgabe['timeServer'] = $actTime;
    $ausgabe['timeAutomat'] = $mytime;


    $conn = new mysqli($servername, $username, $password, $dbname);
    if ($conn->connect_error) { die("Connection failed: " . $conn->connect_error); }

    $sql = "INSERT INTO `eue_automat_feedback` (`automaten_zeit`) VALUES ('$mytime'); ";
    if ($result = $conn->query($sql)) {
        $ausgabe['log'][] = "Eintrag INSERT ok";
    } else {
        $ausgabe['log'][] = "Error: " . $sql . "<br>" . $conn->error;
    }
    $conn->close();

    for ($i = -10; $i <= 10; $i++) {
      $SicherheitFuerI = hash('sha512', strval($actTime+$i).'...');
//      echo "Offset: $i Sicherheit: $SicherheitFuerI\n";
      if ($SicherheitFuerI == $sicherung) {
          $SicherheitErfuellt = true;
          $ausgabe['sicherheitErfuelltByOffset'] = $i;
      }
    }

    if ($SicherheitErfuellt) {
        $conn = new mysqli($servername, $username, $password, $dbname);
        if ($conn->connect_error) { die("Connection failed: " . $conn->connect_error); }

//        $userAgent = $conn->real_escape_string($_SERVER['HTTP_USER_AGENT']);
//        $sessionIDStr = session_id();

        $sql = "SELECT * FROM `eue_automat_status` ORDER BY `zeit` DESC LIMIT 1";
        if ($result = $conn->query($sql)) {
            while ($row = $result->fetch_assoc()) {
                $ausgabe['slotStatus'] = sprintf("%d,%d,%d,%d,%d", $row["Slot0"], $row["Slot1"], $row["Slot2"], $row["Slot3"], $row["Slot4"] );
            }
            $result->free();
        } else {
            $ausgabe['log'][] = "Error: " . $sql . "<br>" . $conn->error;
        }

        $conn->close();
    }

}

$ausgabe['sicherheitErfuellt'] =  $SicherheitErfuellt;
echo json_encode($ausgabe);
?>

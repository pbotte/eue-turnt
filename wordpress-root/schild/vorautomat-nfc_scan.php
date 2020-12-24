<?php
if (!isset($_GET['tagid'])) {
    echo 'Empty tagid';
    exit();
} else if (!ctype_digit($_GET['tagid'])) {
    echo 'Invalid tagid';
    exit();
} else {
    $tagid = (int)$_GET['tagid'];
}
$sicherung = $_GET['sicherung']; //SicherungsHash

// Sicherung Start
$actTime = time();
$SicherheitErfuellt = false;

for ($i = -10; $i <= 10; $i++) {
    $SicherheitFuerI = hash('sha512', strval($actTime+$i).'...');
//      echo "Offset: $i Sicherheit: $SicherheitFuerI\n";
    if ($SicherheitFuerI == $sicherung) {
        $SicherheitErfuellt = true;
    }
}

if ($SicherheitErfuellt) {
    echo "Sicherheit erfuellt\n";
}else {
    echo "Sicherheit nicht erfuellt. Bitte korrekten Wert über sicherung angeben.\n";
    exit();
}

//Ab hier, Sicherheit ist nachgewiesen

$servername = "rdbms.strato.de";
$username = "U4099678";
$password = "rawxem-dukXef-segty2";
$dbname = "DB4099678";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) { die("Connection failed: " . $conn->connect_error); }

//Neuen Schildscan eintragen
$sql = "INSERT INTO `eue_nfc_tag_scans` (`tag_id`) VALUES ('".$tagid."'); ";
echo "SQLStr: $sql\n";
if ($conn->query($sql)) {
    echo "Erfolgreich ausgefuehrt.\n";
} else {
    echo "Fehler bei der Abfrage: " . $sql . "<br>" . $conn->error;
}

//Überprüfen, ob eine Ausgabe erfolgen kann

echo "Test, ob eine Ausgabe erfolgen kann.\n";
$sql = "SELECT scans.`tag_id`, /*scans.`scan_timestamp`, */ `eue_nfc_tags`.color FROM 
(
    SELECT * FROM `eue_nfc_tag_scans` WHERE `eue_nfc_tag_scans`.`tag_id` NOT IN (
        SELECT `eue_nfc_tag_redeem`.`tag_id` FROM `eue_nfc_tag_redeem`
        WHERE 
        `eue_nfc_tag_redeem`.`redeem_timestamp` >= CURRENT_TIMESTAMP - INTERVAL 5 MINUTE
	)
) AS scans
INNER JOIN `eue_nfc_tags` ON scans.`tag_id` = `eue_nfc_tags`.`tag_id`
WHERE 
scans.`scan_timestamp` >= CURRENT_TIMESTAMP - INTERVAL 70 SECOND
GROUP BY
`eue_nfc_tags`.color, scans.`tag_id`
ORDER BY
`eue_nfc_tags`.color, scans.`tag_id`";

$FarbenBeiFolgendenTagsGefunden = Array(0=>NULL, 1=>NULL, 2=>NULL, 3=>NULL, 4=> NULL);
if ($result = $conn->query($sql)) {
    while ($row = $result->fetch_assoc()) {
        printf("%d %d\n", $row["tag_id"], $row["color"] );
        $FarbenBeiFolgendenTagsGefunden[$row["color"]] = $row["tag_id"];
    }
    $result->free();

    $AlleFarbenVorhanden = true;
    foreach ($FarbenBeiFolgendenTagsGefunden as $k => $v) {
        if (is_null($v)) {
            printf("Farbe %d ist nicht vorhanden oder ist in der Tabelle eue_nfc_tag_redeem eingetragen, da gerade eingelöst.\n", $k);
            $AlleFarbenVorhanden = false;
        }
    }

    $TagsAlsStr = ""; //Um sie später in die Tabelle eue_automat_status eintragen zu können
    if ($AlleFarbenVorhanden) {
        echo "Alle Farben vorhanden, mit folgenden Tags: ";
        foreach ($FarbenBeiFolgendenTagsGefunden as $v) {
            $TagsAlsStr .= $v.", ";
            echo $v.", ";

            //Die Tags als "benutzt" in Tabelle eue_nfc_tag_redeem eintragen
            $sql = "INSERT INTO `eue_nfc_tag_redeem` (`tag_id`) VALUES ('".$v."'); ";
            if (!$conn->query($sql)) {
                echo "Fehler bei der Abfrage: " . $sql . "<br>" . $conn->error."\n";
            }

        }
        echo "\n";

        echo "Tags aller Farben vorhanden, waren nicht in der Tabelle eue_nfc_tag_redeem eingetragen dass sie ";
        echo "vor Kurzem erst verwendet wurden und sind nun in ebendiese Tabelle eingetragen worden. Als nächstes ";
        echo "kann die Ausgabe der Belohnung erfolgen.";



        //Code aus voratutomat.php übernommen

        echo "Super! Du warst an allen Schildern. Jetzt kommt Deine Belohnung.\n\n";
        echo "Aktueller Stand der Slots:";

        $sql = "SELECT * FROM `eue_automat_status` ORDER BY `zeit` DESC LIMIT 1";
        $AnzahlVorhandenerSlots = 0;
        $ZuvergebenderSlot = -1;
        $SlotBefuellungsstand = array(); //Zwischenspeicherung des Eintrags, um ihn später - um einen Slot modifiziert - wieder eintragen zu können
        if ($result = $conn->query($sql)) {
            while ($row = $result->fetch_assoc()) {
                printf("%d,%d,%d,%d,%d\n", $row["Slot0"], $row["Slot1"], $row["Slot2"], $row["Slot3"], $row["Slot4"] );
                for ($i=0; $i<5; $i++) {
                    $SlotBefuellungsstand[$i] = $row["Slot".$i];
                    if ($row["Slot".$i]>0) {
                        $AnzahlVorhandenerSlots++;
                        echo "An Slot $i haengt noch eine Tuete.\n";
                        $ZuvergebenderSlot = $i; //Der Teilnehmer soll eine Tuete aus diesem Slot erhalten
                    } else {
                        echo "An Slot $i haengt keine Tuete mehr.\n";
                    }
                }
            }
            $result->free();
        } else {
            echo "Fehler bei der Abfrage: " . $sql . "<br>" . $conn->error;
        }
        echo "\n";


        if ($AnzahlVorhandenerSlots > 0) {
            echo "Es sind noch Tueten vorhanden.\n";
            echo "Du erhaeltst die Tuete aus Slot Nr: $ZuvergebenderSlot\n";
            var_dump($SlotBefuellungsstand);
            //Die folgende Zeile auskommentieren, wenn im Debug-Modus, damit nichts ausgegeben wird
            $SlotBefuellungsstand[$ZuvergebenderSlot] = 0; //Dieser Slot soll ausgeloest werden
            $sql = "INSERT INTO `eue_automat_status` (`sessionID`, `Slot0`, `Slot1`, `Slot2`, `Slot3`, `Slot4`) VALUES ('NFC mit Tags: ".$TagsAlsStr."', ";
            for ($i=0; $i<5; $i++) {
                if ($i >0) {
                    $sql = $sql.",";
                }
                $sql = $sql."'".$SlotBefuellungsstand[$i]."' ";
            }
            $sql = $sql.");";
            echo "SQLStr: $sql\n";

            if ($conn->query($sql)) {
                echo "Erfolgreich ausgefuehrt. Belohnung erhalten. Bitte entnehmen.";
            } else {
                echo "Fehler bei der Abfrage: " . $sql . "<br>" . $conn->error;
            }
            echo "\n";

        } else {
            echo "Leider sind keie Tueten mehr vorhanden, obwohl Du sie Dir verdient hattest. Komme bitte später wieder oder melde Dich bei uns.\n";
        }

        //Ende der Ausgabe aus dem Automat



    }


} else {
    echo "Fehler bei der Abfrage: " . $sql . "<br>" . $conn->error;
}

$conn->close();
?>

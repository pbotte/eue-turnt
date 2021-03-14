<?php
    include('phpqrcode/qrlib.php');
    
    $tempDir = "qr-erstellen/qrbild/";
        
    // generating QR
    // QRcode::png("QR-Code Inhalt", Speicherort_des_QR , Qualität des QR-Codes, Größe des QR-Codes, Breite des Rahmens um den QR Code);
    // Qualität des QR-Codes = QR_ECLEVEL_L oder M oder Q oder H
    // Größe des QR-Codes >0
    // Breite des Rahmens um den QR Code >=0
    
    QRcode::png($codeContents, $tempDir.$codeContents.'_erstellt.png', QR_ECLEVEL_L, 15, 0);  

   ?>

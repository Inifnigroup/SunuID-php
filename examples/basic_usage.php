<?php

require_once __DIR__ . '/../vendor/autoload.php';

use SunuID\SunuID;

// Configuration du SDK
$config = [
    'client_id' => 'your_client_id_here',
    'secret_id' => 'your_secret_id_here',
    'partner_name' => 'Your Company Name',
    'theme' => 'light',
    'language' => 'fr',
    'enable_logs' => true,
    'log_file' => 'sunuid_example.log'
];

try {
    // Initialisation du SDK
    $sunuid = new SunuID($config);
    
    echo "=== SunuID PHP SDK - Exemple d'utilisation ===\n\n";
    
    // Initialisation avec l'API
    echo "1. Initialisation du SDK...\n";
    if ($sunuid->init()) {
        echo "✅ SDK initialisé avec succès\n";
        
        $partnerInfo = $sunuid->getPartnerInfo();
        echo "   - Partenaire: " . ($partnerInfo['partner_name'] ?? 'N/A') . "\n";
        echo "   - ID Partenaire: " . ($partnerInfo['partner_id'] ?? 'N/A') . "\n";
    } else {
        echo "❌ Échec de l'initialisation du SDK\n";
        exit(1);
    }
    
    echo "\n2. Génération d'un QR code d'authentification...\n";
    $qrResult = $sunuid->generateQR('https://example.com/auth');
    
    if ($qrResult['success']) {
        echo "✅ QR code généré avec succès\n";
        echo "   - Session ID: " . $qrResult['data']['session_id'] . "\n";
        echo "   - Contenu: " . $qrResult['data']['content'] . "\n";
        echo "   - Expire le: " . ($qrResult['data']['expires_at'] ?? 'N/A') . "\n";
        
        $sessionId = $qrResult['data']['session_id'];
        
        echo "\n3. Vérification du statut du QR code...\n";
        $statusResult = $sunuid->checkQRStatus($sessionId);
        
        if ($statusResult['success']) {
            echo "✅ Statut récupéré avec succès\n";
            echo "   - Statut: " . $statusResult['data']['status'] . "\n";
            
            if (isset($statusResult['data']['user_data'])) {
                echo "   - Données utilisateur: " . json_encode($statusResult['data']['user_data']) . "\n";
            }
        } else {
            echo "❌ Échec de la récupération du statut\n";
            echo "   - Erreur: " . $statusResult['error'] . "\n";
        }
        
    } else {
        echo "❌ Échec de la génération du QR code\n";
        echo "   - Erreur: " . $qrResult['error'] . "\n";
    }
    
    echo "\n4. Génération d'un QR code local (sans API)...\n";
    $localQrResult = $sunuid->generateQRLocal('https://example.com/local', [
        'size' => 300,
        'margin' => 10,
        'foreground_color' => ['r' => 0, 'g' => 0, 'b' => 0],
        'background_color' => ['r' => 255, 'g' => 255, 'b' => 255]
    ]);
    
    if ($localQrResult['success']) {
        echo "✅ QR code local généré avec succès\n";
        echo "   - Contenu: " . $localQrResult['data']['content'] . "\n";
        echo "   - QR Code (base64): " . substr($localQrResult['data']['qr_code'], 0, 50) . "...\n";
    } else {
        echo "❌ Échec de la génération du QR code local\n";
        echo "   - Erreur: " . $localQrResult['error'] . "\n";
    }
    
    echo "\n5. Informations de configuration...\n";
    $config = $sunuid->getConfig();
    echo "   - URL API: " . $config['api_url'] . "\n";
    echo "   - Type: " . $config['type'] . " (" . $sunuid->getTypeName($config['type']) . ")\n";
    echo "   - Thème: " . $config['theme'] . "\n";
    echo "   - Langue: " . $config['language'] . "\n";
    echo "   - Timeout: " . $config['request_timeout'] . "s\n";
    echo "   - Max retries: " . $config['max_retries'] . "\n";
    
} catch (Exception $e) {
    echo "❌ Erreur: " . $e->getMessage() . "\n";
    echo "   Fichier: " . $e->getFile() . "\n";
    echo "   Ligne: " . $e->getLine() . "\n";
}

echo "\n=== Fin de l'exemple ===\n"; 
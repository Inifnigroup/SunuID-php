<?php

/**
 * Script de test d'installation du SDK SunuID PHP
 * 
 * Ce script vérifie que le SDK est correctement installé et fonctionnel.
 */

use SunuID\SunuID;

echo "🧪 Test d'installation du SDK SunuID PHP\n";
echo "=====================================\n\n";

// Test 1: Vérification de l'autoloader
echo "1. Vérification de l'autoloader...\n";
try {
    require_once __DIR__ . '/vendor/autoload.php';
    echo "   ✅ Autoloader chargé avec succès\n";
} catch (Exception $e) {
    echo "   ❌ Erreur autoloader: " . $e->getMessage() . "\n";
    exit(1);
}

// Test 2: Vérification de la classe SunuID
echo "\n2. Vérification de la classe SunuID...\n";
try {
    $sunuidClass = new ReflectionClass('SunuID\SunuID');
    echo "   ✅ Namespace SunuID disponible\n";
} catch (Exception $e) {
    echo "   ❌ Erreur namespace: " . $e->getMessage() . "\n";
    exit(1);
}

// Test 3: Test d'instanciation
echo "\n3. Test d'instanciation...\n";
try {
    $sunuid = new SunuID([
        'client_id' => 'test_client',
        'secret_id' => 'test_secret',
        'partner_name' => 'Test Company',
        'enable_logs' => false
    ]);
    echo "   ✅ SDK instancié avec succès\n";
} catch (Exception $e) {
    echo "   ❌ Erreur instanciation: " . $e->getMessage() . "\n";
    exit(1);
}

// Test 4: Vérification de la configuration
echo "\n4. Vérification de la configuration...\n";
try {
    $config = $sunuid->getConfig();
    echo "   ✅ Configuration récupérée\n";
    echo "   - Version: " . ($config['version'] ?? '1.0.0') . "\n";
    echo "   - API URL: " . $config['api_url'] . "\n";
    echo "   - Type: " . $config['type'] . "\n";
} catch (Exception $e) {
    echo "   ❌ Erreur configuration: " . $e->getMessage() . "\n";
    exit(1);
}

// Test 5: Test de génération de session
echo "\n5. Test de génération de session...\n";
try {
    $sessionCode = $sunuid->generateSessionCode();
    echo "   ✅ Code de session généré: " . substr($sessionCode, 0, 20) . "...\n";
} catch (Exception $e) {
    echo "   ❌ Erreur génération session: " . $e->getMessage() . "\n";
    exit(1);
}

// Test 6: Test de résolution de type
echo "\n6. Test de résolution de type...\n";
try {
    $typeName = $sunuid->getTypeName(2);
    echo "   ✅ Type résolu: " . $typeName . "\n";
} catch (Exception $e) {
    echo "   ❌ Erreur résolution type: " . $e->getMessage() . "\n";
    exit(1);
}

// Test 7: Test de génération QR local
echo "\n7. Test de génération QR local...\n";
try {
    $result = $sunuid->generateQRLocal('https://example.com/test');
    if ($result['success']) {
        echo "   ✅ QR code local généré avec succès\n";
        echo "   - Contenu: " . $result['data']['content'] . "\n";
        echo "   - QR Code: " . substr($result['data']['qr_code'], 0, 50) . "...\n";
    } else {
        echo "   ❌ Échec génération QR: " . $result['error'] . "\n";
    }
} catch (Exception $e) {
    echo "   ❌ Erreur génération QR: " . $e->getMessage() . "\n";
    exit(1);
}

// Test 8: Vérification des extensions PHP
echo "\n8. Vérification des extensions PHP...\n";
$required_extensions = ['curl', 'json', 'openssl', 'gd'];
$missing_extensions = [];

foreach ($required_extensions as $ext) {
    if (extension_loaded($ext)) {
        echo "   ✅ Extension $ext disponible\n";
    } else {
        echo "   ❌ Extension $ext manquante\n";
        $missing_extensions[] = $ext;
    }
}

if (!empty($missing_extensions)) {
    echo "\n⚠️  Extensions manquantes: " . implode(', ', $missing_extensions) . "\n";
    echo "   Installez-les avec votre gestionnaire de paquets PHP\n";
}

// Résumé
echo "\n" . str_repeat("=", 50) . "\n";
echo "🎉 RÉSUMÉ DU TEST D'INSTALLATION\n";
echo str_repeat("=", 50) . "\n";

if (empty($missing_extensions)) {
    echo "✅ SDK SunuID PHP installé et fonctionnel !\n";
    echo "🚀 Vous pouvez maintenant utiliser le SDK dans votre projet.\n";
    echo "\n📖 Consultez le README.md pour des exemples d'utilisation.\n";
    echo "📧 Support: dev@sunuid.sn\n";
} else {
    echo "⚠️  SDK installé mais extensions manquantes.\n";
    echo "🔧 Installez les extensions manquantes pour une utilisation complète.\n";
}

echo "\n" . str_repeat("=", 50) . "\n"; 
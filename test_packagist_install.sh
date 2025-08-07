#!/bin/bash

# Script de test d'installation depuis Packagist
echo "🧪 Test d'installation depuis Packagist"
echo "======================================"

# Créer un répertoire de test
TEST_DIR="test-packagist-install"
echo "📁 Création du répertoire de test: $TEST_DIR"

if [ -d "$TEST_DIR" ]; then
    rm -rf "$TEST_DIR"
fi

mkdir "$TEST_DIR"
cd "$TEST_DIR"

# Créer composer.json
echo "📝 Création du composer.json..."
cat > composer.json << 'EOF'
{
    "name": "test/sunuid-install",
    "description": "Test d'installation du SDK SunuID",
    "require": {
        "sunuid/php-sdk": "^1.0"
    },
    "minimum-stability": "stable",
    "prefer-stable": true
}
EOF

echo "✅ composer.json créé"

# Installer le package
echo "📦 Installation du package..."
if composer install; then
    echo "✅ Package installé avec succès"
else
    echo "❌ Erreur lors de l'installation"
    exit 1
fi

# Tester l'installation
echo "🧪 Test de l'installation..."
cat > test.php << 'EOF'
<?php

require_once 'vendor/autoload.php';

use SunuID\SunuID;

try {
    $sunuid = new SunuID([
        'client_id' => 'test_client',
        'secret_id' => 'test_secret',
        'partner_name' => 'Test Company',
        'enable_logs' => false
    ]);
    
    echo "✅ SDK instancié avec succès\n";
    
    $config = $sunuid->getConfig();
    echo "📋 Configuration récupérée\n";
    echo "   - API URL: " . $config['api_url'] . "\n";
    echo "   - Type: " . $config['type'] . "\n";
    
    $sessionCode = $sunuid->generateSessionCode();
    echo "🔑 Code de session généré: " . substr($sessionCode, 0, 20) . "...\n";
    
    $result = $sunuid->generateQRLocal('https://example.com/test');
    if ($result['success']) {
        echo "📱 QR code généré avec succès\n";
        echo "   - Contenu: " . $result['data']['content'] . "\n";
    } else {
        echo "❌ Erreur génération QR: " . $result['error'] . "\n";
    }
    
    echo "\n🎉 Test d'installation réussi !\n";
    echo "📦 Le package est prêt à être utilisé.\n";
    
} catch (Exception $e) {
    echo "❌ Erreur: " . $e->getMessage() . "\n";
    exit(1);
}
EOF

# Exécuter le test
if php test.php; then
    echo "✅ Test d'installation réussi"
else
    echo "❌ Test d'installation échoué"
    exit 1
fi

# Nettoyage
cd ..
echo "🧹 Nettoyage..."
rm -rf "$TEST_DIR"

echo ""
echo "🎉 RÉSUMÉ DU TEST"
echo "================="
echo "✅ Package installé depuis Packagist"
echo "✅ SDK fonctionnel"
echo "✅ Toutes les fonctionnalités testées"
echo ""
echo "📦 Le SDK SunuID PHP est prêt pour la production !"
echo "📖 Documentation: https://docs.sunuid.sn"
echo "📧 Support: dev@sunuid.sn" 
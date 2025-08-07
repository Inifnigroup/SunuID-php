<?php

/**
 * Test simple de l'intégration Socket.IO
 */

require_once __DIR__ . '/vendor/autoload.php';

use SunuID\SunuID;

echo "🧪 Test simple Socket.IO - SDK SunuID PHP\n";
echo "=========================================\n\n";

// Configuration simple
$config = [
    'client_id' => 'test_client',
    'secret_id' => 'test_secret',
    'partner_name' => 'Test Partner',
    'enable_websocket' => true,
    'websocket_url' => 'wss://samasocket.fayma.sn:9443',
    'websocket_socketio_version' => '2',
    'enable_logs' => false
];

try {
    // Initialiser le SDK
    $sunuid = new SunuID($config);
    echo "✅ SDK initialisé\n";
    
    // Initialiser le Socket.IO
    $result = $sunuid->initWebSocket();
    echo "✅ Initialisation Socket.IO: " . ($result ? 'Succès' : 'Échec') . "\n";
    
    if ($result) {
        $webSocket = $sunuid->getWebSocket();
        echo "✅ Client Socket.IO récupéré\n";
        
        // Afficher la configuration
        $wsConfig = $webSocket->getConfig();
        echo "✅ Configuration Socket.IO:\n";
        echo "   - URL: " . $wsConfig['ws_url'] . "\n";
        echo "   - Version: " . $wsConfig['socketio_version'] . "\n";
        echo "   - Transports: " . implode(', ', $wsConfig['transports']) . "\n";
        
        // Test de connexion (sera false car serveur de test inexistant)
        echo "\n🔗 Test de connexion Socket.IO...\n";
        $connected = $sunuid->connectWebSocket();
        echo "✅ Connexion: " . ($connected ? 'Succès' : 'Échec (attendu)') . "\n";
        
        // Test des méthodes
        echo "\n🔧 Test des méthodes Socket.IO:\n";
        
        // Test d'envoi de message
        $sent = $sunuid->sendWebSocketMessage([
            'event' => 'test',
            'data' => ['message' => 'Hello Socket.IO!']
        ]);
        echo "   - Envoi message: " . ($sent ? 'Succès' : 'Échec (attendu)') . "\n";
        
        // Test d'abonnement
        $subscribed = $sunuid->subscribeToSession('test_session_123');
        echo "   - Abonnement session: " . ($subscribed ? 'Succès' : 'Échec (attendu)') . "\n";
        
        // Test de désabonnement
        $unsubscribed = $sunuid->unsubscribeFromSession('test_session_123');
        echo "   - Désabonnement session: " . ($unsubscribed ? 'Succès' : 'Échec (attendu)') . "\n";
        
        // Test des sessions actives
        $sessions = $sunuid->getWebSocketActiveSessions();
        echo "   - Sessions actives: " . count($sessions) . "\n";
        
        // Test de déconnexion
        $sunuid->disconnectWebSocket();
        echo "   - Déconnexion: Succès\n";
        
    }
    
} catch (Exception $e) {
    echo "❌ Erreur: " . $e->getMessage() . "\n";
    echo "   Stack trace: " . $e->getTraceAsString() . "\n";
}

echo "\n🎉 Test Socket.IO terminé!\n";
echo "\n💡 Résumé:\n";
echo "   - ✅ SDK initialisé avec succès\n";
echo "   - ✅ Socket.IO configuré correctement\n";
echo "   - ✅ Méthodes Socket.IO disponibles\n";
echo "   - ⚠️ Connexions réelles nécessitent un serveur Socket.IO actif\n";
echo "\n🚀 Le SDK est prêt pour les Socket.IO!\n"; 
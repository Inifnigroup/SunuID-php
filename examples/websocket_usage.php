<?php

/**
 * Exemple d'utilisation WebSocket avec le SDK SunuID PHP
 * 
 * Ce script démontre comment utiliser les WebSockets pour recevoir
 * des notifications en temps réel lors de l'authentification et du KYC.
 */

require_once __DIR__ . '/../vendor/autoload.php';

use SunuID\SunuID;

echo "🚀 Exemple d'utilisation WebSocket - SDK SunuID PHP\n";
echo "==================================================\n\n";

// Configuration avec WebSocket activé
$config = [
    'client_id' => 'votre_client_id',
    'secret_id' => 'votre_secret_id',
    'partner_name' => 'Votre Entreprise',
    'enable_websocket' => true,
    'websocket_auto_connect' => true,
    'websocket_url' => 'wss://api.sunuid.fayma.sn/ws',
    'enable_logs' => true,
    'log_level' => \Monolog\Logger::INFO
];

try {
    // Initialiser le SDK
    $sunuid = new SunuID($config);
    
    echo "✅ SDK initialisé\n";
    
    // Initialiser le WebSocket
    if ($sunuid->initWebSocket()) {
        echo "✅ WebSocket initialisé\n";
        
        // Configurer les callbacks pour les événements
        setupWebSocketCallbacks($sunuid);
        
        // Se connecter au WebSocket
        if ($sunuid->connectWebSocket()) {
            echo "✅ Connexion WebSocket établie\n";
            
            // Attendre un peu pour la connexion
            sleep(2);
            
            // Exemple 1: Générer un QR code avec abonnement automatique
            echo "\n📱 Exemple 1: Génération QR avec WebSocket\n";
            echo "----------------------------------------\n";
            
            $result = $sunuid->generateQRWithWebSocket('https://votre-site.com/auth', [
                'type' => 2, // Authentification
                'theme' => 'light'
            ]);
            
            if ($result['success']) {
                echo "✅ QR code généré avec succès\n";
                echo "📋 Session ID: " . ($result['data']['session_id'] ?? 'N/A') . "\n";
                echo "🔗 URL: " . ($result['data']['url'] ?? 'N/A') . "\n";
                
                // L'utilisateur peut maintenant scanner le QR code
                echo "\n📱 L'utilisateur peut scanner le QR code...\n";
                echo "⏳ En attente des notifications WebSocket...\n";
                
                // Simuler une attente (en production, vous utiliseriez l'event loop)
                echo "⏰ Attente de 30 secondes pour les notifications...\n";
                sleep(30);
                
            } else {
                echo "❌ Erreur lors de la génération du QR code: " . $result['error'] . "\n";
            }
            
            // Exemple 2: Abonnement manuel à une session
            echo "\n📡 Exemple 2: Abonnement manuel à une session\n";
            echo "--------------------------------------------\n";
            
            $sessionId = 'session_' . uniqid();
            if ($sunuid->subscribeToSession($sessionId)) {
                echo "✅ Abonnement à la session: $sessionId\n";
                
                // Attendre un peu
                sleep(5);
                
                // Se désabonner
                $sunuid->unsubscribeFromSession($sessionId);
                echo "✅ Désabonnement de la session: $sessionId\n";
            }
            
            // Exemple 3: Envoi de message personnalisé
            echo "\n💬 Exemple 3: Envoi de message personnalisé\n";
            echo "----------------------------------------\n";
            
            $customMessage = [
                'type' => 'custom_event',
                'data' => [
                    'message' => 'Hello from PHP SDK!',
                    'timestamp' => time()
                ]
            ];
            
            if ($sunuid->sendWebSocketMessage($customMessage)) {
                echo "✅ Message personnalisé envoyé\n";
            }
            
            // Afficher les sessions actives
            echo "\n📊 Sessions actives:\n";
            echo "-------------------\n";
            $activeSessions = $sunuid->getWebSocketActiveSessions();
            if (empty($activeSessions)) {
                echo "Aucune session active\n";
            } else {
                foreach ($activeSessions as $sessionId => $sessionData) {
                    echo "Session: $sessionId - Statut: " . ($sessionData['status'] ?? 'unknown') . "\n";
                }
            }
            
            // Se déconnecter proprement
            echo "\n🔌 Déconnexion WebSocket...\n";
            $sunuid->disconnectWebSocket();
            echo "✅ Déconnexion terminée\n";
            
        } else {
            echo "❌ Échec de la connexion WebSocket\n";
        }
        
    } else {
        echo "❌ Échec de l'initialisation WebSocket\n";
    }
    
} catch (Exception $e) {
    echo "❌ Erreur: " . $e->getMessage() . "\n";
}

/**
 * Configurer les callbacks pour les événements WebSocket
 */
function setupWebSocketCallbacks(SunuID $sunuid): void
{
    // Callback pour la connexion
    $sunuid->onWebSocketEvent('connect', function ($data) {
        echo "🔗 WebSocket connecté\n";
    });
    
    // Callback pour la déconnexion
    $sunuid->onWebSocketEvent('disconnect', function ($data) {
        echo "🔌 WebSocket déconnecté\n";
        if (isset($data['reason'])) {
            echo "   Raison: " . $data['reason'] . "\n";
        }
    });
    
    // Callback pour les erreurs
    $sunuid->onWebSocketEvent('error', function ($data) {
        echo "❌ Erreur WebSocket: " . ($data['error'] ?? 'Erreur inconnue') . "\n";
    });
    
    // Callback pour l'authentification réussie
    $sunuid->onWebSocketEvent('auth_success', function ($data) {
        echo "✅ Authentification réussie!\n";
        echo "   Session ID: " . ($data['session_id'] ?? 'N/A') . "\n";
        echo "   User ID: " . ($data['user_id'] ?? 'N/A') . "\n";
        
        // Ici vous pouvez rediriger l'utilisateur ou mettre à jour l'interface
        echo "   🎉 L'utilisateur est maintenant authentifié!\n";
    });
    
    // Callback pour l'échec d'authentification
    $sunuid->onWebSocketEvent('auth_failure', function ($data) {
        echo "❌ Échec d'authentification\n";
        echo "   Session ID: " . ($data['session_id'] ?? 'N/A') . "\n";
        echo "   Raison: " . ($data['reason'] ?? 'Raison inconnue') . "\n";
        
        // Ici vous pouvez afficher un message d'erreur à l'utilisateur
        echo "   😞 L'authentification a échoué\n";
    });
    
    // Callback pour KYC complété
    $sunuid->onWebSocketEvent('kyc_complete', function ($data) {
        echo "✅ KYC complété!\n";
        echo "   Session ID: " . ($data['session_id'] ?? 'N/A') . "\n";
        
        if (isset($data['kyc_data'])) {
            echo "   📋 Données KYC reçues\n";
            // Traiter les données KYC
            processKycData($data['kyc_data']);
        }
    });
    
    // Callback pour KYC en attente
    $sunuid->onWebSocketEvent('kyc_pending', function ($data) {
        echo "⏳ KYC en attente\n";
        echo "   Session ID: " . ($data['session_id'] ?? 'N/A') . "\n";
        
        if (isset($data['pending_steps'])) {
            echo "   📝 Étapes en attente:\n";
            foreach ($data['pending_steps'] as $step) {
                echo "      - $step\n";
            }
        }
    });
    
    // Callback pour session expirée
    $sunuid->onWebSocketEvent('session_expired', function ($data) {
        echo "⏰ Session expirée\n";
        echo "   Session ID: " . ($data['session_id'] ?? 'N/A') . "\n";
        
        // Ici vous pouvez nettoyer les données de session
        echo "   🧹 Nettoyage de la session...\n";
    });
    
    // Callback pour tous les autres messages
    $sunuid->onWebSocketEvent('message', function ($data) {
        echo "📨 Message reçu: " . ($data['type'] ?? 'type inconnu') . "\n";
        if (isset($data['data'])) {
            echo "   Données: " . json_encode($data['data']) . "\n";
        }
    });
    
    echo "✅ Callbacks WebSocket configurés\n";
}

/**
 * Traiter les données KYC
 */
function processKycData(array $kycData): void
{
    echo "   🔍 Traitement des données KYC:\n";
    
    if (isset($kycData['user_info'])) {
        $userInfo = $kycData['user_info'];
        echo "      👤 Nom: " . ($userInfo['name'] ?? 'N/A') . "\n";
        echo "      📧 Email: " . ($userInfo['email'] ?? 'N/A') . "\n";
        echo "      📱 Téléphone: " . ($userInfo['phone'] ?? 'N/A') . "\n";
    }
    
    if (isset($kycData['verification_status'])) {
        echo "      ✅ Statut de vérification: " . $kycData['verification_status'] . "\n";
    }
    
    if (isset($kycData['documents'])) {
        echo "      📄 Documents vérifiés: " . count($kycData['documents']) . "\n";
    }
    
    // Ici vous pouvez sauvegarder les données en base de données
    // ou les traiter selon vos besoins
    echo "      💾 Données KYC sauvegardées\n";
}

echo "\n🎉 Exemple WebSocket terminé!\n";
echo "\n💡 Conseils d'utilisation:\n";
echo "   - En production, utilisez l'event loop pour maintenir la connexion\n";
echo "   - Gérez les reconnexions automatiques\n";
echo "   - Implémentez un système de retry pour les messages importants\n";
echo "   - Surveillez les logs pour le debugging\n";
echo "   - Utilisez les callbacks pour mettre à jour l'interface utilisateur\n"; 
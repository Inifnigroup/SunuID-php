<?php

/**
 * Exemple d'utilisation Socket.IO avec le SDK SunuID PHP
 * 
 * Ce script démontre comment utiliser Socket.IO pour recevoir
 * des notifications en temps réel lors de l'authentification et du KYC.
 */

require_once __DIR__ . '/../vendor/autoload.php';

use SunuID\SunuID;

echo "🚀 Exemple d'utilisation Socket.IO - SDK SunuID PHP\n";
echo "==================================================\n\n";

// Configuration avec Socket.IO activé
$config = [
    'client_id' => 'votre_client_id',
    'secret_id' => 'votre_secret_id',
    'partner_name' => 'Votre Entreprise',
    'enable_websocket' => true,
    'websocket_auto_connect' => true,
    'websocket_url' => 'wss://samasocket.fayma.sn:9443',
    'websocket_socketio_version' => '2',
    'websocket_transports' => ['websocket', 'polling'],
    'websocket_query_params' => [
        'custom_param' => 'custom_value'
    ],
    'enable_logs' => true,
    'log_level' => \Monolog\Logger::INFO
];

try {
    // Initialiser le SDK
    $sunuid = new SunuID($config);
    
    echo "✅ SDK initialisé\n";
    
    // Initialiser le Socket.IO
    if ($sunuid->initWebSocket()) {
        echo "✅ Socket.IO initialisé\n";
        
        // Configurer les callbacks pour les événements
        setupSocketIOCallbacks($sunuid);
        
        // Se connecter au Socket.IO
        if ($sunuid->connectWebSocket()) {
            echo "✅ Connexion Socket.IO établie\n";
            
            // Attendre un peu pour la connexion
            sleep(2);
            
            // Exemple 1: Générer un QR code avec abonnement automatique
            echo "\n📱 Exemple 1: Génération QR avec Socket.IO\n";
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
                echo "⏳ En attente des notifications Socket.IO...\n";
                
                // Simuler une attente (en production, vous utiliseriez une boucle d'événements)
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
                'event' => 'custom_event',
                'data' => [
                    'message' => 'Hello from PHP SDK!',
                    'timestamp' => time(),
                    'user_id' => 'test_user_123'
                ]
            ];
            
            if ($sunuid->sendWebSocketMessage($customMessage)) {
                echo "✅ Message personnalisé envoyé\n";
            }
            
            // Exemple 4: Écoute d'événements spécifiques
            echo "\n👂 Exemple 4: Écoute d'événements spécifiques\n";
            echo "------------------------------------------\n";
            
            // Écouter des événements spécifiques à votre application
            $sunuid->onWebSocketEvent('user_connected', function ($data) {
                echo "👤 Utilisateur connecté: " . ($data['user_id'] ?? 'N/A') . "\n";
            });
            
            $sunuid->onWebSocketEvent('notification', function ($data) {
                echo "🔔 Notification reçue: " . ($data['message'] ?? 'N/A') . "\n";
            });
            
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
            echo "\n🔌 Déconnexion Socket.IO...\n";
            $sunuid->disconnectWebSocket();
            echo "✅ Déconnexion terminée\n";
            
        } else {
            echo "❌ Échec de la connexion Socket.IO\n";
        }
        
    } else {
        echo "❌ Échec de l'initialisation Socket.IO\n";
    }
    
} catch (Exception $e) {
    echo "❌ Erreur: " . $e->getMessage() . "\n";
}

/**
 * Configurer les callbacks pour les événements Socket.IO
 */
function setupSocketIOCallbacks(SunuID $sunuid): void
{
    // Callback pour la connexion
    $sunuid->onWebSocketEvent('connect', function ($data) {
        echo "🔗 Socket.IO connecté\n";
        echo "   Session ID: " . ($data['sid'] ?? 'N/A') . "\n";
    });
    
    // Callback pour la déconnexion
    $sunuid->onWebSocketEvent('disconnect', function ($data) {
        echo "🔌 Socket.IO déconnecté\n";
        if (isset($data['reason'])) {
            echo "   Raison: " . $data['reason'] . "\n";
        }
    });
    
    // Callback pour les erreurs
    $sunuid->onWebSocketEvent('error', function ($data) {
        echo "❌ Erreur Socket.IO: " . ($data['error'] ?? 'Erreur inconnue') . "\n";
    });
    
    // Callback pour l'authentification réussie
    $sunuid->onWebSocketEvent('auth_success', function ($data) {
        echo "✅ Authentification réussie!\n";
        echo "   Session ID: " . ($data['session_id'] ?? 'N/A') . "\n";
        echo "   User ID: " . ($data['user_id'] ?? 'N/A') . "\n";
        echo "   Timestamp: " . ($data['timestamp'] ?? 'N/A') . "\n";
        
        // Ici vous pouvez rediriger l'utilisateur ou mettre à jour l'interface
        echo "   🎉 L'utilisateur est maintenant authentifié!\n";
        
        // Exemple de traitement post-authentification
        handlePostAuthentication($data);
    });
    
    // Callback pour l'échec d'authentification
    $sunuid->onWebSocketEvent('auth_failure', function ($data) {
        echo "❌ Échec d'authentification\n";
        echo "   Session ID: " . ($data['session_id'] ?? 'N/A') . "\n";
        echo "   Raison: " . ($data['reason'] ?? 'Raison inconnue') . "\n";
        echo "   Code d'erreur: " . ($data['error_code'] ?? 'N/A') . "\n";
        
        // Ici vous pouvez afficher un message d'erreur à l'utilisateur
        echo "   😞 L'authentification a échoué\n";
        
        // Exemple de gestion d'erreur
        handleAuthenticationError($data);
    });
    
    // Callback pour KYC complété
    $sunuid->onWebSocketEvent('kyc_complete', function ($data) {
        echo "✅ KYC complété!\n";
        echo "   Session ID: " . ($data['session_id'] ?? 'N/A') . "\n";
        echo "   Statut: " . ($data['status'] ?? 'N/A') . "\n";
        
        if (isset($data['kyc_data'])) {
            echo "   📋 Données KYC reçues\n";
            // Traiter les données KYC
            processKycData($data['kyc_data']);
        }
        
        // Exemple de traitement post-KYC
        handlePostKYC($data);
    });
    
    // Callback pour KYC en attente
    $sunuid->onWebSocketEvent('kyc_pending', function ($data) {
        echo "⏳ KYC en attente\n";
        echo "   Session ID: " . ($data['session_id'] ?? 'N/A') . "\n";
        echo "   Étape actuelle: " . ($data['current_step'] ?? 'N/A') . "\n";
        
        if (isset($data['pending_steps'])) {
            echo "   📝 Étapes en attente:\n";
            foreach ($data['pending_steps'] as $step) {
                echo "      - $step\n";
            }
        }
        
        // Exemple de mise à jour de l'interface
        updateKYCProgress($data);
    });
    
    // Callback pour session expirée
    $sunuid->onWebSocketEvent('session_expired', function ($data) {
        echo "⏰ Session expirée\n";
        echo "   Session ID: " . ($data['session_id'] ?? 'N/A') . "\n";
        echo "   Expirée à: " . ($data['expired_at'] ?? 'N/A') . "\n";
        
        // Ici vous pouvez nettoyer les données de session
        echo "   🧹 Nettoyage de la session...\n";
        
        // Exemple de nettoyage
        cleanupExpiredSession($data);
    });
    
    // Callback pour tous les autres messages
    $sunuid->onWebSocketEvent('message', function ($data) {
        echo "📨 Message reçu: " . ($data['type'] ?? 'type inconnu') . "\n";
        if (isset($data['data'])) {
            echo "   Données: " . json_encode($data['data']) . "\n";
        }
    });
    
    echo "✅ Callbacks Socket.IO configurés\n";
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
        echo "      🆔 ID National: " . ($userInfo['national_id'] ?? 'N/A') . "\n";
    }
    
    if (isset($kycData['verification_status'])) {
        echo "      ✅ Statut de vérification: " . $kycData['verification_status'] . "\n";
    }
    
    if (isset($kycData['documents'])) {
        echo "      📄 Documents vérifiés: " . count($kycData['documents']) . "\n";
        foreach ($kycData['documents'] as $doc) {
            echo "         - " . ($doc['type'] ?? 'N/A') . ": " . ($doc['status'] ?? 'N/A') . "\n";
        }
    }
    
    if (isset($kycData['biometric_data'])) {
        echo "      🔐 Données biométriques: " . ($kycData['biometric_data']['status'] ?? 'N/A') . "\n";
    }
    
    // Ici vous pouvez sauvegarder les données en base de données
    // ou les traiter selon vos besoins
    echo "      💾 Données KYC sauvegardées\n";
}

/**
 * Gérer l'authentification réussie
 */
function handlePostAuthentication(array $data): void
{
    echo "   🎯 Traitement post-authentification:\n";
    echo "      - Mise à jour du statut utilisateur\n";
    echo "      - Création de session locale\n";
    echo "      - Redirection vers le dashboard\n";
    echo "      - Envoi de notification de succès\n";
}

/**
 * Gérer les erreurs d'authentification
 */
function handleAuthenticationError(array $data): void
{
    echo "   🚨 Gestion d'erreur d'authentification:\n";
    echo "      - Affichage du message d'erreur\n";
    echo "      - Log de l'erreur pour analyse\n";
    echo "      - Proposition de solutions\n";
    echo "      - Retry automatique si possible\n";
}

/**
 * Gérer le post-KYC
 */
function handlePostKYC(array $data): void
{
    echo "   🎯 Traitement post-KYC:\n";
    echo "      - Validation des données reçues\n";
    echo "      - Mise à jour du profil utilisateur\n";
    echo "      - Notification de completion\n";
    echo "      - Déblocage des fonctionnalités premium\n";
}

/**
 * Mettre à jour le progrès KYC
 */
function updateKYCProgress(array $data): void
{
    echo "   📊 Mise à jour du progrès KYC:\n";
    echo "      - Affichage de la barre de progression\n";
    echo "      - Mise à jour des étapes restantes\n";
    echo "      - Notification à l'utilisateur\n";
}

/**
 * Nettoyer une session expirée
 */
function cleanupExpiredSession(array $data): void
{
    echo "   🧹 Nettoyage de session expirée:\n";
    echo "      - Suppression des données temporaires\n";
    echo "      - Fermeture des connexions\n";
    echo "      - Notification à l'utilisateur\n";
    echo "      - Redirection vers la page de connexion\n";
}

echo "\n🎉 Exemple Socket.IO terminé!\n";
echo "\n💡 Conseils d'utilisation:\n";
echo "   - En production, utilisez une boucle d'événements pour maintenir la connexion\n";
echo "   - Gérez les reconnexions automatiques\n";
echo "   - Implémentez un système de retry pour les messages importants\n";
echo "   - Surveillez les logs pour le debugging\n";
echo "   - Utilisez les callbacks pour mettre à jour l'interface utilisateur\n";
echo "   - Gérez les timeouts et les erreurs de connexion\n";
echo "\n🔗 Configuration Socket.IO utilisée:\n";
echo "   - URL: wss://samasocket.fayma.sn:9443\n";
echo "   - Version: Socket.IO v4\n";
echo "   - Transports: websocket, polling\n";
echo "   - Paramètres: token, type, userId, username\n"; 
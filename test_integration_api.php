<?php

/**
 * API Backend pour l'interface de test d'intégration SunuID Socket.IO
 */

require_once __DIR__ . '/vendor/autoload.php';

use SunuID\SunuID;

// Configuration CORS pour permettre les requêtes depuis l'interface web
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

// Gérer les requêtes OPTIONS (preflight)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Configuration pour les tests
$testConfig = [
    'client_id' => 'test_client_' . uniqid(),
    'secret_id' => 'test_secret_' . uniqid(),
    'partner_name' => 'Test Partner - ' . date('Y-m-d H:i:s'),
    'enable_websocket' => true,
    'websocket_url' => 'wss://samasocket.fayma.sn:9443',
    'websocket_socketio_version' => '2',
    'websocket_transports' => ['websocket', 'polling'],
    'websocket_query_params' => [
        'custom_param' => 'custom_value',
        'test_mode' => 'true',
        'test_session' => uniqid()
    ],
    'enable_logs' => true,
    'log_level' => \Monolog\Logger::DEBUG,
    'log_file' => 'test-socketio-' . date('Y-m-d') . '.log'
];

// Instance globale du SDK
$sunuid = null;
$testResults = [];

// Fonction pour initialiser le SDK
function initSDK() {
    global $sunuid, $testConfig;
    
    if ($sunuid === null) {
        try {
            $sunuid = new SunuID($testConfig);
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
    return true;
}

// Fonction pour obtenir les données POST JSON
function getPostData() {
    $input = file_get_contents('php://input');
    return json_decode($input, true) ?: [];
}

// Fonction pour répondre avec succès
function respondSuccess($data = []) {
    echo json_encode([
        'success' => true,
        'data' => $data
    ]);
}

// Fonction pour répondre avec erreur
function respondError($message, $code = 400) {
    http_response_code($code);
    echo json_encode([
        'success' => false,
        'error' => $message
    ]);
}

// Récupérer l'action demandée
$action = $_GET['action'] ?? '';

try {
    switch ($action) {
        case 'load_config':
            // Charger la configuration
            respondSuccess([
                'config' => $testConfig
            ]);
            break;

        case 'init_websocket':
            // Initialiser Socket.IO
            if (!initSDK()) {
                respondError('Impossible d\'initialiser le SDK');
                break;
            }
            
            $result = $sunuid->initWebSocket();
            respondSuccess([
                'initialized' => $result
            ]);
            break;

        case 'connect':
            // Se connecter à Socket.IO
            if (!initSDK()) {
                respondError('Impossible d\'initialiser le SDK');
                break;
            }
            
            $sunuid->initWebSocket();
            $connected = $sunuid->connectWebSocket();
            respondSuccess([
                'connected' => $connected
            ]);
            break;

        case 'disconnect':
            // Se déconnecter de Socket.IO
            if ($sunuid) {
                $sunuid->disconnectWebSocket();
                respondSuccess([
                    'disconnected' => true
                ]);
            } else {
                respondError('SDK non initialisé');
            }
            break;

        case 'status':
            // Vérifier le statut de connexion
            if ($sunuid) {
                $connected = $sunuid->isWebSocketConnected();
                respondSuccess([
                    'connected' => $connected
                ]);
            } else {
                respondSuccess([
                    'connected' => false
                ]);
            }
            break;

        case 'generate_qr':
            // Générer un QR code standard
            if (!initSDK()) {
                respondError('Impossible d\'initialiser le SDK');
                break;
            }
            
            $sunuid->init();
            $result = $sunuid->generateQR('https://test.sunuid.sn/auth', [
                'type' => 2,
                'theme' => 'light',
                'size' => 300
            ]);
            
            if ($result['success']) {
                respondSuccess([
                    'qr_code' => $result['data']['qr_code'],
                    'session_id' => $result['data']['session_id'],
                    'url' => $result['data']['url']
                ]);
            } else {
                respondError($result['error']);
            }
            break;

        case 'generate_qr_websocket':
            // Générer un QR code avec Socket.IO
            if (!initSDK()) {
                respondError('Impossible d\'initialiser le SDK');
                break;
            }
            
            $sunuid->init();
            $sunuid->initWebSocket();
            
            $result = $sunuid->generateQRWithWebSocket('https://test.sunuid.sn/auth', [
                'type' => 2,
                'theme' => 'light',
                'size' => 300,
                'custom_data' => [
                    'test_mode' => true,
                    'test_session' => uniqid()
                ]
            ]);
            
            if ($result['success']) {
                respondSuccess([
                    'qr_code' => $result['data']['qr_code'],
                    'session_id' => $result['data']['session_id'],
                    'url' => $result['data']['url']
                ]);
            } else {
                respondError($result['error']);
            }
            break;

        case 'setup_callbacks':
            // Configurer les callbacks d'événements
            if (!initSDK()) {
                respondError('Impossible d\'initialiser le SDK');
                break;
            }
            
            $callbackCount = 0;
            
            // Callback de connexion
            $sunuid->onWebSocketEvent('connect', function ($data) use (&$callbackCount) {
                $callbackCount++;
                error_log("WebSocket connect: " . json_encode($data));
            });
            
            // Callback d'authentification réussie
            $sunuid->onWebSocketEvent('auth_success', function ($data) use (&$callbackCount) {
                $callbackCount++;
                error_log("Auth success: " . json_encode($data));
            });
            
            // Callback KYC complété
            $sunuid->onWebSocketEvent('kyc_complete', function ($data) use (&$callbackCount) {
                $callbackCount++;
                error_log("KYC complete: " . json_encode($data));
            });
            
            // Callback d'échec d'authentification
            $sunuid->onWebSocketEvent('auth_failure', function ($data) use (&$callbackCount) {
                $callbackCount++;
                error_log("Auth failure: " . json_encode($data));
            });
            
            // Callback KYC en cours
            $sunuid->onWebSocketEvent('kyc_pending', function ($data) use (&$callbackCount) {
                $callbackCount++;
                error_log("KYC pending: " . json_encode($data));
            });
            
            // Callback d'erreur
            $sunuid->onWebSocketEvent('error', function ($data) use (&$callbackCount) {
                $callbackCount++;
                error_log("WebSocket error: " . json_encode($data));
            });
            
            respondSuccess([
                'callback_count' => $callbackCount
            ]);
            break;

        case 'simulate_event':
            // Simuler un événement
            if (!initSDK()) {
                respondError('Impossible d\'initialiser le SDK');
                break;
            }
            
            $postData = getPostData();
            $eventType = $postData['event'] ?? '';
            
            if (empty($eventType)) {
                respondError('Type d\'événement manquant');
                break;
            }
            
            // Données d'événement simulées
            $eventData = [
                'auth_success' => [
                    'session_id' => 'test_session_' . uniqid(),
                    'user_id' => 'test_user_' . uniqid(),
                    'device_info' => [
                        'model' => 'iPhone 14',
                        'os' => 'iOS 17.0',
                        'app_version' => '2.1.0'
                    ],
                    'timestamp' => time()
                ],
                'kyc_complete' => [
                    'session_id' => 'test_session_' . uniqid(),
                    'kyc_data' => [
                        'user_info' => [
                            'name' => 'John Doe',
                            'email' => 'john.doe@example.com',
                            'phone' => '+221 77 777 77 77',
                            'id' => 'user_' . uniqid()
                        ],
                        'verification_status' => 'verified',
                        'documents' => [
                            'identity_card' => 'verified',
                            'selfie' => 'verified',
                            'proof_of_address' => 'verified'
                        ]
                    ],
                    'timestamp' => time()
                ],
                'auth_failure' => [
                    'session_id' => 'test_session_' . uniqid(),
                    'reason' => 'Timeout d\'authentification',
                    'error_code' => 'AUTH_TIMEOUT',
                    'timestamp' => time()
                ]
            ];
            
            if (isset($eventData[$eventType])) {
                // Déclencher l'événement via le callback
                $callbacks = $sunuid->getWebSocketCallbacks();
                if (isset($callbacks[$eventType])) {
                    foreach ($callbacks[$eventType] as $callback) {
                        $callback($eventData[$eventType]);
                    }
                }
                
                respondSuccess([
                    'event_simulated' => $eventType,
                    'data' => $eventData[$eventType]
                ]);
            } else {
                respondError('Type d\'événement non supporté: ' . $eventType);
            }
            break;

        case 'send_message':
            // Envoyer un message via Socket.IO
            if (!initSDK()) {
                respondError('Impossible d\'initialiser le SDK');
                break;
            }
            
            $postData = getPostData();
            $message = $postData['message'] ?? [];
            
            if (empty($message)) {
                respondError('Message manquant');
                break;
            }
            
            $sent = $sunuid->sendWebSocketMessage($message);
            respondSuccess([
                'sent' => $sent,
                'message' => $message
            ]);
            break;

        case 'subscribe_session':
            // S'abonner à une session
            if (!initSDK()) {
                respondError('Impossible d\'initialiser le SDK');
                break;
            }
            
            $postData = getPostData();
            $sessionId = $postData['session_id'] ?? '';
            
            if (empty($sessionId)) {
                respondError('Session ID manquant');
                break;
            }
            
            $subscribed = $sunuid->subscribeToSession($sessionId);
            respondSuccess([
                'subscribed' => $subscribed,
                'session_id' => $sessionId
            ]);
            break;

        case 'unsubscribe_session':
            // Se désabonner d'une session
            if (!initSDK()) {
                respondError('Impossible d\'initialiser le SDK');
                break;
            }
            
            $postData = getPostData();
            $sessionId = $postData['session_id'] ?? '';
            
            if (empty($sessionId)) {
                respondError('Session ID manquant');
                break;
            }
            
            $unsubscribed = $sunuid->unsubscribeFromSession($sessionId);
            respondSuccess([
                'unsubscribed' => $unsubscribed,
                'session_id' => $sessionId
            ]);
            break;

        case 'get_sessions':
            // Obtenir les sessions actives
            if (!initSDK()) {
                respondError('Impossible d\'initialiser le SDK');
                break;
            }
            
            $sessions = $sunuid->getWebSocketActiveSessions();
            $sessionList = [];
            
            foreach ($sessions as $sid => $sessionData) {
                $sessionList[] = [
                    'id' => $sid,
                    'status' => $sessionData['status'] ?? 'unknown',
                    'created_at' => $sessionData['created_at'] ?? time()
                ];
            }
            
            respondSuccess([
                'sessions' => $sessionList
            ]);
            break;

        case 'get_metrics':
            // Obtenir les métriques de performance
            if (!initSDK()) {
                respondError('Impossible d\'initialiser le SDK');
                break;
            }
            
            $metrics = $sunuid->getWebSocketMetrics();
            respondSuccess([
                'metrics' => $metrics ?: [
                    'messages_sent' => 0,
                    'messages_received' => 0,
                    'errors' => 0,
                    'reconnections' => 0,
                    'uptime' => 0
                ]
            ]);
            break;

        case 'run_complete_test':
            // Lancer le test complet
            $startTime = microtime(true);
            $testResults = [];
            
            // Test 1: Initialisation du SDK
            $testResults['sdk_init'] = initSDK();
            
            if ($testResults['sdk_init']) {
                // Test 2: Initialisation Socket.IO
                $testResults['websocket_init'] = $sunuid->initWebSocket();
                
                // Test 3: Configuration des callbacks
                $callbackCount = 0;
                $sunuid->onWebSocketEvent('connect', function ($data) use (&$callbackCount) {
                    $callbackCount++;
                });
                $sunuid->onWebSocketEvent('auth_success', function ($data) use (&$callbackCount) {
                    $callbackCount++;
                });
                $sunuid->onWebSocketEvent('kyc_complete', function ($data) use (&$callbackCount) {
                    $callbackCount++;
                });
                $testResults['callbacks_config'] = $callbackCount > 0;
                
                // Test 4: Tentative de connexion
                $testResults['connection_attempt'] = true;
                $testResults['connection_success'] = $sunuid->connectWebSocket();
                
                // Test 5: Gestion des sessions
                $sessionId = 'test_session_' . uniqid();
                $testResults['session_subscription'] = $sunuid->subscribeToSession($sessionId);
                $testResults['session_unsubscription'] = $sunuid->unsubscribeFromSession($sessionId);
                
                // Test 6: Envoi de messages
                $testMessage = [
                    'event' => 'test_message',
                    'data' => [
                        'message' => 'Hello Socket.IO!',
                        'timestamp' => time(),
                        'test_id' => uniqid()
                    ]
                ];
                $testResults['message_sent'] = $sunuid->sendWebSocketMessage($testMessage);
                
                // Test 7: Génération QR
                $sunuid->init();
                $qrResult = $sunuid->generateQRWithWebSocket('https://test.sunuid.sn/auth', [
                    'type' => 2,
                    'theme' => 'light',
                    'size' => 300
                ]);
                $testResults['qr_generation'] = $qrResult['success'];
                
                // Test 8: Déconnexion
                $sunuid->disconnectWebSocket();
                $testResults['disconnection'] = !$sunuid->isWebSocketConnected();
                
                // Test 9: Métriques
                $metrics = $sunuid->getWebSocketMetrics();
                $testResults['metrics_available'] = !empty($metrics);
            }
            
            $endTime = microtime(true);
            $executionTime = round($endTime - $startTime, 2);
            
            $successCount = count(array_filter($testResults, function($result) {
                return $result === true || (is_numeric($result) && $result > 0);
            }));
            
            $successRate = count($testResults) > 0 ? round(($successCount / count($testResults)) * 100, 2) : 0;
            
            respondSuccess([
                'execution_time' => $executionTime,
                'success_rate' => $successRate,
                'results' => $testResults
            ]);
            break;

        case 'export_results':
            // Exporter les résultats de test
            $resultsFile = 'test-results-' . date('Y-m-d-H-i-s') . '.json';
            $results = [
                'timestamp' => date('Y-m-d H:i:s'),
                'config' => $testConfig,
                'results' => $testResults,
                'execution_time' => 0,
                'success_rate' => 0
            ];
            
            file_put_contents($resultsFile, json_encode($results, JSON_PRETTY_PRINT));
            
            respondSuccess([
                'filename' => $resultsFile
            ]);
            break;

        default:
            respondError('Action non reconnue: ' . $action, 404);
            break;
    }
    
} catch (Exception $e) {
    respondError('Erreur serveur: ' . $e->getMessage(), 500);
}


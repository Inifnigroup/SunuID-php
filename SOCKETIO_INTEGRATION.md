# 🔌 Intégration Socket.IO - SDK SunuID PHP

## 📋 Vue d'ensemble

Le SDK SunuID PHP intègre **Socket.IO** pour permettre la communication en temps réel avec l'API SunuID. Cette intégration permet de recevoir des notifications instantanées lors de l'authentification et du processus KYC, offrant une expérience utilisateur fluide et réactive.

## 🚀 Configuration

### Configuration de base

```php
$config = [
    'client_id' => 'votre_client_id',
    'secret_id' => 'votre_secret_id',
    'partner_name' => 'Votre Entreprise',
    'enable_websocket' => true,
    'websocket_url' => 'wss://samasocket.fayma.sn:9443',
    'websocket_socketio_version' => '2',
    'websocket_transports' => ['websocket', 'polling'],
    'websocket_query_params' => [
        'custom_param' => 'custom_value'
    ],
    'enable_logs' => true,
    'log_level' => \Monolog\Logger::INFO,
    'log_file' => 'sunuid-socketio.log'
];
```

### Paramètres de configuration

| Paramètre | Type | Défaut | Description |
|-----------|------|--------|-------------|
| `enable_websocket` | bool | `false` | Activer les Socket.IO |
| `websocket_url` | string | `wss://samasocket.fayma.sn:9443` | URL du serveur Socket.IO |
| `websocket_socketio_version` | string | `2` | Version Socket.IO (0, 1, 2) |
| `websocket_transports` | array | `['websocket', 'polling']` | Transports supportés |
| `websocket_query_params` | array | `[]` | Paramètres de requête additionnels |
| `enable_logs` | bool | `false` | Activer les logs détaillés |
| `log_level` | int | `INFO` | Niveau de log (DEBUG, INFO, WARNING, ERROR) |
| `log_file` | string | `sunuid.log` | Fichier de log |

## 🔧 Utilisation

### Initialisation et connexion

```php
use SunuID\SunuID;

$sunuid = new SunuID($config);

// Initialiser Socket.IO
if ($sunuid->initWebSocket()) {
    echo "✅ Socket.IO initialisé";
    
    // Se connecter
    if ($sunuid->connectWebSocket()) {
        echo "✅ Connecté au Socket.IO";
    } else {
        echo "❌ Échec de connexion Socket.IO";
    }
}
```

### Écoute d'événements

```php
// Authentification réussie
$sunuid->onWebSocketEvent('auth_success', function ($data) {
    echo "✅ Authentification réussie pour la session: " . $data['session_id'];
    echo "👤 Utilisateur: " . $data['user_id'];
    echo "📱 Appareil: " . $data['device_info']['model'] ?? 'N/A';
    
    // Rediriger l'utilisateur ou mettre à jour l'interface
    redirectToDashboard($data['user_id']);
});

// KYC complété
$sunuid->onWebSocketEvent('kyc_complete', function ($data) {
    echo "✅ KYC complété pour la session: " . $data['session_id'];
    
    $kycData = $data['kyc_data'];
    echo "📋 Données KYC reçues:";
    echo "   - Nom: " . $kycData['user_info']['name'];
    echo "   - Email: " . $kycData['user_info']['email'];
    echo "   - Statut: " . $kycData['verification_status'];
    
    // Sauvegarder en base de données
    saveKycData($kycData);
    
    // Notifier l'utilisateur
    notifyUserKycComplete($kycData['user_info']['email']);
});

// Échec d'authentification
$sunuid->onWebSocketEvent('auth_failure', function ($data) {
    echo "❌ Échec d'authentification: " . $data['reason'];
    echo "🔍 Code d'erreur: " . $data['error_code'] ?? 'N/A';
    
    // Afficher un message d'erreur approprié
    showErrorMessage($data['reason'], $data['error_code']);
});

// Session expirée
$sunuid->onWebSocketEvent('session_expired', function ($data) {
    echo "⏰ Session expirée: " . $data['session_id'];
    echo "🕐 Expirée à: " . date('Y-m-d H:i:s', $data['expired_at']);
    
    // Nettoyer les données de session
    cleanupSession($data['session_id']);
    
    // Rediriger vers la page de connexion
    redirectToLogin();
});

// KYC en cours
$sunuid->onWebSocketEvent('kyc_pending', function ($data) {
    echo "⏳ KYC en cours pour la session: " . $data['session_id'];
    echo "📝 Étapes restantes: " . implode(', ', $data['pending_steps']);
    echo "✅ Étapes complétées: " . implode(', ', $data['completed_steps']);
    
    // Mettre à jour l'interface utilisateur
    updateKycProgress($data['pending_steps'], $data['completed_steps']);
});

// Connexion établie
$sunuid->onWebSocketEvent('connect', function ($data) {
    echo "🔗 Connexion Socket.IO établie";
    echo "🆔 Session Socket: " . $data['socket_id'] ?? 'N/A';
    
    // Marquer comme connecté
    setConnectionStatus(true);
});

// Déconnexion
$sunuid->onWebSocketEvent('disconnect', function ($data) {
    echo "🔌 Déconnexion Socket.IO";
    echo "📊 Raison: " . $data['reason'] ?? 'Unknown';
    
    // Marquer comme déconnecté
    setConnectionStatus(false);
    
    // Tenter une reconnexion automatique
    if ($data['reason'] !== 'io client disconnect') {
        scheduleReconnection();
    }
});

// Erreur de connexion
$sunuid->onWebSocketEvent('error', function ($data) {
    echo "❌ Erreur Socket.IO: " . $data['error'];
    echo "🔍 Type: " . $data['error_type'] ?? 'Unknown';
    
    // Logger l'erreur
    logError('socketio_error', $data);
    
    // Tenter une reconnexion si nécessaire
    if (shouldAttemptReconnection($data['error_type'])) {
        scheduleReconnection();
    }
});
```

### Gestion des sessions

```php
// S'abonner à une session
$sessionId = 'session_123';
$subscribed = $sunuid->subscribeToSession($sessionId);

if ($subscribed) {
    echo "✅ Abonné à la session: $sessionId";
} else {
    echo "❌ Échec d'abonnement à la session: $sessionId";
}

// Se désabonner d'une session
$unsubscribed = $sunuid->unsubscribeFromSession($sessionId);

if ($unsubscribed) {
    echo "✅ Désabonné de la session: $sessionId";
} else {
    echo "❌ Échec de désabonnement de la session: $sessionId";
}

// Obtenir les sessions actives
$activeSessions = $sunuid->getWebSocketActiveSessions();

echo "📋 Sessions actives: " . count($activeSessions);
foreach ($activeSessions as $sid => $sessionData) {
    echo "   - $sid: " . ($sessionData['status'] ?? 'unknown');
    echo "     Créée: " . date('Y-m-d H:i:s', $sessionData['created_at'] ?? time());
}
```

### Envoi de messages

```php
// Envoyer un message personnalisé
$message = [
    'event' => 'custom_event',
    'data' => [
        'message' => 'Hello Socket.IO!',
        'timestamp' => time(),
        'user_id' => 'user_123',
        'action' => 'test_message'
    ]
];

$sent = $sunuid->sendWebSocketMessage($message);

if ($sent) {
    echo "✅ Message envoyé avec succès";
} else {
    echo "❌ Échec d'envoi du message";
}

// Envoyer un ping
$pingMessage = [
    'event' => 'ping',
    'data' => [
        'id' => uniqid(),
        'timestamp' => microtime(true)
    ]
];

$sunuid->sendWebSocketMessage($pingMessage);
```

### Génération QR avec Socket.IO automatique

```php
// Générer un QR code avec abonnement automatique à la session
$qrOptions = [
    'type' => 2, // Authentification (1 = KYC, 2 = Auth)
    'theme' => 'light', // light, dark
    'size' => 300, // Taille en pixels
    'redirect_url' => 'https://votre-site.com/auth/callback',
    'custom_data' => [
        'user_id' => 'user_123',
        'session_type' => 'login'
    ]
];

$result = $sunuid->generateQRWithWebSocket('https://votre-site.com/auth', $qrOptions);

if ($result['success']) {
    echo "✅ QR code généré avec succès";
    echo "📱 Session ID: " . $result['data']['session_id'];
    echo "🔗 URL: " . $result['data']['url'];
    echo "📊 QR Code: " . $result['data']['qr_code'];
    
    // L'utilisateur peut scanner le QR code
    // Les notifications arriveront automatiquement via Socket.IO
    displayQRCode($result['data']['qr_code']);
} else {
    echo "❌ Erreur lors de la génération du QR code: " . $result['error'];
}
```

## 📡 Événements supportés

### Événements système
- `connect` - Connexion établie
- `disconnect` - Déconnexion
- `error` - Erreur de connexion
- `message` - Message générique
- `reconnect` - Reconnexion automatique
- `reconnect_attempt` - Tentative de reconnexion

### Événements métier
- `auth_success` - Authentification réussie
- `auth_failure` - Échec d'authentification
- `kyc_complete` - KYC complété
- `kyc_pending` - KYC en attente
- `kyc_failed` - KYC échoué
- `session_expired` - Session expirée
- `user_registered` - Utilisateur enregistré
- `verification_complete` - Vérification complétée

## 🔗 Configuration Socket.IO

### Paramètres de connexion automatiques

Le SDK configure automatiquement les paramètres suivants lors de la connexion :

```php
$queryParams = [
    'token' => $config['client_id'],
    'type' => 'web',
    'userId' => $config['client_id'],
    'username' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
    'version' => '1.0.0',
    'platform' => 'php-sdk'
];
```

### Transports supportés

- **WebSocket** : Connexion bidirectionnelle en temps réel (recommandé)
- **Polling** : Fallback pour les environnements sans WebSocket
- **Long Polling** : Alternative pour les proxies restrictifs

### Configuration avancée

```php
$advancedConfig = [
    'websocket_timeout' => 20000, // Timeout en ms
    'websocket_force_new' => true, // Forcer une nouvelle connexion
    'websocket_auto_connect' => true, // Connexion automatique
    'websocket_reconnection' => true, // Reconnexion automatique
    'websocket_reconnection_delay' => 1000, // Délai de reconnexion
    'websocket_max_reconnection_attempts' => 5, // Nombre max de tentatives
    'websocket_randomization_factor' => 0.5, // Facteur de randomisation
];
```

## 🛠️ Gestion d'erreurs

### Exceptions courantes

```php
try {
    $sunuid->connectWebSocket();
} catch (ServerConnectionFailureException $e) {
    echo "❌ Impossible de se connecter au serveur Socket.IO";
    echo "🔍 Vérifiez l'URL et la connectivité réseau";
    logError('connection_failure', $e->getMessage());
} catch (AuthenticationException $e) {
    echo "❌ Erreur d'authentification Socket.IO";
    echo "🔑 Vérifiez vos credentials";
    logError('auth_failure', $e->getMessage());
} catch (TimeoutException $e) {
    echo "⏰ Timeout de connexion Socket.IO";
    echo "🔄 Tentative de reconnexion...";
    scheduleReconnection();
} catch (Exception $e) {
    echo "❌ Erreur générale: " . $e->getMessage();
    logError('general_error', $e->getMessage());
}
```

### Reconnexion automatique

Le SDK gère automatiquement :
- Tentatives de reconnexion avec backoff exponentiel
- Gestion des timeouts
- Nettoyage des sessions expirées
- Récupération des abonnements après reconnexion

```php
// Configuration de la reconnexion
$reconnectionConfig = [
    'enabled' => true,
    'max_attempts' => 5,
    'initial_delay' => 1000, // 1 seconde
    'max_delay' => 30000, // 30 secondes
    'backoff_multiplier' => 2
];

$sunuid->setReconnectionConfig($reconnectionConfig);
```

## 📊 Monitoring et Logs

### Configuration des logs

```php
$logConfig = [
    'enable_logs' => true,
    'log_level' => \Monolog\Logger::INFO,
    'log_file' => 'sunuid-socketio.log',
    'log_format' => '[%datetime%] %channel%.%level_name%: %message% %context%',
    'log_date_format' => 'Y-m-d H:i:s'
];

$sunuid->setLogConfig($logConfig);
```

### Statut de connexion

```php
// Vérifier l'état de connexion
if ($sunuid->isWebSocketConnected()) {
    echo "✅ Socket.IO connecté";
    
    $connectionInfo = $sunuid->getWebSocketConnectionInfo();
    echo "📊 Informations de connexion:";
    echo "   - Socket ID: " . $connectionInfo['socket_id'];
    echo "   - Connecté depuis: " . date('Y-m-d H:i:s', $connectionInfo['connected_at']);
    echo "   - Sessions actives: " . count($connectionInfo['active_sessions']);
} else {
    echo "❌ Socket.IO déconnecté";
    
    // Tenter une reconnexion
    if ($sunuid->canReconnect()) {
        echo "🔄 Tentative de reconnexion...";
        $sunuid->reconnectWebSocket();
    }
}
```

### Métriques de performance

```php
$metrics = $sunuid->getWebSocketMetrics();

echo "📈 Métriques Socket.IO:";
echo "   - Messages envoyés: " . $metrics['messages_sent'];
echo "   - Messages reçus: " . $metrics['messages_received'];
echo "   - Erreurs: " . $metrics['errors'];
echo "   - Reconnexions: " . $metrics['reconnections'];
echo "   - Temps de connexion: " . $metrics['uptime'] . " secondes";
```

## 🚀 Exemples complets

### Exemple 1: Authentification avec notifications

```php
<?php
require_once 'vendor/autoload.php';

use SunuID\SunuID;

class AuthManager {
    private $sunuid;
    private $isAuthenticated = false;
    
    public function __construct($config) {
        $this->sunuid = new SunuID($config);
        $this->setupCallbacks();
    }
    
    private function setupCallbacks() {
        // Authentification réussie
        $this->sunuid->onWebSocketEvent('auth_success', function ($data) {
            $this->handleAuthSuccess($data);
        });
        
        // Échec d'authentification
        $this->sunuid->onWebSocketEvent('auth_failure', function ($data) {
            $this->handleAuthFailure($data);
        });
        
        // Connexion établie
        $this->sunuid->onWebSocketEvent('connect', function ($data) {
            echo "🔗 Connecté au serveur d'authentification\n";
        });
    }
    
    private function handleAuthSuccess($data) {
        $this->isAuthenticated = true;
        
        echo "🎉 Authentification réussie!\n";
        echo "👤 Utilisateur: " . $data['user_id'] . "\n";
        echo "📱 Appareil: " . ($data['device_info']['model'] ?? 'N/A') . "\n";
        
        // Sauvegarder les informations de session
        $this->saveSession($data);
        
        // Rediriger vers le dashboard
        $this->redirectToDashboard($data['user_id']);
    }
    
    private function handleAuthFailure($data) {
        echo "❌ Échec d'authentification: " . $data['reason'] . "\n";
        
        // Afficher un message d'erreur approprié
        $this->showErrorMessage($data['reason'], $data['error_code'] ?? null);
    }
    
    public function startAuth() {
        // Initialiser Socket.IO
        if (!$this->sunuid->initWebSocket()) {
            throw new Exception("Impossible d'initialiser Socket.IO");
        }
        
        // Se connecter
        if (!$this->sunuid->connectWebSocket()) {
            throw new Exception("Impossible de se connecter au serveur");
        }
        
        // Générer un QR code
        $result = $this->sunuid->generateQRWithWebSocket('https://monapp.com/auth', [
            'type' => 2,
            'theme' => 'light',
            'size' => 300
        ]);
        
        if ($result['success']) {
            echo "📱 QR code généré: " . $result['data']['url'] . "\n";
            echo "⏳ En attente de l'authentification...\n";
            
            return $result['data'];
        } else {
            throw new Exception("Erreur lors de la génération du QR code: " . $result['error']);
        }
    }
    
    private function saveSession($data) {
        // Sauvegarder en base de données
        $sessionData = [
            'user_id' => $data['user_id'],
            'session_id' => $data['session_id'],
            'authenticated_at' => time(),
            'device_info' => $data['device_info'] ?? []
        ];
        
        // Implémentation de sauvegarde...
    }
    
    private function redirectToDashboard($userId) {
        // Redirection vers le dashboard
        header("Location: /dashboard?user_id=" . urlencode($userId));
        exit;
    }
    
    private function showErrorMessage($reason, $errorCode) {
        // Afficher un message d'erreur à l'utilisateur
        echo "<div class='error'>Erreur d'authentification: $reason</div>";
    }
}

// Utilisation
$config = [
    'client_id' => 'votre_client_id',
    'secret_id' => 'votre_secret_id',
    'partner_name' => 'Votre Entreprise',
    'enable_websocket' => true
];

try {
    $authManager = new AuthManager($config);
    $qrData = $authManager->startAuth();
    
    // Afficher le QR code
    echo "<img src='data:image/png;base64," . $qrData['qr_code'] . "' alt='QR Code'>";
    
} catch (Exception $e) {
    echo "Erreur: " . $e->getMessage();
}
```

### Exemple 2: KYC avec suivi en temps réel

```php
<?php
require_once 'vendor/autoload.php';

use SunuID\SunuID;

class KYCManager {
    private $sunuid;
    private $kycStatus = 'pending';
    private $kycData = null;
    
    public function __construct($config) {
        $this->sunuid = new SunuID($config);
        $this->setupCallbacks();
    }
    
    private function setupCallbacks() {
        // KYC en cours
        $this->sunuid->onWebSocketEvent('kyc_pending', function ($data) {
            $this->handleKycPending($data);
        });
        
        // KYC complété
        $this->sunuid->onWebSocketEvent('kyc_complete', function ($data) {
            $this->handleKycComplete($data);
        });
        
        // KYC échoué
        $this->sunuid->onWebSocketEvent('kyc_failed', function ($data) {
            $this->handleKycFailed($data);
        });
    }
    
    private function handleKycPending($data) {
        $this->kycStatus = 'pending';
        
        echo "⏳ KYC en cours...\n";
        echo "📝 Étapes restantes: " . implode(', ', $data['pending_steps']) . "\n";
        echo "✅ Étapes complétées: " . implode(', ', $data['completed_steps']) . "\n";
        
        // Mettre à jour l'interface utilisateur
        $this->updateProgressUI($data['pending_steps'], $data['completed_steps']);
    }
    
    private function handleKycComplete($data) {
        $this->kycStatus = 'completed';
        $this->kycData = $data['kyc_data'];
        
        echo "✅ KYC complété avec succès!\n";
        
        $userInfo = $data['kyc_data']['user_info'];
        echo "👤 Nom: " . $userInfo['name'] . "\n";
        echo "📧 Email: " . $userInfo['email'] . "\n";
        echo "📱 Téléphone: " . $userInfo['phone'] . "\n";
        echo "🆔 Statut: " . $data['kyc_data']['verification_status'] . "\n";
        
        // Sauvegarder les données KYC
        $this->saveKycData($data['kyc_data']);
        
        // Notifier l'utilisateur
        $this->notifyUserKycComplete($userInfo['email']);
        
        // Rediriger vers la page de succès
        $this->redirectToSuccess();
    }
    
    private function handleKycFailed($data) {
        $this->kycStatus = 'failed';
        
        echo "❌ KYC échoué: " . $data['reason'] . "\n";
        echo "🔍 Code d'erreur: " . $data['error_code'] . "\n";
        
        if (isset($data['retry_available']) && $data['retry_available']) {
            echo "🔄 Nouvelle tentative disponible\n";
            $this->showRetryOption();
        } else {
            echo "❌ Aucune nouvelle tentative disponible\n";
            $this->showFailureMessage($data['reason']);
        }
    }
    
    public function startKYC($userId) {
        // Initialiser Socket.IO
        if (!$this->sunuid->initWebSocket()) {
            throw new Exception("Impossible d'initialiser Socket.IO");
        }
        
        // Se connecter
        if (!$this->sunuid->connectWebSocket()) {
            throw new Exception("Impossible de se connecter au serveur");
        }
        
        // Générer un QR code pour KYC
        $result = $this->sunuid->generateQRWithWebSocket('https://monapp.com/kyc', [
            'type' => 1, // KYC
            'theme' => 'light',
            'size' => 300,
            'custom_data' => [
                'user_id' => $userId,
                'kyc_type' => 'full'
            ]
        ]);
        
        if ($result['success']) {
            echo "📱 QR code KYC généré\n";
            echo "🆔 Session ID: " . $result['data']['session_id'] . "\n";
            
            return $result['data'];
        } else {
            throw new Exception("Erreur lors de la génération du QR code KYC: " . $result['error']);
        }
    }
    
    private function updateProgressUI($pendingSteps, $completedSteps) {
        // Mettre à jour l'interface utilisateur avec la progression
        $totalSteps = count($pendingSteps) + count($completedSteps);
        $progress = (count($completedSteps) / $totalSteps) * 100;
        
        echo "<div class='progress-bar'>";
        echo "<div class='progress' style='width: {$progress}%'></div>";
        echo "</div>";
        
        echo "<div class='steps'>";
        foreach ($completedSteps as $step) {
            echo "<div class='step completed'>✅ $step</div>";
        }
        foreach ($pendingSteps as $step) {
            echo "<div class='step pending'>⏳ $step</div>";
        }
        echo "</div>";
    }
    
    private function saveKycData($kycData) {
        // Sauvegarder les données KYC en base de données
        $data = [
            'user_id' => $kycData['user_info']['id'],
            'name' => $kycData['user_info']['name'],
            'email' => $kycData['user_info']['email'],
            'phone' => $kycData['user_info']['phone'],
            'verification_status' => $kycData['verification_status'],
            'kyc_data' => json_encode($kycData),
            'completed_at' => time()
        ];
        
        // Implémentation de sauvegarde...
    }
    
    private function notifyUserKycComplete($email) {
        // Envoyer un email de confirmation
        $subject = "KYC complété avec succès";
        $message = "Votre processus KYC a été complété avec succès. Vous pouvez maintenant accéder à tous les services.";
        
        // Implémentation d'envoi d'email...
    }
    
    private function redirectToSuccess() {
        header("Location: /kyc/success");
        exit;
    }
    
    private function showRetryOption() {
        echo "<div class='retry-option'>";
        echo "<p>Le KYC a échoué, mais vous pouvez réessayer.</p>";
        echo "<button onclick='retryKYC()'>🔄 Réessayer</button>";
        echo "</div>";
    }
    
    private function showFailureMessage($reason) {
        echo "<div class='failure-message'>";
        echo "<p>Le KYC a échoué: $reason</p>";
        echo "<p>Veuillez contacter le support pour plus d'assistance.</p>";
        echo "</div>";
    }
    
    public function getStatus() {
        return [
            'status' => $this->kycStatus,
            'data' => $this->kycData
        ];
    }
}

// Utilisation
$config = [
    'client_id' => 'votre_client_id',
    'secret_id' => 'votre_secret_id',
    'partner_name' => 'Votre Entreprise',
    'enable_websocket' => true
];

try {
    $kycManager = new KYCManager($config);
    $qrData = $kycManager->startKYC('user_123');
    
    // Afficher le QR code
    echo "<img src='data:image/png;base64," . $qrData['qr_code'] . "' alt='QR Code KYC'>";
    
    // Attendre la completion du KYC
    while (true) {
        $status = $kycManager->getStatus();
        if ($status['status'] === 'completed' || $status['status'] === 'failed') {
            break;
        }
        sleep(1);
    }
    
} catch (Exception $e) {
    echo "Erreur: " . $e->getMessage();
}
```

### Exemple 3: Application web complète

```php
<?php
// index.php - Page principale
require_once 'vendor/autoload.php';

use SunuID\SunuID;

session_start();

$config = [
    'client_id' => $_ENV['SUNUID_CLIENT_ID'],
    'secret_id' => $_ENV['SUNUID_SECRET_ID'],
    'partner_name' => 'Mon Application',
    'enable_websocket' => true,
    'enable_logs' => true
];

$sunuid = new SunuID($config);

// Initialiser Socket.IO
$sunuid->initWebSocket();
$sunuid->connectWebSocket();

// Configurer les callbacks
$sunuid->onWebSocketEvent('auth_success', function ($data) {
    $_SESSION['user_id'] = $data['user_id'];
    $_SESSION['authenticated'] = true;
    
    // Rediriger via JavaScript
    echo "<script>window.location.href = '/dashboard';</script>";
});

$sunuid->onWebSocketEvent('auth_failure', function ($data) {
    echo "<script>showError('" . addslashes($data['reason']) . "');</script>";
});
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Authentification SunuID</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            text-align: center;
        }
        .qr-container {
            margin: 30px 0;
            padding: 20px;
            border: 2px solid #ddd;
            border-radius: 10px;
        }
        .qr-code {
            max-width: 300px;
            margin: 20px auto;
        }
        .status {
            margin: 20px 0;
            padding: 10px;
            border-radius: 5px;
        }
        .status.pending {
            background-color: #fff3cd;
            color: #856404;
        }
        .status.success {
            background-color: #d4edda;
            color: #155724;
        }
        .status.error {
            background-color: #f8d7da;
            color: #721c24;
        }
        .progress-bar {
            width: 100%;
            height: 20px;
            background-color: #f0f0f0;
            border-radius: 10px;
            overflow: hidden;
            margin: 20px 0;
        }
        .progress {
            height: 100%;
            background-color: #007bff;
            transition: width 0.3s ease;
        }
    </style>
</head>
<body>
    <h1>🔐 Authentification SunuID</h1>
    
    <div class="qr-container">
        <h2>📱 Scannez le QR code</h2>
        <p>Utilisez l'application SunuID pour vous authentifier</p>
        
        <div id="qr-code" class="qr-code">
            <!-- QR code sera affiché ici -->
        </div>
        
        <div id="status" class="status pending">
            ⏳ En attente de l'authentification...
        </div>
        
        <div class="progress-bar">
            <div id="progress" class="progress" style="width: 0%"></div>
        </div>
    </div>
    
    <script>
        // Générer le QR code via AJAX
        fetch('/generate-qr.php')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('qr-code').innerHTML = 
                        `<img src="data:image/png;base64,${data.qr_code}" alt="QR Code">`;
                    
                    // Simuler une progression
                    let progress = 0;
                    const interval = setInterval(() => {
                        progress += 1;
                        document.getElementById('progress').style.width = progress + '%';
                        
                        if (progress >= 100) {
                            clearInterval(interval);
                        }
                    }, 100);
                } else {
                    document.getElementById('status').innerHTML = 
                        `<div class="status error">❌ Erreur: ${data.error}</div>`;
                }
            })
            .catch(error => {
                document.getElementById('status').innerHTML = 
                    `<div class="status error">❌ Erreur de connexion</div>`;
            });
        
        function showError(message) {
            document.getElementById('status').innerHTML = 
                `<div class="status error">❌ ${message}</div>`;
        }
        
        function showSuccess(message) {
            document.getElementById('status').innerHTML = 
                `<div class="status success">✅ ${message}</div>`;
        }
    </script>
</body>
</html>
```

```php
<?php
// generate-qr.php - API pour générer le QR code
require_once 'vendor/autoload.php';

use SunuID\SunuID;

header('Content-Type: application/json');

$config = [
    'client_id' => $_ENV['SUNUID_CLIENT_ID'],
    'secret_id' => $_ENV['SUNUID_SECRET_ID'],
    'partner_name' => 'Mon Application',
    'enable_websocket' => true
];

try {
    $sunuid = new SunuID($config);
    
    // Initialiser Socket.IO
    $sunuid->initWebSocket();
    $sunuid->connectWebSocket();
    
    // Générer le QR code
    $result = $sunuid->generateQRWithWebSocket('https://monapp.com/auth', [
        'type' => 2,
        'theme' => 'light',
        'size' => 300
    ]);
    
    if ($result['success']) {
        echo json_encode([
            'success' => true,
            'qr_code' => $result['data']['qr_code'],
            'session_id' => $result['data']['session_id'],
            'url' => $result['data']['url']
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'error' => $result['error']
        ]);
    }
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
```

## 🔧 Dépendances

### Composer

```json
{
    "require": {
        "elephantio/elephant.io": "^3.3",
        "monolog/monolog": "^2.0",
        "guzzlehttp/guzzle": "^7.0"
    }
}
```

### Installation

```bash
composer require elephantio/elephant.io monolog/monolog guzzlehttp/guzzle
```

## 🎯 Avantages

### ✅ Avantages de l'intégration Socket.IO

1. **Notifications en temps réel** - Pas besoin de polling
2. **Réactivité immédiate** - Interface utilisateur mise à jour instantanément
3. **Réduction de la charge serveur** - Moins de requêtes HTTP
4. **Expérience utilisateur améliorée** - Feedback immédiat
5. **Gestion automatique des reconnexions** - Robustesse
6. **Support multi-transports** - WebSocket + Polling
7. **Configuration flexible** - Adaptable à différents environnements
8. **Logs détaillés** - Monitoring et debugging facilités
9. **Gestion d'erreurs robuste** - Récupération automatique
10. **Support multi-sessions** - Gestion de plusieurs utilisateurs

### 🔄 Comparaison avec l'approche polling

| Aspect | Socket.IO | Polling |
|--------|-----------|---------|
| **Latence** | ⚡ Immédiate | ⏱️ 1-5 secondes |
| **Charge serveur** | 🟢 Faible | 🔴 Élevée |
| **Bande passante** | 🟢 Optimale | 🔴 Excessive |
| **Expérience utilisateur** | 🟢 Excellente | 🟡 Correcte |
| **Complexité** | 🟡 Moyenne | 🟢 Simple |
| **Fiabilité** | 🟢 Élevée | 🟡 Moyenne |
| **Scalabilité** | 🟢 Excellente | 🔴 Limitée |

## 🚀 Prochaines étapes

### Améliorations futures

1. **Support Socket.IO v4** - Quand disponible dans ElephantIO
2. **Clustering** - Support multi-serveurs
3. **Compression** - Optimisation de la bande passante
4. **Authentification avancée** - JWT, OAuth
5. **Monitoring avancé** - Métriques de performance
6. **Support WebRTC** - Communication peer-to-peer
7. **Chiffrement end-to-end** - Sécurité renforcée
8. **Support mobile** - Applications natives

### Intégrations

1. **Laravel** - Package dédié avec événements
2. **Symfony** - Bundle avec services
3. **WordPress** - Plugin avec hooks
4. **Magento** - Extension avec observers
5. **Drupal** - Module avec hooks
6. **CodeIgniter** - Library avec helpers

## 🔒 Sécurité

### Bonnes pratiques

1. **Validation des données** - Toujours valider les données reçues
2. **Authentification** - Utiliser des tokens sécurisés
3. **Chiffrement** - Utiliser HTTPS/WSS
4. **Rate limiting** - Limiter les tentatives de connexion
5. **Logs de sécurité** - Tracer les événements suspects
6. **Mise à jour** - Maintenir les dépendances à jour

### Configuration sécurisée

```php
$secureConfig = [
    'client_id' => $_ENV['SUNUID_CLIENT_ID'],
    'secret_id' => $_ENV['SUNUID_SECRET_ID'],
    'enable_websocket' => true,
    'websocket_url' => 'wss://samasocket.fayma.sn:9443',
    'websocket_query_params' => [
        'token' => generateSecureToken(),
        'timestamp' => time(),
        'signature' => generateSignature()
    ],
    'enable_logs' => true,
    'log_level' => \Monolog\Logger::WARNING,
    'log_file' => '/var/log/sunuid-secure.log'
];
```

---

## 📞 Support

Pour toute question sur l'intégration Socket.IO :

- 📧 Email : dev@sunuid.sn
- 📖 Documentation : https://docs.sunuid.sn
- 🐙 GitHub : https://github.com/sunuid/php-sdk
- 💬 Discord : https://discord.gg/sunuid
- 📱 WhatsApp : +221 77 777 77 77

**🎉 L'intégration Socket.IO est maintenant prête pour la production !**

---

*Dernière mise à jour : <?php echo date('Y-m-d H:i:s'); ?>* 
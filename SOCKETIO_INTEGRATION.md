# 🔌 Intégration Socket.IO - SDK SunuID PHP

## 📋 Vue d'ensemble

Le SDK SunuID PHP intègre maintenant **Socket.IO** pour permettre la communication en temps réel avec l'API SunuID. Cette intégration permet de recevoir des notifications instantanées lors de l'authentification et du processus KYC.

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
    ]
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

## 🔧 Utilisation

### Initialisation et connexion

```php
use SunuID\SunuID;

$sunuid = new SunuID($config);

// Initialiser Socket.IO
if ($sunuid->initWebSocket()) {
    // Se connecter
    if ($sunuid->connectWebSocket()) {
        echo "✅ Connecté au Socket.IO";
    }
}
```

### Écoute d'événements

```php
// Authentification réussie
$sunuid->onWebSocketEvent('auth_success', function ($data) {
    echo "✅ Authentification réussie pour la session: " . $data['session_id'];
    // Rediriger l'utilisateur ou mettre à jour l'interface
});

// KYC complété
$sunuid->onWebSocketEvent('kyc_complete', function ($data) {
    echo "✅ KYC complété pour la session: " . $data['session_id'];
    // Traiter les données KYC
    processKycData($data['kyc_data']);
});

// Échec d'authentification
$sunuid->onWebSocketEvent('auth_failure', function ($data) {
    echo "❌ Échec d'authentification: " . $data['reason'];
    // Afficher un message d'erreur
});

// Session expirée
$sunuid->onWebSocketEvent('session_expired', function ($data) {
    echo "⏰ Session expirée: " . $data['session_id'];
    // Nettoyer les données de session
});
```

### Gestion des sessions

```php
// S'abonner à une session
$sunuid->subscribeToSession('session_id_123');

// Se désabonner d'une session
$sunuid->unsubscribeFromSession('session_id_123');

// Obtenir les sessions actives
$activeSessions = $sunuid->getWebSocketActiveSessions();
```

### Envoi de messages

```php
// Envoyer un message personnalisé
$sunuid->sendWebSocketMessage([
    'event' => 'custom_event',
    'data' => [
        'message' => 'Hello Socket.IO!',
        'timestamp' => time(),
        'user_id' => 'user_123'
    ]
]);
```

### Génération QR avec Socket.IO automatique

```php
// Générer un QR code avec abonnement automatique à la session
$result = $sunuid->generateQRWithWebSocket('https://votre-site.com/auth', [
    'type' => 2, // Authentification
    'theme' => 'light'
]);

if ($result['success']) {
    echo "QR code généré avec session ID: " . $result['data']['session_id'];
    // L'utilisateur peut scanner le QR code
    // Les notifications arriveront automatiquement via Socket.IO
}
```

## 📡 Événements supportés

### Événements système
- `connect` - Connexion établie
- `disconnect` - Déconnexion
- `error` - Erreur de connexion
- `message` - Message générique

### Événements métier
- `auth_success` - Authentification réussie
- `auth_failure` - Échec d'authentification
- `kyc_complete` - KYC complété
- `kyc_pending` - KYC en attente
- `session_expired` - Session expirée

## 🔗 Configuration Socket.IO

### Paramètres de connexion automatiques

Le SDK configure automatiquement les paramètres suivants lors de la connexion :

```php
$queryParams = [
    'token' => $config['client_id'],
    'type' => 'web',
    'userId' => $config['client_id'],
    'username' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
];
```

### Transports supportés

- **WebSocket** : Connexion bidirectionnelle en temps réel
- **Polling** : Fallback pour les environnements sans WebSocket

## 🛠️ Gestion d'erreurs

### Exceptions courantes

```php
try {
    $sunuid->connectWebSocket();
} catch (ServerConnectionFailureException $e) {
    echo "Impossible de se connecter au serveur Socket.IO";
} catch (Exception $e) {
    echo "Erreur générale: " . $e->getMessage();
}
```

### Reconnexion automatique

Le SDK gère automatiquement :
- Tentatives de reconnexion
- Gestion des timeouts
- Nettoyage des sessions expirées

## 📊 Monitoring

### Logs

```php
// Activer les logs Socket.IO
$config['enable_logs'] = true;
$config['log_level'] = \Monolog\Logger::INFO;
$config['log_file'] = 'sunuid-socketio.log';
```

### Statut de connexion

```php
if ($sunuid->isWebSocketConnected()) {
    echo "Socket.IO connecté";
} else {
    echo "Socket.IO déconnecté";
}
```

## 🚀 Exemples complets

### Exemple 1: Authentification avec notifications

```php
<?php
require_once 'vendor/autoload.php';

use SunuID\SunuID;

$config = [
    'client_id' => 'votre_client_id',
    'secret_id' => 'votre_secret_id',
    'partner_name' => 'Votre Entreprise',
    'enable_websocket' => true,
    'websocket_auto_connect' => true
];

$sunuid = new SunuID($config);

// Configurer les callbacks
$sunuid->onWebSocketEvent('auth_success', function ($data) {
    echo "🎉 Utilisateur authentifié: " . $data['user_id'];
    // Rediriger vers le dashboard
});

$sunuid->onWebSocketEvent('auth_failure', function ($data) {
    echo "😞 Échec d'authentification: " . $data['reason'];
    // Afficher un message d'erreur
});

// Initialiser et connecter
$sunuid->initWebSocket();
$sunuid->connectWebSocket();

// Générer un QR code avec abonnement automatique
$result = $sunuid->generateQRWithWebSocket('https://votre-site.com/auth');

if ($result['success']) {
    echo "📱 QR code généré: " . $result['data']['url'];
    echo "⏳ En attente de l'authentification...";
    
    // En production, vous utiliseriez une boucle d'événements
    sleep(30); // Attendre 30 secondes
}
```

### Exemple 2: KYC avec suivi en temps réel

```php
<?php
require_once 'vendor/autoload.php';

use SunuID\SunuID;

$config = [
    'client_id' => 'votre_client_id',
    'secret_id' => 'votre_secret_id',
    'partner_name' => 'Votre Entreprise',
    'enable_websocket' => true
];

$sunuid = new SunuID($config);

// Callbacks pour le KYC
$sunuid->onWebSocketEvent('kyc_pending', function ($data) {
    echo "⏳ KYC en cours...\n";
    echo "Étapes restantes: " . implode(', ', $data['pending_steps']);
});

$sunuid->onWebSocketEvent('kyc_complete', function ($data) {
    echo "✅ KYC complété!\n";
    
    $kycData = $data['kyc_data'];
    echo "Nom: " . $kycData['user_info']['name'] . "\n";
    echo "Email: " . $kycData['user_info']['email'] . "\n";
    echo "Statut: " . $kycData['verification_status'] . "\n";
    
    // Sauvegarder en base de données
    saveKycData($kycData);
});

// Initialiser et connecter
$sunuid->initWebSocket();
$sunuid->connectWebSocket();

// Générer un QR code pour KYC
$result = $sunuid->generateQRWithWebSocket('https://votre-site.com/kyc', [
    'type' => 1 // KYC
]);

if ($result['success']) {
    echo "📱 QR code KYC généré\n";
    echo "Session ID: " . $result['data']['session_id'] . "\n";
    
    // Attendre la completion du KYC
    while (true) {
        $sessions = $sunuid->getWebSocketActiveSessions();
        if (empty($sessions)) {
            break; // KYC terminé
        }
        sleep(1);
    }
}
```

## 🔧 Dépendances

### Composer

```json
{
    "require": {
        "elephantio/elephant.io": "^3.3"
    }
}
```

### Installation

```bash
composer require elephantio/elephant.io
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

### 🔄 Comparaison avec l'approche polling

| Aspect | Socket.IO | Polling |
|--------|-----------|---------|
| **Latence** | ⚡ Immédiate | ⏱️ 1-5 secondes |
| **Charge serveur** | 🟢 Faible | 🔴 Élevée |
| **Bande passante** | 🟢 Optimale | 🔴 Excessive |
| **Expérience utilisateur** | 🟢 Excellente | 🟡 Correcte |
| **Complexité** | 🟡 Moyenne | 🟢 Simple |

## 🚀 Prochaines étapes

### Améliorations futures

1. **Support Socket.IO v4** - Quand disponible dans ElephantIO
2. **Clustering** - Support multi-serveurs
3. **Compression** - Optimisation de la bande passante
4. **Authentification avancée** - JWT, OAuth
5. **Monitoring avancé** - Métriques de performance

### Intégrations

1. **Laravel** - Package dédié avec événements
2. **Symfony** - Bundle avec services
3. **WordPress** - Plugin avec hooks
4. **Magento** - Extension avec observers

---

## 📞 Support

Pour toute question sur l'intégration Socket.IO :

- 📧 Email : dev@sunuid.sn
- 📖 Documentation : https://docs.sunuid.sn
- 🐙 GitHub : https://github.com/sunuid/php-sdk

**🎉 L'intégration Socket.IO est maintenant prête pour la production !** 
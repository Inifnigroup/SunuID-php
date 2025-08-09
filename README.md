# SunuID PHP SDK

SDK PHP officiel pour l'intégration des QR codes d'authentification et KYC SunuID.

## 📋 Prérequis

- PHP >= 7.4
- Composer
- Extensions PHP : `curl`, `json`, `openssl`

## 🚀 Installation

### Via Composer (Recommandé)

```bash
composer require sunuid/php-sdk
```

### Installation manuelle

```bash
git clone https://github.com/sunuid/php-sdk.git
cd sunuid-php-sdk
composer install
```

## 📖 Utilisation Rapide

```php
<?php

require_once 'vendor/autoload.php';

use SunuID\SunuID;

// Configuration du SDK
$config = [
    'client_id' => 'votre_client_id',
    'secret_id' => 'votre_secret_id',
    'partner_name' => 'Votre Entreprise',
    'theme' => 'light',
    'language' => 'fr'
];

// Initialisation
$sunuid = new SunuID($config);

// Initialiser avec l'API
if ($sunuid->init()) {
    echo "SDK initialisé avec succès\n";
    
    // Générer un QR code d'authentification
    $result = $sunuid->generateQR('https://votre-site.com/auth');
    
    if ($result['success']) {
        echo "QR Code généré: " . $result['data']['qr_code'] . "\n";
        echo "Session ID: " . $result['data']['session_id'] . "\n";
    }
}
```

## ⚙️ Configuration

### Options de configuration

| Option | Type | Défaut | Description |
|--------|------|--------|-------------|
| `client_id` | string | null | Identifiant client SunuID |
| `secret_id` | string | null | Clé secrète SunuID |
| `partner_name` | string | null | Nom de votre entreprise |
| `api_url` | string | `https://api.sunuid.fayma.sn` | URL de l'API SunuID |
| `type` | int | `2` | Type de service (1=KYC, 2=Auth, 3=Signature) |
| `theme` | string | `light` | Thème (light/dark) |
| `language` | string | `fr` | Langue (fr/en) |
| `enable_logs` | bool | `true` | Activer les logs |
| `log_file` | string | `sunuid.log` | Fichier de log |
| `request_timeout` | int | `10` | Timeout des requêtes (secondes) |
| `max_retries` | int | `3` | Nombre max de tentatives |
| `enable_websocket` | bool | `false` | Activer les Socket.IO |
| `websocket_url` | string | `https://samasocket.fayma.sn:9443` | Endpoint Socket.IO (handshake http/https) |
| `websocket_auto_connect` | bool | `false` | Connexion automatique |
| `websocket_socketio_version` | string | `2` | Version Socket.IO (0, 1, 2 pris en charge par ElephantIO) |
| `websocket_transports` | array | `['websocket', 'polling']` | Transports supportés |
| `websocket_query_params` | array | `[]` | Paramètres de requête additionnels |

## 🔧 Fonctionnalités

### Génération de QR Codes

```php
// QR code avec API (authentification)
$result = $sunuid->generateQR('https://votre-site.com/auth');

// QR code local (sans API)
$result = $sunuid->generateQRLocal('https://votre-site.com', [
    'size' => 300,
    'margin' => 10
]);

// QR code avec WebSocket automatique
$result = $sunuid->generateQRWithWebSocket('https://votre-site.com/auth');
```

### Socket.IO - Notifications Temps Réel

```php
// Configuration avec Socket.IO
$config = [
    'client_id' => 'votre_client_id',
    'secret_id' => 'votre_secret_id',
    'partner_name' => 'Votre Entreprise',
    'enable_websocket' => true,
    'websocket_auto_connect' => true,
    'websocket_url' => 'https://samasocket.fayma.sn:9443',
    'websocket_socketio_version' => '2',
    'websocket_transports' => ['websocket', 'polling']
];

$sunuid = new SunuID($config);

// Initialiser et connecter le Socket.IO
$sunuid->initWebSocket();
if (!$sunuid->connectWebSocket()) {
    // Diagnostic rapide en cas d'échec
    echo "Erreur Socket.IO: " . ($sunuid->getWebSocketLastError() ?? 'inconnue') . "\n";
}

// Configurer les callbacks pour les événements
$sunuid->onWebSocketEvent('auth_success', function ($data) {
    echo "✅ Authentification réussie pour la session: " . $data['session_id'];
});

$sunuid->onWebSocketEvent('kyc_complete', function ($data) {
    echo "✅ KYC complété pour la session: " . $data['session_id'];
});

// S'abonner à une session spécifique
$sunuid->subscribeToSession('session_id_123');

// Envoyer un message personnalisé
$sunuid->sendWebSocketMessage([
    'event' => 'custom_event',
    'data' => ['message' => 'Hello!']
]);
```

### Vérification de Statut

```php
$status = $sunuid->checkQRStatus('session_id_123');

if ($status['success']) {
    echo "Statut: " . $status['data']['status'] . "\n";
    if (isset($status['data']['user_data'])) {
        echo "Utilisateur: " . json_encode($status['data']['user_data']) . "\n";
    }
}
```

### Types de Services

```php
// KYC (Know Your Customer)
$sunuid = new SunuID(['type' => 1, ...]);

// Authentification
$sunuid = new SunuID(['type' => 2, ...]);

// Signature électronique
$sunuid = new SunuID(['type' => 3, ...]);
```

## 🧪 Tests

```bash
# Lancer tous les tests
composer test

# Tests avec couverture
composer test-coverage

# Tests de qualité du code
composer stan
composer cs
```

### Tests d'intégration

- Interface web: lancer un serveur local puis ouvrir l'UI de test

```bash
php -S localhost:8000 -t test
# Naviguer vers http://localhost:8000/index.html
```

- API/CLI: test complet côté ligne de commande

```bash
php test_socketio_integration.php
```

## 🛠️ Dépannage Socket.IO

- Handshake ElephantIO: utiliser un endpoint `http/https` (pas `wss`) pour `websocket_url`.
- Version supportée: configurer `websocket_socketio_version` sur `"2"` (ElephantIO 3.x)
- Message Deprecated ElephantIO sous PHP 8.2+: inoffensif. Pour le masquer dans vos scripts de test:

```php
error_reporting(E_ALL ^ E_DEPRECATED);
```

## 📝 Exemples

Consultez le dossier `examples/` pour des exemples d'utilisation :

- `basic_usage.php` - Utilisation de base
- Tests unitaires dans `tests/`

## 🔒 Sécurité

- Les identifiants sont transmis de manière sécurisée via HTTPS
- Support des tokens JWT pour l'authentification
- Validation automatique des paramètres
- Gestion des erreurs et exceptions

## 📞 Support

- **Documentation** : https://docs.sunuid.sn
- **Issues** : https://github.com/sunuid/php-sdk/issues
- **Email** : dev@sunuid.sn

## 📄 Licence

MIT License - voir le fichier `LICENSE` pour plus de détails.

## 🤝 Contribution

Les contributions sont les bienvenues ! Consultez `CONTRIBUTING.md` pour les détails.

---

**SunuID Team** - Simplifiez l'authentification et le KYC avec les QR codes intelligents. # SunuID-php

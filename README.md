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

# Guide d'Installation - SunuID PHP SDK

## 🚨 Important : Package en développement

Le SDK n'est pas encore publié sur Packagist. Utilisez une des méthodes ci-dessous pour l'installer.

## 📦 Méthodes d'Installation

### **Méthode 1: Installation depuis GitHub (Recommandée)**

Ajoutez ceci à votre `composer.json` :

```json
{
    "repositories": [
        {
            "type": "vcs",
            "url": "https://github.com/sunuid/php-sdk"
        }
    ],
    "require": {
        "sunuid/php-sdk": "^1.0"
    }
}
```

Puis exécutez :
```bash
composer update
```

### **Méthode 2: Installation directe depuis GitHub**

```bash
composer require sunuid/php-sdk:dev-main
```

### **Méthode 3: Installation manuelle**

```bash
# Cloner le repository
git clone https://github.com/sunuid/php-sdk.git
cd sunuid-php-sdk

# Installer les dépendances
composer install

# Copier dans votre projet
cp -r src/SunuID/ /path/to/your/project/src/
```

### **Méthode 4: Via Composer avec repository privé**

Si vous avez un repository privé :

```json
{
    "repositories": [
        {
            "type": "vcs",
            "url": "git@github.com:sunuid/php-sdk.git"
        }
    ],
    "require": {
        "sunuid/php-sdk": "^1.0"
    }
}
```

## 🔧 Configuration

Une fois installé, configurez le SDK :

```php
<?php

require_once 'vendor/autoload.php';

use SunuID\SunuID;

$config = [
    'client_id' => 'votre_client_id',
    'secret_id' => 'votre_secret_id',
    'partner_name' => 'Votre Entreprise'
];

$sunuid = new SunuID($config);
```

## 🧪 Test de l'Installation

Créez un fichier `test_installation.php` :

```php
<?php

require_once 'vendor/autoload.php';

use SunuID\SunuID;

try {
    $sunuid = new SunuID([
        'client_id' => 'test',
        'secret_id' => 'test',
        'partner_name' => 'Test'
    ]);
    
    echo "✅ SDK installé avec succès!\n";
    echo "Version: " . $sunuid->getConfig()['version'] . "\n";
    
} catch (Exception $e) {
    echo "❌ Erreur: " . $e->getMessage() . "\n";
}
```

## 📋 Dépendances Requises

Le SDK nécessite ces extensions PHP :
- `curl`
- `json` 
- `openssl`
- `gd` (pour la génération de QR codes)

Vérifiez avec :
```bash
php -m | grep -E "(curl|json|openssl|gd)"
```

## 🚀 Prochaines Étapes

1. **Obtenir vos identifiants** sur https://sunuid.sn
2. **Consulter la documentation** dans le README.md
3. **Tester avec l'exemple** dans `examples/basic_usage.php`

## 📞 Support

- **Email** : dev@sunuid.sn
- **Issues** : https://github.com/sunuid/php-sdk/issues
- **Documentation** : https://docs.sunuid.sn

---

**Note** : Une fois le SDK publié sur Packagist, vous pourrez simplement utiliser `composer require sunuid/php-sdk`. 
# Guide de Publication sur Packagist

## 🎯 État Actuel

✅ **Repository GitHub** : https://github.com/Inifnigroup/SunuID-php  
✅ **Tag de version** : v1.0.0 créé et poussé  
✅ **Composer.json** : Validé et optimisé pour Packagist  
✅ **Documentation** : Complète et prête  

## 🚀 Étapes pour Publier sur Packagist

### **Étape 1: Créer un Compte Packagist**

1. **Aller sur [Packagist.org](https://packagist.org)**
2. **Cliquer sur "Sign up" ou "Log in"**
3. **Créer un compte ou se connecter avec GitHub**

### **Étape 2: Soumettre le Package**

1. **Cliquer sur "Submit Package"**
2. **Entrer l'URL du repository** : `https://github.com/Inifnigroup/SunuID-php`
3. **Vérifier les informations** :
   - **Nom** : `sunuid/php-sdk`
   - **Description** : SDK PHP pour l'intégration des QR codes d'authentification et KYC SunuID
   - **Type** : Library
   - **Licence** : MIT
   - **Version** : 1.0.0

### **Étape 3: Configuration du Package**

Une fois le package créé, configurer :

1. **Aller dans les paramètres du package**
2. **Configurer l'intégration GitHub** :
   - Activer la mise à jour automatique
   - Configurer les webhooks si nécessaire

3. **Vérifier les métadonnées** :
   - Auteurs
   - Description
   - Mots-clés
   - Support

### **Étape 4: Test de l'Installation**

Après publication, tester l'installation :

```bash
# Créer un nouveau projet de test
mkdir test-sunuid-install
cd test-sunuid-install

# Créer composer.json
cat > composer.json << 'EOF'
{
    "require": {
        "sunuid/php-sdk": "^1.0"
    }
}
EOF

# Installer le package
composer install

# Tester l'installation
php -r "
require 'vendor/autoload.php';
use SunuID\SunuID;
\$sunuid = new SunuID(['client_id' => 'test', 'secret_id' => 'test']);
echo '✅ SDK installé avec succès!';
"
```

## 📋 Checklist de Publication

### ✅ **Pré-publication**
- [x] Repository GitHub créé et configuré
- [x] Code poussé sur GitHub
- [x] Tag de version créé (v1.0.0)
- [x] Composer.json validé
- [x] Documentation complète
- [x] Tests fonctionnels

### 🔄 **Publication**
- [ ] Compte Packagist créé
- [ ] Package soumis sur Packagist
- [ ] Métadonnées vérifiées
- [ ] Intégration GitHub configurée
- [ ] Test d'installation réussi

### ✅ **Post-publication**
- [ ] Documentation mise à jour
- [ ] README.md mis à jour avec lien Packagist
- [ ] Exemples testés
- [ ] Support configuré

## 🔗 Liens Utiles

- **Repository GitHub** : https://github.com/Inifnigroup/SunuID-php
- **Packagist** : https://packagist.org
- **Documentation** : https://docs.sunuid.sn
- **Support** : dev@sunuid.sn

## 📝 Instructions d'Installation (Post-publication)

Une fois publié sur Packagist, les utilisateurs pourront installer le SDK avec :

```bash
composer require sunuid/php-sdk
```

### **Utilisation**

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

// Générer un QR code
$result = $sunuid->generateQRLocal('https://example.com/auth');
```

## 🎉 Résultat Attendu

Après publication réussie :
- ✅ Package disponible sur Packagist
- ✅ Installation simple : `composer require sunuid/php-sdk`
- ✅ Documentation accessible
- ✅ Support utilisateur disponible
- ✅ Mises à jour automatiques via GitHub

---

**Note** : La publication sur Packagist peut prendre quelques minutes pour être indexée. Une fois publié, le package sera disponible pour tous les développeurs PHP ! 
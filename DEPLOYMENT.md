# Guide de Déploiement - SunuID PHP SDK

## 🚀 Déploiement sur Packagist (Recommandé)

### **Étape 1: Préparation du Repository GitHub**

```bash
# Initialiser Git (si pas déjà fait)
git init

# Ajouter tous les fichiers
git add .

# Premier commit
git commit -m "Initial commit: SunuID PHP SDK v1.0.0"

# Créer le repository sur GitHub et ajouter l'origin
git remote add origin https://github.com/sunuid/php-sdk.git

# Pousser le code
git push -u origin main
```

### **Étape 2: Créer un tag de version**

```bash
# Créer un tag annoté
git tag -a v1.0.0 -m "Version 1.0.0 - SDK initial"

# Pousser le tag
git push origin v1.0.0
```

### **Étape 3: Publication sur Packagist**

1. **Aller sur [Packagist.org](https://packagist.org)**
2. **Créer un compte ou se connecter**
3. **Cliquer sur "Submit Package"**
4. **Entrer l'URL du repository GitHub** : `https://github.com/sunuid/php-sdk`
5. **Vérifier les informations et soumettre**

### **Étape 4: Configuration automatique (Optionnel)**

1. **Dans Packagist, aller dans les paramètres du package**
2. **Configurer l'intégration GitHub**
3. **Activer la mise à jour automatique**

## 📦 Déploiement Manuel

### **Option 1: Package ZIP**

```bash
# Utiliser le script de build
./build.sh

# Ou créer manuellement
zip -r sunuid-php-sdk-1.0.0.zip . \
    -x "vendor/*" \
    -x "tests/*" \
    -x "examples/*" \
    -x "*.log" \
    -x ".git/*" \
    -x ".gitignore" \
    -x "build.sh" \
    -x "phpunit.xml" \
    -x "composer.lock"
```

### **Option 2: Repository privé**

```bash
# Créer un repository privé sur GitHub
# Ajouter le repository dans composer.json du projet client

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

## 🔧 Configuration Post-Déploiement

### **Vérification de l'Installation**

```bash
# Dans le projet client
composer require sunuid/php-sdk

# Tester l'installation
php test_installation.php
```

### **Configuration du SDK**

```php
<?php

require_once 'vendor/autoload.php';

use SunuID\SunuID;

$config = [
    'client_id' => 'votre_client_id',
    'secret_id' => 'votre_secret_id',
    'partner_name' => 'Votre Entreprise',
    'theme' => 'light',
    'language' => 'fr'
];

$sunuid = new SunuID($config);
```

## 📋 Checklist de Déploiement

### ✅ **Pré-déploiement**
- [ ] Tests unitaires passants (`composer test`)
- [ ] Validation du code (`composer stan`, `composer cs`)
- [ ] Documentation complète (README.md, INSTALLATION.md)
- [ ] Licence MIT ajoutée
- [ ] Changelog à jour
- [ ] Version correcte dans composer.json

### ✅ **Déploiement**
- [ ] Repository GitHub créé et configuré
- [ ] Code poussé sur GitHub
- [ ] Tag de version créé
- [ ] Package soumis sur Packagist
- [ ] Tests d'installation réussis

### ✅ **Post-déploiement**
- [ ] Documentation mise à jour
- [ ] Exemples testés
- [ ] Support configuré
- [ ] Monitoring mis en place

## 🚨 Résolution des Problèmes

### **Erreur "Could not find a matching version"**

**Cause** : Le package n'est pas encore publié sur Packagist.

**Solutions** :
1. **Attendre** que Packagist indexe le package (peut prendre quelques minutes)
2. **Utiliser GitHub directement** :
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
3. **Installation directe** :
   ```bash
   composer require sunuid/php-sdk:dev-main
   ```

### **Erreur d'Autoloading**

**Cause** : Problème de configuration PSR-4.

**Solution** :
```bash
composer dump-autoload
```

### **Erreur de Dépendances**

**Cause** : Extensions PHP manquantes.

**Solution** :
```bash
# Vérifier les extensions
php -m | grep -E "(curl|json|openssl|gd)"

# Installer les extensions manquantes
# Ubuntu/Debian
sudo apt-get install php-curl php-json php-openssl php-gd

# macOS avec Homebrew
brew install php@8.1
```

## 📞 Support

- **Email** : dev@sunuid.sn
- **Issues GitHub** : https://github.com/sunuid/php-sdk/issues
- **Documentation** : https://docs.sunuid.sn

## 🎯 Prochaines Étapes

1. **Mettre en place CI/CD** avec GitHub Actions
2. **Créer des releases automatiques**
3. **Ajouter des tests d'intégration**
4. **Optimiser les performances**
5. **Ajouter de nouvelles fonctionnalités**

---

**Note** : Une fois le SDK publié sur Packagist, les utilisateurs pourront simplement utiliser `composer require sunuid/php-sdk` sans configuration supplémentaire. 
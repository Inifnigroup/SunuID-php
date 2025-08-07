#!/bin/bash

# Script post-publication pour le SDK SunuID PHP
echo "🎉 Post-Publication SDK SunuID PHP"
echo "=================================="

# Configuration
PACKAGIST_URL="https://packagist.org/packages/sunuid/php-sdk"
GITHUB_URL="https://github.com/Inifnigroup/SunuID-php"
DOCS_URL="https://docs.sunuid.sn"

# Couleurs
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

print_status() {
    echo -e "${GREEN}✅ $1${NC}"
}

print_info() {
    echo -e "${BLUE}ℹ️  $1${NC}"
}

print_warning() {
    echo -e "${YELLOW}⚠️  $1${NC}"
}

# Étape 1: Test de l'installation depuis Packagist
echo "🧪 Étape 1: Test de l'installation depuis Packagist"
echo "------------------------------------------------"

if ./test_packagist_install.sh; then
    print_status "Installation depuis Packagist réussie"
else
    print_warning "Installation échouée - vérifier la publication sur Packagist"
    echo "   URL à vérifier: $PACKAGIST_URL"
fi

echo ""

# Étape 2: Mise à jour de la documentation
echo "📝 Étape 2: Mise à jour de la documentation"
echo "------------------------------------------"

# Mettre à jour le README avec le lien Packagist
if [ -f "README.md" ]; then
    # Ajouter le badge Packagist si pas déjà présent
    if ! grep -q "packagist.org" README.md; then
        echo "   Ajout du badge Packagist au README..."
        # Ajouter le badge après le titre
        sed -i '' '3i\
[![Packagist](https://img.shields.io/packagist/v/sunuid/php-sdk.svg)](https://packagist.org/packages/sunuid/php-sdk)\
[![Downloads](https://img.shields.io/packagist/dt/sunuid/php-sdk.svg)](https://packagist.org/packages/sunuid/php-sdk)\
' README.md
        print_status "Badge Packagist ajouté au README"
    else
        print_info "Badge Packagist déjà présent"
    fi
fi

echo ""

# Étape 3: Génération du contenu marketing
echo "📢 Étape 3: Génération du contenu marketing"
echo "------------------------------------------"

# Créer un fichier d'annonce
cat > ANNOUNCEMENT.md << 'EOF'
# 🎉 Lancement du SDK SunuID PHP !

## 📦 Disponible sur Packagist

Le SDK SunuID PHP est maintenant disponible sur Packagist pour une installation facile :

```bash
composer require sunuid/php-sdk
```

## 🚀 Fonctionnalités

- ✅ **Authentification** via QR codes
- ✅ **KYC** (Know Your Customer) intégré
- ✅ **Génération de QR codes** locale et API
- ✅ **Gestion des sessions** sécurisée
- ✅ **Configuration flexible** et validation
- ✅ **Système de logging** intégré
- ✅ **Support multi-types** (Auth, KYC, Signature)

## 📖 Documentation

- **📚 Guide complet** : https://docs.sunuid.sn
- **📋 Exemples d'utilisation** : https://github.com/Inifnigroup/SunuID-php
- **📧 Support** : dev@sunuid.sn

## 🎯 Utilisation Rapide

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

// Générer un QR code d'authentification
$result = $sunuid->generateQRLocal('https://votre-site.com/auth');
```

## 🔗 Liens Utiles

- **📦 Packagist** : https://packagist.org/packages/sunuid/php-sdk
- **🐙 GitHub** : https://github.com/Inifnigroup/SunuID-php
- **📖 Documentation** : https://docs.sunuid.sn
- **📧 Support** : dev@sunuid.sn

---

**Simplifiez l'authentification et le KYC avec les QR codes intelligents SunuID ! 🚀**
EOF

print_status "Fichier d'annonce créé: ANNOUNCEMENT.md"

echo ""

# Étape 4: Génération des métriques
echo "📊 Étape 4: Génération des métriques"
echo "-----------------------------------"

# Créer un fichier de métriques
cat > METRICS.md << 'EOF'
# 📊 Métriques du SDK SunuID PHP

## 🎯 Objectifs du Premier Mois

### **Métriques Techniques**
- [ ] **Downloads Packagist** : 100+
- [ ] **Stars GitHub** : 50+
- [ ] **Issues résolues** : 90% dans les 24h
- [ ] **Tests passants** : 95%+

### **Métriques Business**
- [ ] **Utilisateurs actifs** : 10+
- [ ] **Intégrations réussies** : 5+
- [ ] **Feedback positif** : 4.5/5 sur Packagist
- [ ] **Retention** : 80% après 30 jours

## 📈 Suivi Hebdomadaire

### **Semaine 1**
- [ ] Publication sur Packagist
- [ ] Premiers downloads
- [ ] Feedback initial

### **Semaine 2**
- [ ] Corrections de bugs
- [ ] Amélioration documentation
- [ ] Premiers utilisateurs actifs

### **Semaine 3**
- [ ] Nouvelles fonctionnalités
- [ ] Expansion communauté
- [ ] Optimisations performances

### **Semaine 4**
- [ ] Bilan du premier mois
- [ ] Planification version 1.1
- [ ] Stratégie d'expansion

## 🔗 Liens de Suivi

- **Packagist Stats** : https://packagist.org/packages/sunuid/php-sdk/stats
- **GitHub Insights** : https://github.com/Inifnigroup/SunuID-php/pulse
- **Analytics** : À configurer
EOF

print_status "Fichier de métriques créé: METRICS.md"

echo ""

# Étape 5: Checklist post-publication
echo "✅ Étape 5: Checklist post-publication"
echo "-------------------------------------"

cat > POST_PUBLICATION_CHECKLIST.md << 'EOF'
# ✅ Checklist Post-Publication

## 🚀 Publication Packagist
- [ ] Package publié sur Packagist
- [ ] Installation testée avec succès
- [ ] Métadonnées vérifiées
- [ ] Intégration GitHub activée

## 📢 Communication
- [ ] Annonce sur LinkedIn
- [ ] Post sur Twitter
- [ ] Article de blog rédigé
- [ ] Email aux clients existants
- [ ] Partage dans les communautés PHP

## 📝 Documentation
- [ ] README mis à jour avec lien Packagist
- [ ] Badges ajoutés
- [ ] Exemples vérifiés
- [ ] Guide d'installation mis à jour

## 🧪 Tests et Qualité
- [ ] Tests d'installation réussis
- [ ] Exemples fonctionnels
- [ ] Documentation à jour
- [ ] Support configuré

## 📊 Monitoring
- [ ] Métriques configurées
- [ ] Analytics mis en place
- [ ] Feedback collecté
- [ ] Issues traitées

## 🎯 Prochaines Étapes
- [ ] Collecter les retours utilisateurs
- [ ] Planifier les améliorations
- [ ] Préparer la version 1.1
- [ ] Développer l'écosystème
EOF

print_status "Checklist post-publication créée"

echo ""

# Résumé final
echo "🎉 RÉSUMÉ POST-PUBLICATION"
echo "=========================="
echo ""
echo "📦 Package publié sur Packagist"
echo "🧪 Tests d'installation réussis"
echo "📝 Documentation mise à jour"
echo "📢 Contenu marketing généré"
echo "📊 Métriques configurées"
echo ""
echo "🚀 Actions recommandées :"
echo "1. Tester l'installation : ./test_packagist_install.sh"
echo "2. Annoncer le lancement : ANNOUNCEMENT.md"
echo "3. Suivre les métriques : METRICS.md"
echo "4. Suivre la checklist : POST_PUBLICATION_CHECKLIST.md"
echo ""
echo "📞 Support : dev@sunuid.sn"
echo "📖 Documentation : $DOCS_URL"
echo "🐙 GitHub : $GITHUB_URL"
echo "📦 Packagist : $PACKAGIST_URL"
echo ""
print_status "Post-publication terminé avec succès !" 
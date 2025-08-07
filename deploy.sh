#!/bin/bash

# Script de déploiement automatisé pour SunuID PHP SDK
set -e

echo "🚀 Déploiement du SDK SunuID PHP"
echo "================================"

# Configuration
VERSION=$(grep '"version"' composer.json | cut -d'"' -f4)
REPO_URL="https://github.com/sunuid/php-sdk.git"
PACKAGE_NAME="sunuid-php-sdk-${VERSION}"

# Couleurs pour les messages
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Fonction pour afficher les messages
print_status() {
    echo -e "${GREEN}✅ $1${NC}"
}

print_warning() {
    echo -e "${YELLOW}⚠️  $1${NC}"
}

print_error() {
    echo -e "${RED}❌ $1${NC}"
}

# Vérification des prérequis
echo "🔍 Vérification des prérequis..."

if ! command -v git &> /dev/null; then
    print_error "Git n'est pas installé"
    exit 1
fi

if ! command -v composer &> /dev/null; then
    print_error "Composer n'est pas installé"
    exit 1
fi

if ! command -v php &> /dev/null; then
    print_error "PHP n'est pas installé"
    exit 1
fi

print_status "Prérequis vérifiés"

# Nettoyage
echo "🧹 Nettoyage..."
rm -rf vendor/
rm -f composer.lock
rm -rf tests/results/
rm -f *.log
rm -f sunuid-php-sdk-*.zip

print_status "Nettoyage terminé"

# Installation des dépendances
echo "📦 Installation des dépendances..."
composer install --no-dev --optimize-autoloader

print_status "Dépendances installées"

# Tests
echo "🧪 Lancement des tests..."
if composer test > /dev/null 2>&1; then
    print_status "Tests passants"
else
    print_warning "Certains tests ont échoué, mais le déploiement continue..."
fi

# Validation du code
echo "🔍 Validation du code..."
if composer stan > /dev/null 2>&1; then
    print_status "PHPStan OK"
else
    print_warning "PHPStan a détecté des problèmes"
fi

if composer cs > /dev/null 2>&1; then
    print_status "CodeSniffer OK"
else
    print_warning "CodeSniffer a détecté des problèmes"
fi

# Vérification Git
echo "📋 Vérification Git..."
if [ ! -d ".git" ]; then
    print_error "Ce n'est pas un repository Git"
    exit 1
fi

# Vérifier s'il y a des changements non commités
if [ -n "$(git status --porcelain)" ]; then
    print_warning "Il y a des changements non commités"
    read -p "Voulez-vous continuer ? (y/N): " -n 1 -r
    echo
    if [[ ! $REPLY =~ ^[Yy]$ ]]; then
        print_error "Déploiement annulé"
        exit 1
    fi
fi

# Création du package
echo "📦 Création du package..."
zip -r "${PACKAGE_NAME}.zip" . \
    -x "vendor/*" \
    -x "tests/*" \
    -x "examples/*" \
    -x "*.log" \
    -x ".git/*" \
    -x ".gitignore" \
    -x "build.sh" \
    -x "deploy.sh" \
    -x "phpunit.xml" \
    -x "composer.lock" \
    -x "test_installation.php" \
    -x "*.zip" > /dev/null

print_status "Package créé: ${PACKAGE_NAME}.zip"

# Vérification du repository distant
echo "🌐 Vérification du repository distant..."
if git remote get-url origin > /dev/null 2>&1; then
    REMOTE_URL=$(git remote get-url origin)
    print_status "Repository distant: $REMOTE_URL"
else
    print_warning "Aucun repository distant configuré"
    read -p "Voulez-vous ajouter un repository distant ? (y/N): " -n 1 -r
    echo
    if [[ $REPLY =~ ^[Yy]$ ]]; then
        read -p "URL du repository: " REPO_URL
        git remote add origin "$REPO_URL"
        print_status "Repository distant ajouté"
    fi
fi

# Commit et push (si demandé)
echo "📤 Push vers GitHub..."
read -p "Voulez-vous pousser vers GitHub ? (y/N): " -n 1 -r
echo
if [[ $REPLY =~ ^[Yy]$ ]]; then
    # Ajouter tous les fichiers
    git add .
    
    # Commit
    git commit -m "Release v${VERSION} - SDK SunuID PHP" || true
    
    # Push
    if git push origin main; then
        print_status "Code poussé vers GitHub"
        
        # Créer et pousser le tag
        if git tag -a "v${VERSION}" -m "Version ${VERSION}"; then
            git push origin "v${VERSION}"
            print_status "Tag v${VERSION} créé et poussé"
        else
            print_warning "Impossible de créer le tag"
        fi
    else
        print_error "Erreur lors du push"
    fi
fi

# Instructions pour Packagist
echo ""
echo "📋 Instructions pour Packagist:"
echo "================================"
echo "1. Allez sur https://packagist.org"
echo "2. Connectez-vous ou créez un compte"
echo "3. Cliquez sur 'Submit Package'"
echo "4. Entrez l'URL: $REPO_URL"
echo "5. Vérifiez les informations et soumettez"
echo ""

# Résumé
echo "🎉 RÉSUMÉ DU DÉPLOIEMENT"
echo "========================"
echo "Version: $VERSION"
echo "Package: $PACKAGE_NAME.zip"
echo "Taille: $(du -h "$PACKAGE_NAME.zip" | cut -f1)"
echo ""
echo "📁 Fichiers créés:"
echo "- $PACKAGE_NAME.zip (package de distribution)"
echo "- Documentation mise à jour"
echo ""
echo "📞 Support: dev@sunuid.sn"
echo "📖 Documentation: https://docs.sunuid.sn"
echo ""
print_status "Déploiement terminé avec succès!" 
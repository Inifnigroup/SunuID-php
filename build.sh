#!/bin/bash

# Script de build pour SunuID PHP SDK
echo "🚀 Build du SDK SunuID PHP..."

# Nettoyage
echo "🧹 Nettoyage..."
rm -rf vendor/
rm -f composer.lock
rm -rf tests/results/
rm -f *.log

# Installation des dépendances
echo "📦 Installation des dépendances..."
composer install --no-dev --optimize-autoloader

# Tests
echo "🧪 Lancement des tests..."
composer test

# Vérification de la qualité du code
echo "🔍 Vérification de la qualité..."
composer stan
composer cs

# Création du package
echo "📦 Création du package..."
VERSION=$(grep '"version"' composer.json | cut -d'"' -f4)
PACKAGE_NAME="sunuid-php-sdk-${VERSION}.zip"

# Exclusion des fichiers de développement
zip -r "$PACKAGE_NAME" . \
    -x "vendor/*" \
    -x "tests/*" \
    -x "examples/*" \
    -x "*.log" \
    -x ".git/*" \
    -x ".gitignore" \
    -x "build.sh" \
    -x "phpunit.xml" \
    -x "composer.lock"

echo "✅ Package créé: $PACKAGE_NAME"
echo "📋 Taille: $(du -h "$PACKAGE_NAME" | cut -f1)"
echo "🎉 Build terminé avec succès!" 
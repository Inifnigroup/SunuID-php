# Changelog

Tous les changements notables de ce projet seront documentés dans ce fichier.

Le format est basé sur [Keep a Changelog](https://keepachangelog.com/fr/1.0.0/),
et ce projet adhère au [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.0.0] - 2024-12-31

### Ajouté
- SDK PHP complet pour l'intégration SunuID
- Génération de QR codes d'authentification et KYC
- Support des QR codes locaux (sans API)
- Vérification de statut des sessions
- Configuration flexible avec validation
- Système de logging intégré
- Gestion des erreurs et retry automatique
- Support de multiples types de services (Auth, KYC, Signature)
- Tests unitaires complets
- Documentation complète

### Fonctionnalités
- **Authentification** : QR codes pour l'authentification utilisateur
- **KYC** : Vérification d'identité via QR codes
- **Signature** : Signature électronique via QR codes
- **Local** : Génération de QR codes sans appel API
- **Monitoring** : Vérification du statut des sessions
- **Logging** : Logs détaillés pour le debugging

### Technique
- PHP 7.4+ supporté
- Dépendances : GuzzleHTTP, Endroid QR Code, Firebase JWT, Monolog
- Tests : PHPUnit avec couverture de code
- Qualité : PHPStan et PHP CodeSniffer
- Licence : MIT

---

## [Unreleased]

### Planifié
- Support des webhooks
- Intégration avec plus de frameworks PHP
- Amélioration des performances
- Support de nouvelles fonctionnalités SunuID 
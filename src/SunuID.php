<?php

namespace SunuID;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\RotatingFileHandler;

/**
 * SDK PHP pour l'intégration des QR codes d'authentification et KYC SunuID
 * 
 * @version 1.0.0
 * @author SunuID Team
 * @license MIT
 */
class SunuID
{
    /**
     * Configuration par défaut
     */
    private const DEFAULT_CONFIG = [
        'api_url' => 'https://api.sunuid.fayma.sn',
        'client_id' => null,
        'secret_id' => null,
        'type' => 2, // Type par défaut (2 = authentification)
        'partner_name' => null,
        'theme' => 'light',
        'language' => 'fr',
        'auto_refresh' => false,
        'refresh_interval' => 30000,
        'request_timeout' => 10,
        'max_retries' => 3,
        'enable_logs' => true,
        'log_level' => Logger::INFO,
        'log_file' => 'sunuid.log',
        'secure_init' => false,
        'secure_init_url' => 'https://api.sunuid.fayma.sn/secure-init.php',
        'force_remote_server' => true,
        'use_local_fallback' => false
    ];

    /**
     * Configuration actuelle
     */
    private array $config;

    /**
     * Client HTTP Guzzle
     */
    private Client $httpClient;

    /**
     * Logger Monolog
     */
    private Logger $logger;

    /**
     * QR Code Writer
     */
    private ?PngWriter $qrWriter = null;

    /**
     * Statut d'initialisation
     */
    private bool $isInitialized = false;

    /**
     * Informations du partenaire
     */
    private array $partnerInfo = [];

    /**
     * Constructeur
     */
    public function __construct(array $config = [])
    {
        $this->config = array_merge(self::DEFAULT_CONFIG, $config);
        $this->initializeComponents();
    }

    /**
     * Initialiser les composants
     */
    private function initializeComponents(): void
    {
        // Initialiser le client HTTP
        $this->httpClient = new Client([
            'timeout' => $this->config['request_timeout'],
            'headers' => [
                'User-Agent' => 'SunuID-PHP-SDK/1.0.0',
                'Accept' => 'application/json',
                'Content-Type' => 'application/json'
            ]
        ]);

        // Initialiser le logger
        $this->logger = new Logger('sunuid-sdk');
        if ($this->config['enable_logs']) {
            $this->logger->pushHandler(new RotatingFileHandler(
                $this->config['log_file'],
                30,
                $this->config['log_level']
            ));
        }

        // QR Writer sera initialisé de manière lazy

        $this->logInfo('SDK PHP SunuID initialisé', [
            'api_url' => $this->config['api_url'],
            'type' => $this->config['type']
        ]);
    }

    /**
     * Initialiser le SDK
     */
    public function init(): bool
    {
        try {
            $this->logInfo('Début initialisation SDK');

            // Validation des paramètres
            $this->validateConfig();

            // Initialisation sécurisée si activée
            if ($this->config['secure_init']) {
                $this->secureInit();
            }

            // Récupérer les informations du partenaire
            $this->fetchPartnerInfo();

            $this->isInitialized = true;
            $this->logInfo('SDK initialisé avec succès');

            return true;

        } catch (\Exception $e) {
            $this->logError('Erreur lors de l\'initialisation', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Valider la configuration
     */
    public function validateConfig(): void
    {
        $errors = [];

        if (empty($this->config['client_id'])) {
            $errors[] = 'client_id manquant';
        }

        if (empty($this->config['secret_id'])) {
            $errors[] = 'secret_id manquant';
        }

        if (!in_array($this->config['type'], [1, 2, 3])) {
            $errors[] = 'type invalide (doit être 1, 2 ou 3)';
        }

        if (!filter_var($this->config['api_url'], FILTER_VALIDATE_URL)) {
            $errors[] = 'api_url invalide';
        }

        if (!empty($errors)) {
            throw new \InvalidArgumentException('Configuration invalide: ' . implode(', ', $errors));
        }
    }

    /**
     * Initialisation sécurisée
     */
    private function secureInit(): void
    {
        $this->logInfo('Début initialisation sécurisée');

        $response = $this->httpClient->post($this->config['secure_init_url'], [
            'json' => [
                'type' => $this->config['type'],
                'partner_name' => $this->config['partner_name'],
                'theme' => $this->config['theme']
            ]
        ]);

        $data = json_decode($response->getBody()->getContents(), true);

        if (!$data['success']) {
            throw new \RuntimeException('Échec de l\'initialisation sécurisée: ' . ($data['error'] ?? 'Erreur inconnue'));
        }

        // Décoder le token
        $tokenData = $this->decodeSecureToken($data['data']['token']);
        if ($tokenData) {
            $this->config['client_id'] = $tokenData['client_id'];
            $this->config['secret_id'] = $tokenData['secret_id'];
        }

        $this->logInfo('Initialisation sécurisée réussie');
    }

    /**
     * Décoder le token sécurisé
     */
    private function decodeSecureToken(string $token): ?array
    {
        try {
            $parts = explode('.', $token);
            if (count($parts) !== 2) {
                return null;
            }

            $payload = base64_decode($parts[0]);
            $tokenData = json_decode($payload, true);

            // Vérifier l'expiration
            if (isset($tokenData['exp']) && $tokenData['exp'] < time()) {
                return null;
            }

            return $tokenData;

        } catch (\Exception $e) {
            $this->logError('Erreur décodage token', ['error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Récupérer les informations du partenaire
     */
    private function fetchPartnerInfo(): void
    {
        try {
            $response = $this->makeRequest('/debug', [
                'type' => $this->config['type'],
                'client_id' => $this->config['client_id'],
                'secret_id' => $this->config['secret_id']
            ]);

            if ($response['success'] && isset($response['authentication']['auth_test'])) {
                $authTest = $response['authentication']['auth_test'];
                $this->partnerInfo = [
                    'partner_id' => $authTest['partner_id'],
                    'partner_name' => $this->getPartnerName($authTest['partner_id']),
                    'service_id' => $response['service_id'] ?? $authTest['partner_id']
                ];

                $this->logInfo('Informations partenaire récupérées', $this->partnerInfo);
            }

        } catch (\Exception $e) {
            $this->logWarning('Impossible de récupérer les informations du partenaire', [
                'error' => $e->getMessage()
            ]);
            $this->partnerInfo = [
                'partner_name' => 'Partner_unknown',
                'partner_id' => 'unknown',
                'service_id' => 'unknown'
            ];
        }
    }

    /**
     * Obtenir le nom du partenaire
     */
    private function getPartnerName(int $partnerId): string
    {
        $partnerNames = [
            21 => 'Fayma',
            // Ajouter d'autres partenaires ici
        ];

        return $partnerNames[$partnerId] ?? "Partner_{$partnerId}";
    }

    /**
     * Générer un QR code
     */
    public function generateQR(?string $content = null, array $options = []): array
    {
        if (!$this->isInitialized) {
            throw new \RuntimeException('SDK non initialisé');
        }

        try {
            $this->logInfo('Génération QR code', ['content' => $content, 'options' => $options]);

            // Utiliser le contenu fourni ou générer un contenu par défaut
            $qrContent = $content ?? $this->generateSessionCode();
            
            // Générer le QR code via l'API
            $response = $this->makeRequest('/qr-generate', [
                'type' => $this->config['type'],
                'content' => $qrContent,
                'label' => $this->getTypeName($this->config['type']) . ' ' . $this->partnerInfo['partner_name'],
                ...$options
            ]);

            if (!$response['success']) {
                throw new \RuntimeException($response['message'] ?? 'Erreur lors de la génération du QR code');
            }

            $result = [
                'success' => true,
                'data' => [
                    'qr_code' => $response['data']['qrCodeUrl'] ?? $response['data']['qr_code'] ?? '',
                    'content' => $qrContent,
                    'session_id' => $response['data']['sessionId'] ?? $response['data']['session_id'] ?? '',
                    'label' => $response['data']['label'] ?? '',
                    'type' => $this->config['type'],
                    'partner_name' => $this->partnerInfo['partner_name'] ?? '',
                    'expires_at' => $response['data']['expires_at'] ?? null
                ]
            ];

            $this->logInfo('QR code généré avec succès', $result);

            return $result;

        } catch (\Exception $e) {
            $this->logError('Erreur génération QR code', ['error' => $e->getMessage()]);
            return [
                'success' => false,
                'error' => 'Erreur lors de la génération du QR code: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Obtenir le QR Writer (lazy loading)
     */
    private function getQrWriter(): PngWriter
    {
        if ($this->qrWriter === null) {
            $this->qrWriter = new PngWriter();
        }
        return $this->qrWriter;
    }

    /**
     * Générer un QR code local (sans API)
     */
    public function generateQRLocal(string $content, array $options = []): array
    {
        try {
            $this->logInfo('Génération QR code local', ['content' => $content]);

            // Vérifier que le contenu n'est pas vide
            if (empty($content)) {
                return [
                    'success' => false,
                    'error' => 'Le contenu du QR code ne peut pas être vide'
                ];
            }

            // Créer le QR code
            $qrCode = new QrCode($content);
            $qrCode->setSize($options['size'] ?? 300);
            $qrCode->setMargin($options['margin'] ?? 10);

            // Générer l'image
            $result = $this->getQrWriter()->write($qrCode);
            $dataUri = 'data:image/png;base64,' . base64_encode($result->getString());

            $response = [
                'success' => true,
                'data' => [
                    'qr_code' => $dataUri,
                    'content' => $content,
                    'session_id' => $this->generateSessionCode(),
                    'label' => $options['label'] ?? $this->getTypeName($this->config['type']),
                    'type' => $this->config['type'],
                    'generated_locally' => true
                ]
            ];

            $this->logInfo('QR code local généré avec succès');

            return $response;

        } catch (\Exception $e) {
            $this->logError('Erreur génération QR code local', ['error' => $e->getMessage()]);
            return [
                'success' => false,
                'error' => 'Erreur lors de la génération du QR code: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Vérifier le statut d'un QR code
     */
    public function checkQRStatus(string $sessionId): array
    {
        if (!$this->isInitialized) {
            throw new \RuntimeException('SDK non initialisé');
        }

        try {
            $this->logInfo('Vérification statut QR', ['session_id' => $sessionId]);

            $response = $this->makeRequest('/qr-status', [
                'serviceId' => $sessionId
            ]);

            if (!$response['success']) {
                throw new \RuntimeException($response['message'] ?? 'Erreur lors de la vérification du statut');
            }

            $this->logInfo('Statut QR récupéré', $response['data']);

            return [
                'success' => true,
                'data' => $response['data']
            ];

        } catch (\Exception $e) {
            $this->logError('Erreur vérification statut QR', ['error' => $e->getMessage()]);
            return [
                'success' => false,
                'error' => 'Erreur lors de la vérification du statut: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Effectuer une requête API
     */
    private function makeRequest(string $endpoint, array $data): array
    {
        $url = $this->config['api_url'] . $endpoint;
        
        $this->logInfo('Requête API', [
            'url' => $url,
            'endpoint' => $endpoint,
            'data_keys' => array_keys($data)
        ]);

        $requestData = array_merge($data, [
            'client_id' => $this->config['client_id'],
            'secret_id' => $this->config['secret_id']
        ]);

        $retryCount = 0;
        $maxRetries = $this->config['max_retries'];

        while ($retryCount <= $maxRetries) {
            try {
                $response = $this->httpClient->post($url, [
                    'json' => $requestData
                ]);

                $responseData = json_decode($response->getBody()->getContents(), true);

                $this->logInfo('Réponse API reçue', [
                    'status_code' => $response->getStatusCode(),
                    'success' => $responseData['success'] ?? false
                ]);

                return $responseData;

            } catch (RequestException $e) {
                $retryCount++;
                
                $this->logWarning('Erreur requête API', [
                    'attempt' => $retryCount,
                    'error' => $e->getMessage(),
                    'status_code' => $e->getResponse()?->getStatusCode()
                ]);

                if ($retryCount > $maxRetries) {
                    throw new \RuntimeException('Échec de la requête API après ' . $maxRetries . ' tentatives: ' . $e->getMessage());
                }

                // Attendre avant de réessayer
                sleep($retryCount);
            }
        }

        throw new \RuntimeException('Échec de la requête API');
    }

    /**
     * Générer un code de session unique
     */
    public function generateSessionCode(): string
    {
        $timestamp = time();
        $random = bin2hex(random_bytes(8));
        return base64_encode("{$timestamp}_{$random}");
    }

    /**
     * Obtenir le nom du type
     */
    public function getTypeName(int $type): string
    {
        return match($type) {
            1 => 'KYC',
            2 => 'Authentification',
            3 => 'SIGNATURE',
            default => 'Inconnu'
        };
    }

    /**
     * Logger une information
     */
    private function logInfo(string $message, array $context = []): void
    {
        $this->logger->info($message, $context);
    }

    /**
     * Logger un warning
     */
    private function logWarning(string $message, array $context = []): void
    {
        $this->logger->warning($message, $context);
    }

    /**
     * Logger une erreur
     */
    private function logError(string $message, array $context = []): void
    {
        $this->logger->error($message, $context);
    }

    /**
     * Obtenir la configuration
     */
    public function getConfig(): array
    {
        return $this->config;
    }

    /**
     * Obtenir les informations du partenaire
     */
    public function getPartnerInfo(): array
    {
        return $this->partnerInfo;
    }

    /**
     * Vérifier si le SDK est initialisé
     */
    public function isInitialized(): bool
    {
        return $this->isInitialized;
    }

    /**
     * Obtenir le logger
     */
    public function getLogger(): Logger
    {
        return $this->logger;
    }
} 
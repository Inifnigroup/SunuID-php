<?php

namespace SunuID\WebSocket;

use ElephantIO\Engine\SocketIO\Version2X;
use ElephantIO\Engine\SocketIO\Version1X;
use ElephantIO\Engine\SocketIO\Version0X;
use ElephantIO\Exception\ServerConnectionFailureException;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Exception;

/**
 * Client WebSocket pour le SDK SunuID PHP
 * 
 * Permet la communication en temps réel avec l'API SunuID
 * pour recevoir les notifications d'authentification et KYC
 * 
 * @version 1.0.0
 * @author SunuID Team
 * @license MIT
 */
class SunuIDWebSocket
{
    /**
     * Configuration par défaut
     */
    private const DEFAULT_CONFIG = [
        // ElephantIO (v3) attend un endpoint http(s) pour l'handshake Socket.IO
        'ws_url' => 'https://samasocket.fayma.sn:9443',
        // Version 2 par défaut (supportée par ElephantIO 3.x)
        'socketio_version' => '2',
        'connection_timeout' => 10,
        'enable_logs' => true,
        'log_level' => Logger::INFO,
        'log_file' => 'sunuid-websocket.log',
        'transports' => ['websocket', 'polling'],
        'query_params' => [],
        // Options additionnelles
        'headers' => [],
        // SSL
        'ssl_verify_peer' => true,
        'ssl_verify_peer_name' => true,
        'allow_self_signed' => false,
        // Enregistrement automatique du SID côté serveur
        'auto_register_sid' => true,
        'register_event_name' => 'register_sid',
        'register_payload_extra' => []
    ];

    /**
     * Configuration actuelle
     */
    private array $config;

    /**
     * Client Socket.IO
     */
    private $connection = null;

    /**
     * Logger
     */
    private Logger $logger;

    /**
     * Statut de connexion
     */
    private bool $isConnected = false;

    /**
     * Callbacks pour les événements
     */
    private array $callbacks = [
        'connect' => [],
        'disconnect' => [],
        'message' => [],
        'error' => [],
        'auth_success' => [],
        'auth_failure' => [],
        'kyc_complete' => [],
        'kyc_pending' => [],
        'session_expired' => []
    ];

    /**
     * Sessions actives
     */
    private array $activeSessions = [];

    /**
     * Dernière erreur
     */
    private ?string $lastError = null;

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
        // Initialiser le logger
        $this->logger = new Logger('SunuIDWebSocket');
        if ($this->config['enable_logs']) {
            $this->logger->pushHandler(new StreamHandler($this->config['log_file'], $this->config['log_level']));
        }
    }

    /**
     * Se connecter au Socket.IO
     */
    public function connect(): bool
    {
        try {
            $this->logInfo('Tentative de connexion Socket.IO', ['url' => $this->config['ws_url']]);

            // Créer le client Socket.IO selon la version
            $clientOptions = [
                'timeout' => $this->config['connection_timeout'],
                'transports' => $this->config['transports'],
                // NB: ElephantIO ne lit pas les query params depuis 'options';
                // ils doivent être sur l'URL. On conserve la clé pour compat, mais on construit l'URL ci-dessous.
                'query' => $this->config['query_params'],
                'headers' => $this->config['headers'],
                'ssl' => [
                    'verify_peer' => $this->config['ssl_verify_peer'],
                    'verify_peer_name' => $this->config['ssl_verify_peer_name'],
                    'allow_self_signed' => $this->config['allow_self_signed']
                ]
            ];

            // Construire l'URL avec les paramètres de requête (obligatoire pour ElephantIO)
            $baseUrl = $this->config['ws_url'];
            $qs = http_build_query($this->config['query_params'] ?? []);
            $urlWithQuery = $baseUrl;
            if (!empty($qs)) {
                $urlWithQuery = $baseUrl . ((strpos($baseUrl, '?') !== false) ? '&' : '?') . $qs;
            }
            $this->logInfo('URL Socket.IO', ['url' => $urlWithQuery]);

            switch ($this->config['socketio_version']) {
                case '0':
                    $this->connection = new Version0X($urlWithQuery, $clientOptions);
                    break;
                case '1':
                    $this->connection = new Version1X($urlWithQuery, $clientOptions);
                    break;
                case '2':
                default:
                    $this->connection = new Version2X($urlWithQuery, $clientOptions);
                    break;
            }

            // Établir la connexion
            $this->connection->connect();
            $this->isConnected = true;
            
            $this->logInfo('Connexion Socket.IO établie');
            $this->triggerCallbacks('connect');

            // Optionnel: envoyer automatiquement un event d'enregistrement avec le SID
            if (!empty($this->config['auto_register_sid'])) {
                try {
                    $sid = $this->getSocketId();
                    $payload = array_merge([
                        'sid' => $sid,
                        'ts' => time(),
                        'source' => 'php-sdk'
                    ], is_array($this->config['register_payload_extra']) ? $this->config['register_payload_extra'] : []);
                    $eventName = $this->config['register_event_name'] ?? 'register_sid';
                    if ($sid) {
                        $this->sendMessage(['event' => $eventName, 'data' => $payload]);
                        $this->logInfo('Event auto-register envoyé', ['event' => $eventName, 'sid' => $sid]);
                    }
                } catch (Exception $e) {
                    $this->logWarning('Auto-register SID échoué', ['error' => $e->getMessage()]);
                }
            }

            return true;
        } catch (ServerConnectionFailureException $e) {
            $this->logError('Échec de connexion Socket.IO', ['error' => $e->getMessage()]);
            $this->lastError = $e->getMessage();
            $this->triggerCallbacks('error', ['error' => $e->getMessage()]);
            return false;
        } catch (Exception $e) {
            $this->logError('Erreur lors de la connexion Socket.IO', ['error' => $e->getMessage()]);
            $this->lastError = $e->getMessage();
            $this->triggerCallbacks('error', ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Recevoir un message du Socket.IO
     */
    public function receive(): ?array
    {
        if (!$this->connection || !$this->isConnected) {
            return null;
        }

        try {
            $message = $this->connection->read();
            
            if ($message) {
                $data = is_array($message) ? $message : json_decode($message, true);
                
                if ($data) {
                    $this->handleMessage($data);
                    return $data;
                }
            }
        } catch (Exception $e) {
            $this->logError('Erreur lors de la réception', ['error' => $e->getMessage()]);
        }

        return null;
    }

    /**
     * Gérer les messages reçus
     */
    private function handleMessage(array $data): void
    {
        $this->logInfo('Message WebSocket reçu', ['type' => $data['type'] ?? 'unknown']);

        // Traiter selon le type de message
        switch ($data['type'] ?? '') {
            case 'auth_success':
                $this->handleAuthSuccess($data);
                break;
                
            case 'auth_failure':
                $this->handleAuthFailure($data);
                break;
                
            case 'kyc_complete':
                $this->handleKycComplete($data);
                break;
                
            case 'kyc_pending':
                $this->handleKycPending($data);
                break;
                
            case 'session_expired':
                $this->handleSessionExpired($data);
                break;
                
            case 'heartbeat':
                $this->handleHeartbeat($data);
                break;
                
            default:
                $this->triggerCallbacks('message', $data);
                break;
        }
    }

    /**
     * Gérer le succès d'authentification
     */
    private function handleAuthSuccess(array $data): void
    {
        $sessionId = $data['session_id'] ?? null;
        $userId = $data['user_id'] ?? null;
        
        $this->logInfo('Authentification réussie', [
            'session_id' => $sessionId,
            'user_id' => $userId
        ]);

        // Mettre à jour la session
        if ($sessionId) {
            $this->activeSessions[$sessionId] = array_merge(
                $this->activeSessions[$sessionId] ?? [],
                ['status' => 'authenticated', 'user_id' => $userId]
            );
        }

        $this->triggerCallbacks('auth_success', $data);
    }

    /**
     * Gérer l'échec d'authentification
     */
    private function handleAuthFailure(array $data): void
    {
        $sessionId = $data['session_id'] ?? null;
        $reason = $data['reason'] ?? 'unknown';
        
        $this->logWarning('Échec d\'authentification', [
            'session_id' => $sessionId,
            'reason' => $reason
        ]);

        // Mettre à jour la session
        if ($sessionId) {
            $this->activeSessions[$sessionId] = array_merge(
                $this->activeSessions[$sessionId] ?? [],
                ['status' => 'failed', 'reason' => $reason]
            );
        }

        $this->triggerCallbacks('auth_failure', $data);
    }

    /**
     * Gérer la completion KYC
     */
    private function handleKycComplete(array $data): void
    {
        $sessionId = $data['session_id'] ?? null;
        $kycData = $data['kyc_data'] ?? [];
        
        $this->logInfo('KYC complété', [
            'session_id' => $sessionId,
            'kyc_data' => $kycData
        ]);

        // Mettre à jour la session
        if ($sessionId) {
            $this->activeSessions[$sessionId] = array_merge(
                $this->activeSessions[$sessionId] ?? [],
                ['status' => 'kyc_complete', 'kyc_data' => $kycData]
            );
        }

        $this->triggerCallbacks('kyc_complete', $data);
    }

    /**
     * Gérer le KYC en attente
     */
    private function handleKycPending(array $data): void
    {
        $sessionId = $data['session_id'] ?? null;
        $pendingSteps = $data['pending_steps'] ?? [];
        
        $this->logInfo('KYC en attente', [
            'session_id' => $sessionId,
            'pending_steps' => $pendingSteps
        ]);

        // Mettre à jour la session
        if ($sessionId) {
            $this->activeSessions[$sessionId] = array_merge(
                $this->activeSessions[$sessionId] ?? [],
                ['status' => 'kyc_pending', 'pending_steps' => $pendingSteps]
            );
        }

        $this->triggerCallbacks('kyc_pending', $data);
    }

    /**
     * Gérer l'expiration de session
     */
    private function handleSessionExpired(array $data): void
    {
        $sessionId = $data['session_id'] ?? null;
        
        $this->logWarning('Session expirée', ['session_id' => $sessionId]);

        // Supprimer la session
        if ($sessionId && isset($this->activeSessions[$sessionId])) {
            unset($this->activeSessions[$sessionId]);
        }

        $this->triggerCallbacks('session_expired', $data);
    }

    /**
     * Gérer le heartbeat
     */
    private function handleHeartbeat(array $data): void
    {
        $this->logInfo('Heartbeat reçu', ['timestamp' => $data['timestamp'] ?? time()]);
        
        // Répondre au heartbeat
        $this->sendHeartbeat();
    }

    /**
     * Gérer la déconnexion
     */
    private function handleDisconnect($code, $reason): void
    {
        $this->isConnected = false;
        $this->connection = null;
        
        $this->logWarning('Déconnexion WebSocket', [
            'code' => $code,
            'reason' => $reason
        ]);

        $this->triggerCallbacks('disconnect', ['code' => $code, 'reason' => $reason]);

        // Tentative de reconnexion automatique
        $this->scheduleReconnect();
    }

    /**
     * Gérer les erreurs
     */
    private function handleError(Exception $e): void
    {
        $this->logError('Erreur WebSocket', ['error' => $e->getMessage()]);
        $this->triggerCallbacks('error', ['error' => $e->getMessage()]);
    }

    /**
     * Programmer une reconnexion
     */
    private function scheduleReconnect(): void
    {
        if ($this->reconnectAttempts >= $this->config['max_reconnect_attempts']) {
            $this->logError('Nombre maximum de tentatives de reconnexion atteint');
            return;
        }

        $this->reconnectAttempts++;
        $delay = $this->config['reconnect_interval'] * $this->reconnectAttempts;

        $this->logInfo('Programmation de reconnexion', [
            'attempt' => $this->reconnectAttempts,
            'delay' => $delay
        ]);

        $this->loop->addTimer($delay / 1000, function () {
            $this->connect();
        });
    }

    /**
     * Démarrer le heartbeat
     */
    private function startHeartbeat(): void
    {
        $this->loop->addPeriodicTimer($this->config['heartbeat_interval'] / 1000, function () {
            if ($this->isConnected) {
                $this->sendHeartbeat();
            }
        });
    }

    /**
     * Envoyer un heartbeat
     */
    private function sendHeartbeat(): void
    {
        if (!$this->connection) {
            return;
        }

        $heartbeat = json_encode([
            'type' => 'heartbeat',
            'timestamp' => time()
        ]);

        $this->connection->send($heartbeat);
        $this->logInfo('Heartbeat envoyé');
    }

    /**
     * S'abonner à une session
     */
    public function subscribeToSession(string $sessionId): bool
    {
        if (!$this->isConnected) {
            $this->logWarning('Impossible de s\'abonner : non connecté');
            return false;
        }

        try {
            $this->connection->emit('subscribe', [
                'session_id' => $sessionId
            ]);
            
            $this->activeSessions[$sessionId] = [
                'status' => 'subscribed',
                'subscribed_at' => time()
            ];

            $this->logInfo('Abonnement à la session', ['session_id' => $sessionId]);
            return true;
        } catch (Exception $e) {
            $this->logError('Erreur lors de l\'abonnement', ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Se désabonner d'une session
     */
    public function unsubscribeFromSession(string $sessionId): bool
    {
        if (!$this->isConnected) {
            return false;
        }

        try {
            $this->connection->emit('unsubscribe', [
                'session_id' => $sessionId
            ]);
            
            if (isset($this->activeSessions[$sessionId])) {
                unset($this->activeSessions[$sessionId]);
            }

            $this->logInfo('Désabonnement de la session', ['session_id' => $sessionId]);
            return true;
        } catch (Exception $e) {
            $this->logError('Erreur lors du désabonnement', ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Envoyer un message personnalisé
     */
    public function sendMessage(array $data): bool
    {
        if (!$this->isConnected) {
            return false;
        }

        try {
            $event = $data['event'] ?? 'message';
            $payload = $data['data'] ?? $data;
            
            $this->connection->emit($event, $payload);
            
            $this->logInfo('Message envoyé', ['event' => $event]);
            return true;
        } catch (Exception $e) {
            $this->logError('Erreur lors de l\'envoi', ['error' => $e->getMessage()]);
            return false;
        }
    }



    /**
     * Déclencher les callbacks pour un événement
     */
    private function triggerCallbacks(string $event, array $data = []): void
    {
        if (isset($this->callbacks[$event])) {
            foreach ($this->callbacks[$event] as $callback) {
                try {
                    $callback($data);
                } catch (Exception $e) {
                    $this->logError('Erreur dans le callback', [
                        'event' => $event,
                        'error' => $e->getMessage()
                    ]);
                }
            }
        }
    }

    /**
     * Se déconnecter
     */
    public function disconnect(): void
    {
        if ($this->connection) {
            $this->connection->close();
        }
        
        $this->isConnected = false;
        $this->connection = null;
        
        $this->logInfo('Déconnexion Socket.IO manuelle');
    }

    /**
     * Écouter un événement Socket.IO
     */
    public function on(string $event, callable $callback): void
    {
        if (isset($this->callbacks[$event])) {
            $this->callbacks[$event][] = $callback;
            $this->logInfo('Callback ajouté pour l\'événement', ['event' => $event]);
        } else {
            $this->logWarning('Événement non supporté', ['event' => $event]);
        }
    }

    /**
     * Vérifier si connecté
     */
    public function isConnected(): bool
    {
        return $this->isConnected;
    }

    /**
     * Obtenir le dernier message d'erreur
     */
    public function getLastError(): ?string
    {
        return $this->lastError;
    }

    /**
     * Récupérer l'identifiant de socket (SID Engine.IO) si disponible
     */
    public function getSocketId(): ?string
    {
        if (!$this->connection) {
            return null;
        }

        try {
            // Utiliser la réflexion pour lire la session interne d'ElephantIO
            $ref = new \ReflectionClass($this->connection);
            if ($ref->hasProperty('session')) {
                $prop = $ref->getProperty('session');
                $prop->setAccessible(true);
                $session = $prop->getValue($this->connection);
                if ($session !== null) {
                    $sidProp = new \ReflectionProperty($session, 'id');
                    $sidProp->setAccessible(true);
                    $sid = $sidProp->getValue($session);
                    return is_string($sid) ? $sid : null;
                }
            }
        } catch (\Throwable $e) {
            $this->logWarning('Impossible de récupérer le socketId', ['error' => $e->getMessage()]);
        }

        return null;
    }

    /**
     * Obtenir les sessions actives
     */
    public function getActiveSessions(): array
    {
        return $this->activeSessions;
    }

    /**
     * Obtenir la configuration
     */
    public function getConfig(): array
    {
        return $this->config;
    }

    /**
     * Logger les informations
     */
    private function logInfo(string $message, array $context = []): void
    {
        $this->logger->info($message, $context);
    }

    /**
     * Logger les avertissements
     */
    private function logWarning(string $message, array $context = []): void
    {
        $this->logger->warning($message, $context);
    }

    /**
     * Logger les erreurs
     */
    private function logError(string $message, array $context = []): void
    {
        $this->logger->error($message, $context);
    }
} 
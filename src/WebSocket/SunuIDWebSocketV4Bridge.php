<?php

namespace SunuID\WebSocket;

use GuzzleHttp\Client;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

class SunuIDWebSocketV4Bridge
{
    private array $config;
    private Client $http;
    private Logger $logger;
    private bool $isConnected = false;
    private ?string $socketId = null;

    public function __construct(array $config = [])
    {
        $this->config = $config;
        $this->http = new Client([
            'base_uri' => rtrim($config['bridge_url'] ?? '', '/') . '/',
            'timeout' => $config['bridge_timeout'] ?? 10,
        ]);

        $this->logger = new Logger('SunuIDWebSocketV4Bridge');
        if (!empty($config['enable_logs'])) {
            $this->logger->pushHandler(new StreamHandler($config['log_file'] ?? 'sunuid-websocket-v4.log', $config['log_level'] ?? Logger::INFO));
        }
    }

    public function connect(): bool
    {
        try {
            $payload = [
                'token' => $this->config['token'] ?? '',
                'type' => $this->config['type'] ?? 'web',
                'userId' => $this->config['userId'] ?? '',
                'username' => $this->config['username'] ?? 'php-sdk',
                'query' => $this->config['query_params'] ?? [],
            ];

            $res = $this->http->post('connect', ['json' => $payload]);
            $data = json_decode((string) $res->getBody(), true);
            if (!($data['success'] ?? false)) {
                $this->logger->warning('Bridge connect failed', ['response' => $data]);
                return false;
            }

            $this->socketId = $data['sid'] ?? null;
            $this->isConnected = !empty($this->socketId);
            $this->logger->info('Bridge connected', ['sid' => $this->socketId]);
            return $this->isConnected;
        } catch (\Throwable $e) {
            $this->logger->error('Bridge connect error', ['error' => $e->getMessage()]);
            return false;
        }
    }

    public function disconnect(): void
    {
        $this->isConnected = false;
        $this->socketId = null;
    }

    public function isConnected(): bool
    {
        return $this->isConnected;
    }

    public function getSocketId(): ?string
    {
        return $this->socketId;
    }

    public function sendMessage(array $data): bool
    {
        if (!$this->isConnected || empty($this->socketId)) {
            return false;
        }
        try {
            $event = $data['event'] ?? 'message';
            $payload = $data['data'] ?? $data;
            $res = $this->http->post('emit', [
                'json' => [
                    'sid' => $this->socketId,
                    'event' => $event,
                    'data' => $payload,
                ]
            ]);
            $ok = $res->getStatusCode() === 200;
            if ($ok) {
                $this->logger->info('Bridge emit ok', ['event' => $event]);
            }
            return $ok;
        } catch (\Throwable $e) {
            $this->logger->error('Bridge emit error', ['error' => $e->getMessage()]);
            return false;
        }
    }

    public function subscribeToSession(string $sessionId): bool
    {
        return $this->sendMessage(['event' => 'subscribe', 'data' => ['session_id' => $sessionId]]);
    }

    public function unsubscribeFromSession(string $sessionId): bool
    {
        return $this->sendMessage(['event' => 'unsubscribe', 'data' => ['session_id' => $sessionId]]);
    }
}



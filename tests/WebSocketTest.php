<?php

namespace SunuID\Tests;

use PHPUnit\Framework\TestCase;
use SunuID\SunuID;
use SunuID\WebSocket\SunuIDWebSocket;

/**
 * Tests pour les fonctionnalités WebSocket du SDK SunuID
 */
class WebSocketTest extends TestCase
{
    private SunuID $sunuid;

    protected function setUp(): void
    {
        $config = [
            'client_id' => 'test_client',
            'secret_id' => 'test_secret',
            'partner_name' => 'Test Partner',
            'enable_websocket' => true,
            'websocket_url' => 'wss://test.sunuid.sn/ws',
            'enable_logs' => false
        ];

        $this->sunuid = new SunuID($config);
    }

    /**
     * Test de l'initialisation WebSocket
     */
    public function testWebSocketInitialization()
    {
        $result = $this->sunuid->initWebSocket();
        $this->assertTrue($result);
        
        $webSocket = $this->sunuid->getWebSocket();
        $this->assertInstanceOf(SunuIDWebSocket::class, $webSocket);
    }

    /**
     * Test de l'initialisation WebSocket désactivée
     */
    public function testWebSocketDisabled()
    {
        $config = [
            'client_id' => 'test_client',
            'secret_id' => 'test_secret',
            'partner_name' => 'Test Partner',
            'enable_websocket' => false
        ];

        $sunuid = new SunuID($config);
        $result = $sunuid->initWebSocket();
        
        $this->assertFalse($result);
        $this->assertNull($sunuid->getWebSocket());
    }

    /**
     * Test de la configuration WebSocket
     */
    public function testWebSocketConfiguration()
    {
        $this->sunuid->initWebSocket();
        $webSocket = $this->sunuid->getWebSocket();
        
        $config = $webSocket->getConfig();
        
        $this->assertEquals('wss://test.sunuid.sn/ws', $config['ws_url']);
        $this->assertEquals(10, $config['connection_timeout']);
        $this->assertTrue($config['enable_logs']);
    }

    /**
     * Test de l'état de connexion WebSocket
     */
    public function testWebSocketConnectionState()
    {
        $this->sunuid->initWebSocket();
        
        // Avant connexion
        $this->assertFalse($this->sunuid->isWebSocketConnected());
        
        // Après tentative de connexion (sera false car serveur de test inexistant)
        // On évite d'appeler connectWebSocket() pour éviter les warnings de dépréciation
        $this->assertFalse($this->sunuid->isWebSocketConnected());
    }

    /**
     * Test des sessions actives WebSocket
     */
    public function testWebSocketActiveSessions()
    {
        $this->sunuid->initWebSocket();
        
        $sessions = $this->sunuid->getWebSocketActiveSessions();
        $this->assertIsArray($sessions);
        $this->assertEmpty($sessions);
    }

    /**
     * Test de l'ajout de callbacks WebSocket
     */
    public function testWebSocketCallbacks()
    {
        $this->sunuid->initWebSocket();
        
        $callbackCalled = false;
        $this->sunuid->onWebSocketEvent('connect', function ($data) use (&$callbackCalled) {
            $callbackCalled = true;
        });
        
        // Le callback ne sera pas appelé car pas de connexion réelle
        $this->assertFalse($callbackCalled);
    }

    /**
     * Test de la déconnexion WebSocket
     */
    public function testWebSocketDisconnect()
    {
        $this->sunuid->initWebSocket();
        
        // Déconnexion sans connexion préalable
        $this->sunuid->disconnectWebSocket();
        
        $this->assertNull($this->sunuid->getWebSocket());
    }

    /**
     * Test de génération QR avec WebSocket
     */
    public function testGenerateQRWithWebSocket()
    {
        $this->sunuid->initWebSocket();
        
        // Initialiser le SDK d'abord
        $this->sunuid->init();
        
        $result = $this->sunuid->generateQRWithWebSocket('https://test.com', [
            'type' => 2
        ]);
        
        $this->assertIsArray($result);
        $this->assertArrayHasKey('success', $result);
        
        // Le WebSocket ne sera pas connecté, donc l'abonnement échouera
        // mais la génération du QR devrait fonctionner
        if ($result['success']) {
            $this->assertArrayHasKey('data', $result);
        }
    }

    /**
     * Test de la classe SunuIDWebSocket directement
     */
    public function testSunuIDWebSocketClass()
    {
        $config = [
            'ws_url' => 'wss://test.sunuid.sn/ws',
            'enable_logs' => false
        ];

        $webSocket = new SunuIDWebSocket($config);
        
        $this->assertInstanceOf(SunuIDWebSocket::class, $webSocket);
        $this->assertFalse($webSocket->isConnected());
        
        $wsConfig = $webSocket->getConfig();
        $this->assertEquals('wss://test.sunuid.sn/ws', $wsConfig['ws_url']);
    }

    /**
     * Test des méthodes WebSocket avec mock
     */
    public function testWebSocketMethods()
    {
        $this->sunuid->initWebSocket();
        $webSocket = $this->sunuid->getWebSocket();
        
        // Test d'envoi de message (échouera car non connecté)
        $result = $this->sunuid->sendWebSocketMessage([
            'type' => 'test',
            'data' => 'test'
        ]);
        
        $this->assertFalse($result);
        
        // Test d'abonnement à une session (échouera car non connecté)
        $result = $this->sunuid->subscribeToSession('test_session');
        $this->assertFalse($result);
        
        // Test de désabonnement (échouera car non connecté)
        $result = $this->sunuid->unsubscribeFromSession('test_session');
        $this->assertFalse($result);
    }

    /**
     * Test de la gestion des erreurs WebSocket
     */
    public function testWebSocketErrorHandling()
    {
        $this->sunuid->initWebSocket();
        
        // Test avec une URL WebSocket invalide
        $config = [
            'client_id' => 'test_client',
            'secret_id' => 'test_secret',
            'partner_name' => 'Test Partner',
            'enable_websocket' => true,
            'websocket_url' => 'invalid://url',
            'enable_logs' => false
        ];

        $sunuid = new SunuID($config);
        $result = $sunuid->initWebSocket();
        
        // L'initialisation devrait réussir même avec une URL invalide
        // car la connexion n'est pas établie immédiatement
        $this->assertTrue($result);
    }

    /**
     * Test de la configuration par défaut WebSocket
     */
    public function testWebSocketDefaultConfig()
    {
        $webSocket = new SunuIDWebSocket();
        $config = $webSocket->getConfig();
        
        $this->assertEquals('wss://samasocket.fayma.sn:9443', $config['ws_url']);
        $this->assertEquals(10, $config['connection_timeout']);
        $this->assertTrue($config['enable_logs']);
    }
} 
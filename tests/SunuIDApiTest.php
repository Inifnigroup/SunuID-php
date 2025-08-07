<?php

namespace SunuID\Tests;

use PHPUnit\Framework\TestCase;
use SunuID\SunuID;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Middleware;

class SunuIDApiTest extends TestCase
{
    private SunuID $sunuid;
    private array $container = [];

    protected function setUp(): void
    {
        $this->sunuid = new SunuID([
            'client_id' => 'test_client_id',
            'secret_id' => 'test_secret_id',
            'partner_name' => 'Test Partner',
            'enable_logs' => false
        ]);
    }

    /**
     * Test successful QR generation with mocked API response
     */
    public function testGenerateQRWithSuccessfulApiResponse()
    {
        // Mock successful API response
        $mockResponse = [
            'success' => true,
            'data' => [
                'session_id' => 'test_session_123',
                'qr_code' => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNkYPhfDwAChwGA60e6kgAAAABJRU5ErkJggg==',
                'content' => 'https://example.com/auth?session=test_session_123',
                'expires_at' => '2024-12-31T23:59:59Z'
            ]
        ];

        $this->createMockHttpClient(200, json_encode($mockResponse));
        
        $result = $this->sunuid->generateQR('https://example.com/auth');
        
        $this->assertIsArray($result);
        $this->assertArrayHasKey('success', $result);
        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('data', $result);
        $this->assertArrayHasKey('session_id', $result['data']);
        $this->assertArrayHasKey('qr_code', $result['data']);
        $this->assertArrayHasKey('content', $result['data']);
    }

    /**
     * Test failed QR generation with mocked API error response
     */
    public function testGenerateQRWithApiErrorResponse()
    {
        // Mock error API response
        $mockResponse = [
            'success' => false,
            'error' => 'Invalid credentials',
            'code' => 401
        ];

        $this->createMockHttpClient(401, json_encode($mockResponse));
        
        $result = $this->sunuid->generateQR('https://example.com/auth');
        
        $this->assertIsArray($result);
        $this->assertArrayHasKey('success', $result);
        $this->assertFalse($result['success']);
        $this->assertArrayHasKey('error', $result);
    }

    /**
     * Test QR status check with successful response
     */
    public function testCheckQRStatusWithSuccessfulResponse()
    {
        // Mock successful status response
        $mockResponse = [
            'success' => true,
            'data' => [
                'session_id' => 'test_session_123',
                'status' => 'completed',
                'user_data' => [
                    'name' => 'John Doe',
                    'email' => 'john@example.com'
                ],
                'completed_at' => '2024-12-31T12:00:00Z'
            ]
        ];

        $this->createMockHttpClient(200, json_encode($mockResponse));
        
        $result = $this->sunuid->checkQRStatus('test_session_123');
        
        $this->assertIsArray($result);
        $this->assertArrayHasKey('success', $result);
        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('data', $result);
        $this->assertArrayHasKey('status', $result['data']);
        $this->assertEquals('completed', $result['data']['status']);
    }

    /**
     * Test QR status check with pending response
     */
    public function testCheckQRStatusWithPendingResponse()
    {
        // Mock pending status response
        $mockResponse = [
            'success' => true,
            'data' => [
                'session_id' => 'test_session_123',
                'status' => 'pending',
                'message' => 'Waiting for user authentication'
            ]
        ];

        $this->createMockHttpClient(200, json_encode($mockResponse));
        
        $result = $this->sunuid->checkQRStatus('test_session_123');
        
        $this->assertIsArray($result);
        $this->assertArrayHasKey('success', $result);
        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('data', $result);
        $this->assertArrayHasKey('status', $result['data']);
        $this->assertEquals('pending', $result['data']['status']);
    }

    /**
     * Test QR status check with expired session
     */
    public function testCheckQRStatusWithExpiredSession()
    {
        // Mock expired session response
        $mockResponse = [
            'success' => false,
            'error' => 'Session expired',
            'code' => 410
        ];

        $this->createMockHttpClient(410, json_encode($mockResponse));
        
        $result = $this->sunuid->checkQRStatus('expired_session_123');
        
        $this->assertIsArray($result);
        $this->assertArrayHasKey('success', $result);
        $this->assertFalse($result['success']);
        $this->assertArrayHasKey('error', $result);
    }

    /**
     * Test initialization with successful partner info
     */
    public function testInitWithSuccessfulPartnerInfo()
    {
        // Mock successful partner info response
        $mockResponse = [
            'success' => true,
            'data' => [
                'partner_id' => 123,
                'partner_name' => 'Test Partner',
                'status' => 'active',
                'features' => ['auth', 'kyc'],
                'limits' => [
                    'daily_requests' => 1000,
                    'monthly_requests' => 30000
                ]
            ]
        ];

        $this->createMockHttpClient(200, json_encode($mockResponse));
        
        $result = $this->sunuid->init();
        
        $this->assertTrue($result);
        $this->assertTrue($this->sunuid->isInitialized());
        
        $partnerInfo = $this->sunuid->getPartnerInfo();
        $this->assertIsArray($partnerInfo);
        $this->assertArrayHasKey('partner_id', $partnerInfo);
        $this->assertEquals(123, $partnerInfo['partner_id']);
    }

    /**
     * Test initialization with invalid credentials
     */
    public function testInitWithInvalidCredentials()
    {
        // Mock invalid credentials response
        $mockResponse = [
            'success' => false,
            'error' => 'Invalid client_id or secret_id',
            'code' => 401
        ];

        $this->createMockHttpClient(401, json_encode($mockResponse));
        
        $result = $this->sunuid->init();
        
        $this->assertFalse($result);
        $this->assertFalse($this->sunuid->isInitialized());
    }

    /**
     * Test network timeout handling
     */
    public function testNetworkTimeoutHandling()
    {
        // Create a mock that simulates timeout
        $mock = new MockHandler([
            new \GuzzleHttp\Exception\ConnectException(
                'Connection timed out',
                new \GuzzleHttp\Psr7\Request('POST', 'test')
            )
        ]);

        $handlerStack = HandlerStack::create($mock);
        $client = new Client(['handler' => $handlerStack]);
        
        // Use reflection to set the mocked client
        $reflection = new \ReflectionClass($this->sunuid);
        $httpClientProperty = $reflection->getProperty('httpClient');
        $httpClientProperty->setAccessible(true);
        $httpClientProperty->setValue($this->sunuid, $client);
        
        $result = $this->sunuid->generateQR('https://example.com/auth');
        
        $this->assertIsArray($result);
        $this->assertArrayHasKey('success', $result);
        $this->assertFalse($result['success']);
        $this->assertArrayHasKey('error', $result);
    }

    /**
     * Test retry mechanism with temporary failures
     */
    public function testRetryMechanismWithTemporaryFailures()
    {
        // Mock responses: first two failures, then success
        $mock = new MockHandler([
            new Response(500, [], json_encode(['success' => false, 'error' => 'Internal server error'])),
            new Response(503, [], json_encode(['success' => false, 'error' => 'Service unavailable'])),
            new Response(200, [], json_encode([
                'success' => true,
                'data' => [
                    'session_id' => 'test_session_123',
                    'qr_code' => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNkYPhfDwAChwGA60e6kgAAAABJRU5ErkJggg==',
                    'content' => 'https://example.com/auth?session=test_session_123'
                ]
            ]))
        ]);

        $handlerStack = HandlerStack::create($mock);
        $client = new Client(['handler' => $handlerStack]);
        
        // Use reflection to set the mocked client
        $reflection = new \ReflectionClass($this->sunuid);
        $httpClientProperty = $reflection->getProperty('httpClient');
        $httpClientProperty->setAccessible(true);
        $httpClientProperty->setValue($this->sunuid, $client);
        
        $result = $this->sunuid->generateQR('https://example.com/auth');
        
        $this->assertIsArray($result);
        $this->assertArrayHasKey('success', $result);
        $this->assertTrue($result['success']);
    }

    /**
     * Helper method to create a mock HTTP client
     */
    private function createMockHttpClient(int $statusCode, string $responseBody): void
    {
        $mock = new MockHandler([
            new Response($statusCode, [], $responseBody)
        ]);

        $handlerStack = HandlerStack::create($mock);
        $client = new Client(['handler' => $handlerStack]);
        
        // Use reflection to set the mocked client
        $reflection = new \ReflectionClass($this->sunuid);
        $httpClientProperty = $reflection->getProperty('httpClient');
        $httpClientProperty->setAccessible(true);
        $httpClientProperty->setValue($this->sunuid, $client);
    }

    /**
     * Test different QR content types with API
     */
    public function testGenerateQRWithDifferentContentTypes()
    {
        $testContents = [
            'https://example.com/auth',
            'tel:+1234567890',
            'mailto:test@example.com',
            'SMS:+1234567890:Hello World'
        ];

        foreach ($testContents as $content) {
            $mockResponse = [
                'success' => true,
                'data' => [
                    'session_id' => 'test_session_' . md5($content),
                    'qr_code' => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNkYPhfDwAChwGA60e6kgAAAABJRU5ErkJggg==',
                    'content' => $content,
                    'expires_at' => '2024-12-31T23:59:59Z'
                ]
            ];

            $this->createMockHttpClient(200, json_encode($mockResponse));
            
            $result = $this->sunuid->generateQR($content);
            
            $this->assertIsArray($result);
            $this->assertArrayHasKey('success', $result);
            $this->assertTrue($result['success']);
            $this->assertArrayHasKey('data', $result);
            $this->assertArrayHasKey('content', $result['data']);
            $this->assertEquals($content, $result['data']['content']);
        }
    }

    /**
     * Test QR generation with custom options
     */
    public function testGenerateQRWithCustomOptions()
    {
        $options = [
            'theme' => 'dark',
            'language' => 'en',
            'size' => 400,
            'margin' => 15
        ];

        $mockResponse = [
            'success' => true,
            'data' => [
                'session_id' => 'test_session_123',
                'qr_code' => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNkYPhfDwAChwGA60e6kgAAAABJRU5ErkJggg==',
                'content' => 'https://example.com/auth',
                'options' => $options,
                'expires_at' => '2024-12-31T23:59:59Z'
            ]
        ];

        $this->createMockHttpClient(200, json_encode($mockResponse));
        
        $result = $this->sunuid->generateQR('https://example.com/auth', $options);
        
        $this->assertIsArray($result);
        $this->assertArrayHasKey('success', $result);
        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('data', $result);
        $this->assertArrayHasKey('options', $result['data']);
    }
} 
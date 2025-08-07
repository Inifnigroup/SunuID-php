<?php

namespace SunuID\Tests;

use PHPUnit\Framework\TestCase;
use SunuID\SunuID;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Middleware;

class SunuIDTest extends TestCase
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

    public function testConstructorWithDefaultConfig()
    {
        $sunuid = new SunuID();
        $config = $sunuid->getConfig();
        
        $this->assertEquals('https://api.sunuid.fayma.sn', $config['api_url']);
        $this->assertEquals(2, $config['type']);
        $this->assertEquals('light', $config['theme']);
        $this->assertEquals('fr', $config['language']);
        $this->assertFalse($config['auto_refresh']);
        $this->assertEquals(30000, $config['refresh_interval']);
        $this->assertEquals(10, $config['request_timeout']);
        $this->assertEquals(3, $config['max_retries']);
    }

    public function testConstructorWithCustomConfig()
    {
        $customConfig = [
            'client_id' => 'custom_client_id',
            'secret_id' => 'custom_secret_id',
            'partner_name' => 'Custom Partner',
            'theme' => 'dark',
            'language' => 'en',
            'auto_refresh' => true,
            'refresh_interval' => 60000,
            'request_timeout' => 15,
            'max_retries' => 5
        ];

        $sunuid = new SunuID($customConfig);
        $config = $sunuid->getConfig();
        
        $this->assertEquals('custom_client_id', $config['client_id']);
        $this->assertEquals('custom_secret_id', $config['secret_id']);
        $this->assertEquals('Custom Partner', $config['partner_name']);
        $this->assertEquals('dark', $config['theme']);
        $this->assertEquals('en', $config['language']);
        $this->assertTrue($config['auto_refresh']);
        $this->assertEquals(60000, $config['refresh_interval']);
        $this->assertEquals(15, $config['request_timeout']);
        $this->assertEquals(5, $config['max_retries']);
    }

    public function testGetConfig()
    {
        $config = $this->sunuid->getConfig();
        
        $this->assertIsArray($config);
        $this->assertArrayHasKey('api_url', $config);
        $this->assertArrayHasKey('client_id', $config);
        $this->assertArrayHasKey('secret_id', $config);
        $this->assertArrayHasKey('type', $config);
        $this->assertArrayHasKey('partner_name', $config);
        $this->assertArrayHasKey('theme', $config);
        $this->assertArrayHasKey('language', $config);
    }

    public function testIsInitializedBeforeInit()
    {
        $this->assertFalse($this->sunuid->isInitialized());
    }

    public function testGetPartnerInfoBeforeInit()
    {
        $partnerInfo = $this->sunuid->getPartnerInfo();
        $this->assertIsArray($partnerInfo);
        $this->assertEmpty($partnerInfo);
    }

    public function testGenerateSessionCode()
    {
        // Test that session code generation works
        $sessionCode1 = $this->sunuid->generateSessionCode();
        $sessionCode2 = $this->sunuid->generateSessionCode();
        
        $this->assertIsString($sessionCode1);
        $this->assertIsString($sessionCode2);
        $this->assertNotEquals($sessionCode1, $sessionCode2); // Should be unique
        $this->assertGreaterThan(0, strlen($sessionCode1)); // Should not be empty
    }

    public function testGetTypeName()
    {
        $this->assertEquals('Authentification', $this->sunuid->getTypeName(2));
        $this->assertEquals('KYC', $this->sunuid->getTypeName(1));
        $this->assertEquals('Inconnu', $this->sunuid->getTypeName(999));
    }

    public function testGenerateQRLocal()
    {
        $content = 'https://example.com/test';
        $options = [
            'size' => 300,
            'margin' => 10,
            'foreground_color' => ['r' => 0, 'g' => 0, 'b' => 0],
            'background_color' => ['r' => 255, 'g' => 255, 'b' => 255]
        ];

        $result = $this->sunuid->generateQRLocal($content, $options);
        
        $this->assertIsArray($result);
        $this->assertArrayHasKey('success', $result);
        $this->assertArrayHasKey('data', $result);
        
        if ($result['success']) {
            $this->assertArrayHasKey('qr_code', $result['data']);
            $this->assertArrayHasKey('content', $result['data']);
            $this->assertEquals($content, $result['data']['content']);
        }
    }

    public function testGenerateQRLocalWithInvalidContent()
    {
        $result = $this->sunuid->generateQRLocal('', []);
        
        $this->assertIsArray($result);
        $this->assertArrayHasKey('success', $result);
        $this->assertFalse($result['success']);
        $this->assertArrayHasKey('error', $result);
    }

    public function testGenerateQRLocalWithInvalidOptions()
    {
        $content = 'https://example.com/test';
        $options = [
            'size' => -100, // Invalid size
            'margin' => -10 // Invalid margin
        ];

        $result = $this->sunuid->generateQRLocal($content, $options);
        
        $this->assertIsArray($result);
        $this->assertArrayHasKey('success', $result);
        // Should still work as the SDK should handle invalid options gracefully
    }

    public function testLoggerAccess()
    {
        $logger = $this->sunuid->getLogger();
        $this->assertInstanceOf(\Monolog\Logger::class, $logger);
    }



    /**
     * Test configuration validation
     */
    public function testValidateConfigWithMissingCredentials()
    {
        $sunuid = new SunuID([]);
        
        $this->expectException(\InvalidArgumentException::class);
        $sunuid->validateConfig();
    }

    public function testValidateConfigWithValidCredentials()
    {
        // Should not throw exception
        $this->sunuid->validateConfig();
        $this->assertTrue(true); // If we reach here, no exception was thrown
    }

    /**
     * Test QR generation with different content types
     */
    public function testGenerateQRLocalWithDifferentContentTypes()
    {
        $testContents = [
            'https://example.com',
            'tel:+1234567890',
            'mailto:test@example.com',
            'SMS:+1234567890:Hello World',
            'WIFI:S:MyWiFi;T:WPA;P:password123;;',
            'BEGIN:VCARD\nVERSION:3.0\nFN:John Doe\nTEL:+1234567890\nEND:VCARD'
        ];

        foreach ($testContents as $content) {
            $result = $this->sunuid->generateQRLocal($content);
            $this->assertIsArray($result);
            $this->assertArrayHasKey('success', $result);
            
            if ($result['success']) {
                $this->assertArrayHasKey('data', $result);
                $this->assertArrayHasKey('content', $result['data']);
                $this->assertEquals($content, $result['data']['content']);
            }
        }
    }

    /**
     * Test QR generation with different size options
     */
    public function testGenerateQRLocalWithDifferentSizes()
    {
        $content = 'https://example.com';
        $sizes = [100, 200, 300, 400, 500];

        foreach ($sizes as $size) {
            $result = $this->sunuid->generateQRLocal($content, ['size' => $size]);
            $this->assertIsArray($result);
            $this->assertArrayHasKey('success', $result);
            
            if ($result['success']) {
                $this->assertArrayHasKey('data', $result);
                $this->assertArrayHasKey('qr_code', $result['data']);
            }
        }
    }

    /**
     * Test QR generation with different color options
     */
    public function testGenerateQRLocalWithDifferentColors()
    {
        $content = 'https://example.com';
        $colorOptions = [
            [
                'foreground_color' => ['r' => 255, 'g' => 0, 'b' => 0], // Red
                'background_color' => ['r' => 255, 'g' => 255, 'b' => 255] // White
            ],
            [
                'foreground_color' => ['r' => 0, 'g' => 0, 'b' => 255], // Blue
                'background_color' => ['r' => 255, 'g' => 255, 'b' => 255] // White
            ],
            [
                'foreground_color' => ['r' => 0, 'g' => 0, 'b' => 0], // Black
                'background_color' => ['r' => 255, 'g' => 255, 'b' => 0] // Yellow
            ]
        ];

        foreach ($colorOptions as $colors) {
            $result = $this->sunuid->generateQRLocal($content, $colors);
            $this->assertIsArray($result);
            $this->assertArrayHasKey('success', $result);
            
            if ($result['success']) {
                $this->assertArrayHasKey('data', $result);
                $this->assertArrayHasKey('qr_code', $result['data']);
            }
        }
    }
} 
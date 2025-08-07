<?php

namespace SunuID\Tests;

use PHPUnit\Framework\TestCase;
use SunuID\SunuID;

class BasicTest extends TestCase
{
    public function testBasicConstructor()
    {
        $sunuid = new SunuID([
            'client_id' => 'test_client',
            'secret_id' => 'test_secret',
            'partner_name' => 'Test Partner',
            'enable_logs' => false
        ]);
        
        $this->assertInstanceOf(SunuID::class, $sunuid);
    }

    public function testDefaultConfig()
    {
        $sunuid = new SunuID();
        $config = $sunuid->getConfig();
        
        $this->assertEquals('https://api.sunuid.fayma.sn', $config['api_url']);
        $this->assertEquals(2, $config['type']);
        $this->assertEquals('light', $config['theme']);
        $this->assertEquals('fr', $config['language']);
    }

    public function testCustomConfig()
    {
        $customConfig = [
            'client_id' => 'custom_client',
            'secret_id' => 'custom_secret',
            'partner_name' => 'Custom Partner',
            'theme' => 'dark',
            'language' => 'en'
        ];

        $sunuid = new SunuID($customConfig);
        $config = $sunuid->getConfig();
        
        $this->assertEquals('custom_client', $config['client_id']);
        $this->assertEquals('custom_secret', $config['secret_id']);
        $this->assertEquals('Custom Partner', $config['partner_name']);
        $this->assertEquals('dark', $config['theme']);
        $this->assertEquals('en', $config['language']);
    }

    public function testGetTypeName()
    {
        $sunuid = new SunuID();
        
        $this->assertEquals('KYC', $sunuid->getTypeName(1));
        $this->assertEquals('Authentification', $sunuid->getTypeName(2));
        $this->assertEquals('SIGNATURE', $sunuid->getTypeName(3));
        $this->assertEquals('Inconnu', $sunuid->getTypeName(999));
    }

    public function testGenerateSessionCode()
    {
        $sunuid = new SunuID();
        
        $code1 = $sunuid->generateSessionCode();
        $code2 = $sunuid->generateSessionCode();
        
        $this->assertIsString($code1);
        $this->assertIsString($code2);
        $this->assertNotEquals($code1, $code2);
        $this->assertGreaterThan(0, strlen($code1));
    }

    public function testIsInitializedBeforeInit()
    {
        $sunuid = new SunuID();
        $this->assertFalse($sunuid->isInitialized());
    }

    public function testGetPartnerInfoBeforeInit()
    {
        $sunuid = new SunuID();
        $partnerInfo = $sunuid->getPartnerInfo();
        
        $this->assertIsArray($partnerInfo);
        $this->assertEmpty($partnerInfo);
    }

    public function testValidateConfigWithValidCredentials()
    {
        $sunuid = new SunuID([
            'client_id' => 'test_client',
            'secret_id' => 'test_secret',
            'partner_name' => 'Test Partner'
        ]);
        
        // Should not throw exception
        $sunuid->validateConfig();
        $this->assertTrue(true);
    }

    public function testValidateConfigWithMissingCredentials()
    {
        $sunuid = new SunuID([]);
        
        $this->expectException(\InvalidArgumentException::class);
        $sunuid->validateConfig();
    }

    public function testLoggerAccess()
    {
        $sunuid = new SunuID(['enable_logs' => false]);
        $logger = $sunuid->getLogger();
        
        $this->assertInstanceOf(\Monolog\Logger::class, $logger);
    }
} 
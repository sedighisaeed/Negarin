<?php

namespace Tests\Unit;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class RebrandingConfigTest extends TestCase
{
    #[Test]
    public function config_pixelfed_file_uses_negarin_environment_variables()
    {
        $configPath = config_path('pixelfed.php');
        $configContent = file_get_contents($configPath);
        
        // Should use NEGARIN_ environment variables
        $this->assertStringContainsString(
            'NEGARIN_',
            $configContent,
            'Config file should use NEGARIN_ environment variables'
        );
        
        // Should not use old PF_ environment variables
        $this->assertStringNotContainsString(
            'PF_',
            $configContent,
            'Config file should not use PF_ environment variables'
        );
    }

    #[Test]
    public function app_name_configuration_uses_negarin()
    {
        $appName = config('app.name');
        
        $this->assertStringContainsString(
            'Negarin',
            $appName,
            'App name should contain Negarin'
        );
        
        $this->assertStringNotContainsString(
            'Pixelfed',
            $appName,
            'App name should not contain Pixelfed'
        );
    }

    #[Test]
    public function database_configuration_uses_correct_naming()
    {
        $databaseConfig = config('database');
        
        // Check database name if it's brand-specific
        if (isset($databaseConfig['connections']['mysql']['database'])) {
            $dbName = $databaseConfig['connections']['mysql']['database'];
            
            if (strpos($dbName, 'pixelfed') !== false) {
                $this->fail('Database name should not contain pixelfed');
            }
        }
    }

    #[Test]
    public function session_configuration_uses_correct_naming()
    {
        $sessionConfig = config('session');
        
        // Check session cookie name
        if (isset($sessionConfig['cookie'])) {
            $cookieName = $sessionConfig['cookie'];
            
            $this->assertStringNotContainsString(
                'pixelfed',
                strtolower($cookieName),
                'Session cookie name should not contain pixelfed'
            );
        }
    }

    #[Test]
    public function cache_configuration_uses_correct_prefixes()
    {
        $cacheConfig = config('cache');
        
        // Check cache prefixes
        if (isset($cacheConfig['prefix'])) {
            $cachePrefix = $cacheConfig['prefix'];
            
            $this->assertStringNotContainsString(
                'pixelfed',
                strtolower($cachePrefix),
                'Cache prefix should not contain pixelfed'
            );
        }
        
        // Check Redis prefix if using Redis
        if (isset($cacheConfig['stores']['redis']['prefix'])) {
            $redisPrefix = $cacheConfig['stores']['redis']['prefix'];
            
            $this->assertStringNotContainsString(
                'pixelfed',
                strtolower($redisPrefix),
                'Redis cache prefix should not contain pixelfed'
            );
        }
    }

    #[Test]
    public function queue_configuration_uses_correct_naming()
    {
        $queueConfig = config('queue');
        
        // Check queue names and prefixes
        foreach ($queueConfig['connections'] as $connection => $config) {
            if (isset($config['queue'])) {
                $queueName = $config['queue'];
                
                $this->assertStringNotContainsString(
                    'pixelfed',
                    strtolower($queueName),
                    "Queue name for {$connection} should not contain pixelfed"
                );
            }
        }
    }

    #[Test]
    public function logging_configuration_uses_correct_naming()
    {
        $loggingConfig = config('logging');
        
        // Check log channel names
        foreach ($loggingConfig['channels'] as $channel => $config) {
            if (isset($config['path'])) {
                $logPath = $config['path'];
                
                $this->assertStringNotContainsString(
                    'pixelfed',
                    strtolower($logPath),
                    "Log path for {$channel} should not contain pixelfed"
                );
            }
        }
    }

    #[Test]
    public function filesystem_configuration_uses_correct_naming()
    {
        $filesystemConfig = config('filesystems');
        
        // Check disk configurations
        foreach ($filesystemConfig['disks'] as $disk => $config) {
            if (isset($config['root'])) {
                $rootPath = $config['root'];
                
                // Only check if it's a custom path that might contain branding
                if (strpos($rootPath, 'pixelfed') !== false) {
                    $this->fail("Filesystem root path for {$disk} should not contain pixelfed");
                }
            }
        }
    }

    #[Test]
    public function broadcasting_configuration_uses_correct_naming()
    {
        $broadcastingConfig = config('broadcasting');
        
        // Check broadcasting connections
        foreach ($broadcastingConfig['connections'] as $connection => $config) {
            if (isset($config['app_id']) && is_string($config['app_id'])) {
                $appId = $config['app_id'];
                
                $this->assertStringNotContainsString(
                    'pixelfed',
                    strtolower($appId),
                    "Broadcasting app_id for {$connection} should not contain pixelfed"
                );
            }
        }
    }

    #[Test]
    public function services_configuration_uses_correct_naming()
    {
        $servicesConfig = config('services');
        
        // Check service configurations for any brand-specific naming
        foreach ($servicesConfig as $service => $config) {
            if (is_array($config)) {
                foreach ($config as $key => $value) {
                    if (is_string($value) && strpos($value, 'pixelfed') !== false) {
                        $this->fail("Service {$service} configuration {$key} should not contain pixelfed");
                    }
                }
            }
        }
    }
}
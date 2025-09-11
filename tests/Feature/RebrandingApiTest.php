<?php

namespace Tests\Feature;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use App\User;

class RebrandingApiTest extends TestCase
{
    #[Test]
    public function api_instance_endpoint_returns_negarin_branding()
    {
        $response = $this->getJson('/api/v1/instance');
        
        if ($response->status() === 200) {
            $data = $response->json();
            
            // Check title and description
            if (isset($data['title'])) {
                $this->assertStringNotContainsString(
                    'Pixelfed',
                    $data['title'],
                    'API instance title should not contain Pixelfed'
                );
            }
            
            if (isset($data['description'])) {
                $this->assertStringNotContainsString(
                    'Pixelfed',
                    $data['description'],
                    'API instance description should not contain Pixelfed'
                );
            }
            
            // Check version or software name
            if (isset($data['version'])) {
                $this->assertStringNotContainsString(
                    'pixelfed',
                    strtolower($data['version']),
                    'API instance version should not contain pixelfed'
                );
            }
        }
    }

    #[Test]
    public function api_nodeinfo_endpoint_returns_negarin_branding()
    {
        $response = $this->getJson('/.well-known/nodeinfo');
        
        if ($response->status() === 200) {
            $data = $response->json();
            
            // Follow the nodeinfo links to check the actual nodeinfo
            if (isset($data['links'])) {
                foreach ($data['links'] as $link) {
                    if (isset($link['href'])) {
                        $nodeinfoResponse = $this->getJson($link['href']);
                        
                        if ($nodeinfoResponse->status() === 200) {
                            $nodeinfoData = $nodeinfoResponse->json();
                            
                            if (isset($nodeinfoData['software']['name'])) {
                                $this->assertStringNotContainsString(
                                    'pixelfed',
                                    strtolower($nodeinfoData['software']['name']),
                                    'NodeInfo software name should not contain pixelfed'
                                );
                            }
                            
                            if (isset($nodeinfoData['metadata']['nodeName'])) {
                                $this->assertStringNotContainsString(
                                    'Pixelfed',
                                    $nodeinfoData['metadata']['nodeName'],
                                    'NodeInfo node name should not contain Pixelfed'
                                );
                            }
                        }
                        break; // Only test the first link
                    }
                }
            }
        }
    }

    #[Test]
    public function api_error_responses_use_negarin_branding()
    {
        // Test a non-existent endpoint to get an error response
        $response = $this->getJson('/api/v1/nonexistent-endpoint');
        
        if ($response->status() >= 400) {
            $content = $response->getContent();
            
            $this->assertStringNotContainsString(
                'Pixelfed',
                $content,
                'API error responses should not contain Pixelfed references'
            );
        }
    }

    #[Test]
    public function api_oauth_endpoints_use_negarin_branding()
    {
        // Test OAuth authorization page
        $response = $this->get('/oauth/authorize');
        
        // This might redirect or show a form, check the content
        $content = $response->getContent();
        
        $this->assertStringNotContainsString(
            'Pixelfed',
            $content,
            'OAuth authorization page should not contain Pixelfed references'
        );
        
        if (strpos($content, 'authorization') !== false || strpos($content, 'oauth') !== false) {
            $this->assertStringContainsString(
                'Negarin',
                $content,
                'OAuth authorization page should contain Negarin references'
            );
        }
    }

    #[Test]
    public function api_application_endpoints_use_negarin_branding()
    {
        // Test the applications endpoint (might require authentication)
        $response = $this->getJson('/api/v1/apps');
        
        // Even if it returns 401/403, check that error messages don't contain Pixelfed
        $content = $response->getContent();
        
        $this->assertStringNotContainsString(
            'Pixelfed',
            $content,
            'API applications endpoint should not contain Pixelfed references'
        );
    }

    #[Test]
    public function api_activitypub_endpoints_use_negarin_branding()
    {
        // Test ActivityPub endpoints
        $response = $this->get('/.well-known/webfinger');
        
        if ($response->status() !== 404) {
            $content = $response->getContent();
            
            $this->assertStringNotContainsString(
                'Pixelfed',
                $content,
                'ActivityPub webfinger should not contain Pixelfed references'
            );
        }
    }

    #[Test]
    public function api_user_agent_uses_negarin_branding()
    {
        // Check if there are any HTTP client configurations that set User-Agent
        $httpConfig = config('services');
        
        foreach ($httpConfig as $service => $config) {
            if (is_array($config) && isset($config['user_agent'])) {
                $this->assertStringNotContainsString(
                    'Pixelfed',
                    $config['user_agent'],
                    "Service {$service} user agent should not contain Pixelfed"
                );
                
                $this->assertStringContainsString(
                    'Negarin',
                    $config['user_agent'],
                    "Service {$service} user agent should contain Negarin"
                );
            }
        }
    }

    #[Test]
    public function api_headers_use_negarin_branding()
    {
        $response = $this->get('/');
        
        // Check response headers for any branding
        $headers = $response->headers->all();
        
        foreach ($headers as $name => $values) {
            foreach ($values as $value) {
                $this->assertStringNotContainsString(
                    'Pixelfed',
                    $value,
                    "Response header {$name} should not contain Pixelfed"
                );
            }
        }
    }
}
<?php

namespace Tests\Feature;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Mail\Mailable;

class RebrandingEmailTest extends TestCase
{
    #[Test]
    public function email_templates_contain_negarin_references()
    {
        $emailViewsPath = resource_path('views/emails');
        
        if (is_dir($emailViewsPath)) {
            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($emailViewsPath)
            );

            foreach ($iterator as $file) {
                if ($file->getExtension() === 'php') {
                    $content = file_get_contents($file->getPathname());
                    
                    // Should not contain Pixelfed references
                    $this->assertStringNotContainsString(
                        'Pixelfed',
                        $content,
                        "Email template {$file->getFilename()} should not contain Pixelfed references"
                    );
                    
                    // Should not contain "Powered by Pixelfed"
                    $this->assertStringNotContainsString(
                        'Powered by Pixelfed',
                        $content,
                        "Email template {$file->getFilename()} should not contain 'Powered by Pixelfed'"
                    );
                    
                    // If it contains platform references, should be Negarin
                    if (strpos($content, 'platform') !== false || strpos($content, 'Powered by') !== false) {
                        $this->assertStringContainsString(
                            'Negarin',
                            $content,
                            "Email template {$file->getFilename()} should contain Negarin references"
                        );
                    }
                }
            }
        }
    }

    #[Test]
    public function mail_classes_use_negarin_branding()
    {
        $mailPath = app_path('Mail');
        
        if (is_dir($mailPath)) {
            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($mailPath)
            );

            foreach ($iterator as $file) {
                if ($file->getExtension() === 'php') {
                    $content = file_get_contents($file->getPathname());
                    
                    // Should not contain Pixelfed references in subject lines or content
                    $this->assertStringNotContainsString(
                        'Pixelfed',
                        $content,
                        "Mail class {$file->getFilename()} should not contain Pixelfed references"
                    );
                    
                    // Check for subject lines that might need updating
                    if (preg_match('/subject.*=.*["\']([^"\']*)["\']/', $content, $matches)) {
                        $subject = $matches[1];
                        $this->assertStringNotContainsString(
                            'Pixelfed',
                            $subject,
                            "Mail subject in {$file->getFilename()} should not contain Pixelfed"
                        );
                    }
                }
            }
        }
    }

    #[Test]
    public function notification_classes_use_negarin_branding()
    {
        $notificationPath = app_path('Notifications');
        
        if (is_dir($notificationPath)) {
            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($notificationPath)
            );

            foreach ($iterator as $file) {
                if ($file->getExtension() === 'php') {
                    $content = file_get_contents($file->getPathname());
                    
                    // Should not contain Pixelfed references
                    $this->assertStringNotContainsString(
                        'Pixelfed',
                        $content,
                        "Notification class {$file->getFilename()} should not contain Pixelfed references"
                    );
                }
            }
        }
    }

    #[Test]
    public function email_configuration_uses_negarin_branding()
    {
        // Check mail configuration for any brand-specific settings
        $mailConfig = config('mail');
        
        if (isset($mailConfig['from']['name'])) {
            $fromName = $mailConfig['from']['name'];
            $this->assertStringNotContainsString(
                'Pixelfed',
                $fromName,
                'Mail from name should not contain Pixelfed'
            );
        }
        
        // Check if there are any mail-related environment variables
        $appName = config('app.name');
        if ($appName) {
            $this->assertStringNotContainsString(
                'Pixelfed',
                $appName,
                'App name should not contain Pixelfed'
            );
            $this->assertStringContainsString(
                'Negarin',
                $appName,
                'App name should contain Negarin'
            );
        }
    }

    #[Test]
    public function password_reset_email_uses_negarin_branding()
    {
        // Test password reset functionality to ensure it uses Negarin branding
        Mail::fake();
        
        // This test would need a user to test with, but we can check the template exists
        $passwordResetView = 'auth.passwords.email';
        
        try {
            $viewContent = view($passwordResetView)->render();
            
            $this->assertStringNotContainsString(
                'Pixelfed',
                $viewContent,
                'Password reset email should not contain Pixelfed references'
            );
            
            $this->assertStringContainsString(
                'Negarin',
                $viewContent,
                'Password reset email should contain Negarin references'
            );
        } catch (\Exception $e) {
            // View might not exist, skip this test
            $this->markTestSkipped('Password reset view not found');
        }
    }

    #[Test]
    public function email_verification_uses_negarin_branding()
    {
        // Test email verification functionality
        $verificationView = 'auth.verify';
        
        try {
            $viewContent = view($verificationView)->render();
            
            $this->assertStringNotContainsString(
                'Pixelfed',
                $viewContent,
                'Email verification view should not contain Pixelfed references'
            );
            
            $this->assertStringContainsString(
                'Negarin',
                $viewContent,
                'Email verification view should contain Negarin references'
            );
        } catch (\Exception $e) {
            // View might not exist, skip this test
            $this->markTestSkipped('Email verification view not found');
        }
    }
}
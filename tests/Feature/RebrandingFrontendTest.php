<?php

namespace Tests\Feature;


use Tests\TestCase;

class RebrandingFrontendTest extends TestCase
{
    #[Test]
    public function login_page_displays_negarin_branding()
    {
        $response = $this->get('/login');
        
        $response->assertStatus(200);
        $response->assertSee('Negarin');
        $response->assertDontSee('Pixelfed');
    }

    #[Test]
    public function register_page_displays_negarin_branding()
    {
        $response = $this->get('/register');
        
        $response->assertStatus(200);
        $response->assertSee('Negarin');
        $response->assertDontSee('Pixelfed');
    }

    #[Test]
    public function home_page_displays_negarin_branding()
    {
        $response = $this->get('/');
        
        $response->assertStatus(200);
        $response->assertSee('Negarin');
        $response->assertDontSee('Pixelfed');
    }

    #[Test]
    public function about_page_displays_negarin_branding()
    {
        $response = $this->get('/site/about');
        
        // Page might not exist, so check if it's accessible first
        if ($response->status() === 200) {
            $response->assertSee('Negarin');
            $response->assertDontSee('Pixelfed');
        } else {
            $this->markTestSkipped('About page not accessible');
        }
    }

    #[Test]
    public function negarin_logo_files_exist()
    {
        $logoFiles = [
            'public/img/negarin-icon-grey.svg',
            'public/img/negarin-icon-color.svg',
            'public/img/negarin-icon-white.svg',
            'public/img/negarin-icon-color.png',
        ];

        foreach ($logoFiles as $logoFile) {
            $this->assertFileExists(
                base_path($logoFile),
                "Negarin logo file {$logoFile} should exist"
            );
        }
    }

    #[Test]
    public function old_pixelfed_logo_files_do_not_exist()
    {
        $oldLogoFiles = [
            'public/img/pixelfed-icon-grey.svg',
            'public/img/pixelfed-icon-color.svg',
            'public/img/pixelfed-icon-white.svg',
            'public/img/pixelfed-icon-color.png',
        ];

        foreach ($oldLogoFiles as $logoFile) {
            $this->assertFileDoesNotExist(
                base_path($logoFile),
                "Old Pixelfed logo file {$logoFile} should not exist"
            );
        }
    }

    #[Test]
    public function vue_components_reference_negarin_assets()
    {
        $vueComponentsPath = resource_path('assets/js/components');
        
        if (is_dir($vueComponentsPath)) {
            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($vueComponentsPath)
            );

            foreach ($iterator as $file) {
                if ($file->getExtension() === 'vue') {
                    $content = file_get_contents($file->getPathname());
                    
                    // Should not reference old pixelfed icons
                    $this->assertStringNotContainsString(
                        'pixelfed-icon-',
                        $content,
                        "Vue component {$file->getFilename()} should not reference pixelfed-icon assets"
                    );
                    
                    // If it references any icon, it should be negarin
                    if (strpos($content, '-icon-') !== false) {
                        $this->assertStringContainsString(
                            'negarin-icon-',
                            $content,
                            "Vue component {$file->getFilename()} should reference negarin-icon assets"
                        );
                    }
                }
            }
        }
    }

    #[Test]
    public function blade_templates_display_negarin_branding()
    {
        $viewsPath = resource_path('views');
        
        if (is_dir($viewsPath)) {
            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($viewsPath)
            );

            foreach ($iterator as $file) {
                if ($file->getExtension() === 'php') {
                    $content = file_get_contents($file->getPathname());
                    
                    // Should not contain Pixelfed references
                    $this->assertStringNotContainsString(
                        'Pixelfed',
                        $content,
                        "Blade template {$file->getFilename()} should not contain Pixelfed references"
                    );
                    
                    // Should not reference old pixelfed assets
                    $this->assertStringNotContainsString(
                        'pixelfed-icon-',
                        $content,
                        "Blade template {$file->getFilename()} should not reference pixelfed-icon assets"
                    );
                }
            }
        }
    }

    #[Test]
    public function manifest_json_uses_negarin_branding()
    {
        $manifestPath = public_path('manifest.json');
        
        if (file_exists($manifestPath)) {
            $manifestContent = file_get_contents($manifestPath);
            $manifest = json_decode($manifestContent, true);
            
            if ($manifest) {
                // Check name and short_name
                if (isset($manifest['name'])) {
                    $this->assertStringContainsString(
                        'Negarin',
                        $manifest['name'],
                        'Manifest name should contain Negarin'
                    );
                    $this->assertStringNotContainsString(
                        'Pixelfed',
                        $manifest['name'],
                        'Manifest name should not contain Pixelfed'
                    );
                }
                
                if (isset($manifest['short_name'])) {
                    $this->assertStringContainsString(
                        'Negarin',
                        $manifest['short_name'],
                        'Manifest short_name should contain Negarin'
                    );
                    $this->assertStringNotContainsString(
                        'Pixelfed',
                        $manifest['short_name'],
                        'Manifest short_name should not contain Pixelfed'
                    );
                }
            }
        }
    }
}
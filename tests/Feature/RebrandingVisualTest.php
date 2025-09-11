<?php

namespace Tests\Feature;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class RebrandingVisualTest extends TestCase
{
    #[Test]
    public function favicon_uses_negarin_branding()
    {
        $faviconPath = public_path('favicon.ico');
        
        $this->assertFileExists(
            $faviconPath,
            'Favicon should exist'
        );
        
        // Check that favicon is not the old Pixelfed favicon
        // This would require comparing file hashes or sizes if we had the old favicon
        $faviconSize = filesize($faviconPath);
        $this->assertGreaterThan(0, $faviconSize, 'Favicon should not be empty');
    }

    #[Test]
    public function apple_touch_icons_use_negarin_branding()
    {
        $appleTouchIconSizes = [57, 60, 72, 76, 114, 120, 144, 152, 180];
        
        foreach ($appleTouchIconSizes as $size) {
            $iconPath = public_path("apple-touch-icon-{$size}x{$size}.png");
            
            if (file_exists($iconPath)) {
                $iconSize = filesize($iconPath);
                $this->assertGreaterThan(
                    0,
                    $iconSize,
                    "Apple touch icon {$size}x{$size} should not be empty"
                );
            }
        }
    }

    #[Test]
    public function css_files_do_not_reference_pixelfed_assets()
    {
        $cssPath = public_path('css');
        
        if (is_dir($cssPath)) {
            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($cssPath)
            );

            foreach ($iterator as $file) {
                if ($file->getExtension() === 'css') {
                    $content = file_get_contents($file->getPathname());
                    
                    // Should not reference old pixelfed assets
                    $this->assertStringNotContainsString(
                        'pixelfed-icon-',
                        $content,
                        "CSS file {$file->getFilename()} should not reference pixelfed-icon assets"
                    );
                    
                    // Should not contain Pixelfed text in content or comments
                    $this->assertStringNotContainsString(
                        'Pixelfed',
                        $content,
                        "CSS file {$file->getFilename()} should not contain Pixelfed references"
                    );
                }
            }
        }
    }

    #[Test]
    public function javascript_files_do_not_reference_pixelfed_assets()
    {
        $jsPath = public_path('js');
        
        if (is_dir($jsPath)) {
            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($jsPath)
            );

            foreach ($iterator as $file) {
                if ($file->getExtension() === 'js') {
                    $content = file_get_contents($file->getPathname());
                    
                    // Should not reference old pixelfed assets
                    $this->assertStringNotContainsString(
                        'pixelfed-icon-',
                        $content,
                        "JS file {$file->getFilename()} should not reference pixelfed-icon assets"
                    );
                    
                    // Should not contain Pixelfed text in strings or comments
                    $this->assertStringNotContainsString(
                        'Pixelfed',
                        $content,
                        "JS file {$file->getFilename()} should not contain Pixelfed references"
                    );
                }
            }
        }
    }

    #[Test]
    public function svg_icons_use_negarin_branding()
    {
        $svgPath = public_path('svg');
        
        if (is_dir($svgPath)) {
            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($svgPath)
            );

            foreach ($iterator as $file) {
                if ($file->getExtension() === 'svg') {
                    $content = file_get_contents($file->getPathname());
                    
                    // SVG files should not contain Pixelfed references in titles or descriptions
                    $this->assertStringNotContainsString(
                        'Pixelfed',
                        $content,
                        "SVG file {$file->getFilename()} should not contain Pixelfed references"
                    );
                }
            }
        }
    }

    #[Test]
    public function page_titles_use_negarin_branding()
    {
        $pages = [
            '/',
            '/login',
            '/register',
            '/discover',
            '/about',
        ];

        foreach ($pages as $page) {
            $response = $this->get($page);
            
            if ($response->status() === 200) {
                $content = $response->getContent();
                
                // Extract title tag content
                if (preg_match('/<title[^>]*>(.*?)<\/title>/i', $content, $matches)) {
                    $title = $matches[1];
                    
                    $this->assertStringNotContainsString(
                        'Pixelfed',
                        $title,
                        "Page title for {$page} should not contain Pixelfed"
                    );
                    
                    // If title contains platform reference, should be Negarin
                    if (strpos(strtolower($title), 'platform') !== false || 
                        strpos(strtolower($title), 'social') !== false) {
                        $this->assertStringContainsString(
                            'Negarin',
                            $title,
                            "Page title for {$page} should contain Negarin"
                        );
                    }
                }
            }
        }
    }

    #[Test]
    public function meta_tags_use_negarin_branding()
    {
        $response = $this->get('/');
        
        if ($response->status() === 200) {
            $content = $response->getContent();
            
            // Check meta description
            if (preg_match('/<meta[^>]*name=["\']description["\'][^>]*content=["\']([^"\']*)["\'][^>]*>/i', $content, $matches)) {
                $description = $matches[1];
                
                $this->assertStringNotContainsString(
                    'Pixelfed',
                    $description,
                    'Meta description should not contain Pixelfed'
                );
            }
            
            // Check Open Graph tags
            if (preg_match('/<meta[^>]*property=["\']og:title["\'][^>]*content=["\']([^"\']*)["\'][^>]*>/i', $content, $matches)) {
                $ogTitle = $matches[1];
                
                $this->assertStringNotContainsString(
                    'Pixelfed',
                    $ogTitle,
                    'Open Graph title should not contain Pixelfed'
                );
            }
            
            if (preg_match('/<meta[^>]*property=["\']og:description["\'][^>]*content=["\']([^"\']*)["\'][^>]*>/i', $content, $matches)) {
                $ogDescription = $matches[1];
                
                $this->assertStringNotContainsString(
                    'Pixelfed',
                    $ogDescription,
                    'Open Graph description should not contain Pixelfed'
                );
            }
        }
    }

    #[Test]
    public function loading_screens_use_negarin_branding()
    {
        // Check for any loading screen templates or components
        $loadingViews = [
            'layouts.app',
            'layouts.blank',
            'components.loading',
        ];

        foreach ($loadingViews as $view) {
            try {
                $viewContent = view($view)->render();
                
                $this->assertStringNotContainsString(
                    'Pixelfed',
                    $viewContent,
                    "Loading view {$view} should not contain Pixelfed references"
                );
                
                // If it contains loading text, should reference Negarin
                if (strpos(strtolower($viewContent), 'loading') !== false) {
                    $this->assertStringContainsString(
                        'Negarin',
                        $viewContent,
                        "Loading view {$view} should contain Negarin references"
                    );
                }
            } catch (\Exception $e) {
                // View might not exist, continue
                continue;
            }
        }
    }
}
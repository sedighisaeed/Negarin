<?php

namespace Tests\Feature;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class RebrandingComplianceTest extends TestCase
{
    #[Test]
    public function composer_json_uses_negarin_branding()
    {
        $composerPath = base_path('composer.json');
        $composerContent = file_get_contents($composerPath);
        $composer = json_decode($composerContent, true);
        
        // Check package name
        if (isset($composer['name'])) {
            $this->assertStringNotContainsString(
                'pixelfed',
                strtolower($composer['name']),
                'Composer package name should not contain pixelfed'
            );
        }
        
        // Check description
        if (isset($composer['description'])) {
            $this->assertStringNotContainsString(
                'Pixelfed',
                $composer['description'],
                'Composer description should not contain Pixelfed'
            );
            
            $this->assertStringContainsString(
                'Negarin',
                $composer['description'],
                'Composer description should contain Negarin'
            );
        }
        
        // Check keywords
        if (isset($composer['keywords'])) {
            foreach ($composer['keywords'] as $keyword) {
                $this->assertStringNotContainsString(
                    'pixelfed',
                    strtolower($keyword),
                    'Composer keywords should not contain pixelfed'
                );
            }
        }
    }

    #[Test]
    public function package_json_uses_negarin_branding()
    {
        $packagePath = base_path('package.json');
        
        if (file_exists($packagePath)) {
            $packageContent = file_get_contents($packagePath);
            $package = json_decode($packageContent, true);
            
            // Check package name
            if (isset($package['name'])) {
                $this->assertStringNotContainsString(
                    'pixelfed',
                    strtolower($package['name']),
                    'Package.json name should not contain pixelfed'
                );
            }
            
            // Check description
            if (isset($package['description'])) {
                $this->assertStringNotContainsString(
                    'Pixelfed',
                    $package['description'],
                    'Package.json description should not contain Pixelfed'
                );
            }
        }
    }

    #[Test]
    public function readme_file_uses_negarin_branding()
    {
        $readmePath = base_path('README.md');
        
        if (file_exists($readmePath)) {
            $readmeContent = file_get_contents($readmePath);
            
            // Should not contain Pixelfed references
            $this->assertStringNotContainsString(
                'Pixelfed',
                $readmeContent,
                'README should not contain Pixelfed references'
            );
            
            // Should contain Negarin references
            $this->assertStringContainsString(
                'Negarin',
                $readmeContent,
                'README should contain Negarin references'
            );
        }
    }

    #[Test]
    public function documentation_files_use_negarin_branding()
    {
        $docFiles = [
            'CONTRIBUTING.md',
            'CODE_OF_CONDUCT.md',
            'SECURITY.md',
            'CHANGELOG.md',
        ];

        foreach ($docFiles as $docFile) {
            $docPath = base_path($docFile);
            
            if (file_exists($docPath)) {
                $docContent = file_get_contents($docPath);
                
                $this->assertStringNotContainsString(
                    'Pixelfed',
                    $docContent,
                    "{$docFile} should not contain Pixelfed references"
                );
                
                // If it mentions the platform, should be Negarin
                if (strpos($docContent, 'platform') !== false || 
                    strpos($docContent, 'project') !== false) {
                    $this->assertStringContainsString(
                        'Negarin',
                        $docContent,
                        "{$docFile} should contain Negarin references"
                    );
                }
            }
        }
    }

    #[Test]
    public function docker_files_use_negarin_branding()
    {
        $dockerFiles = [
            'Dockerfile',
            'docker-compose.yml',
            'docker-compose-simple.yml',
            'docker-compose.migrate.yml',
        ];

        foreach ($dockerFiles as $dockerFile) {
            $dockerPath = base_path($dockerFile);
            
            if (file_exists($dockerPath)) {
                $dockerContent = file_get_contents($dockerPath);
                
                // Should not contain Pixelfed references in labels or comments
                $this->assertStringNotContainsString(
                    'Pixelfed',
                    $dockerContent,
                    "{$dockerFile} should not contain Pixelfed references"
                );
            }
        }
    }

    #[Test]
    public function artisan_commands_use_negarin_branding()
    {
        $commandsPath = app_path('Console/Commands');
        
        if (is_dir($commandsPath)) {
            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($commandsPath)
            );

            foreach ($iterator as $file) {
                if ($file->getExtension() === 'php') {
                    $content = file_get_contents($file->getPathname());
                    
                    // Should not contain Pixelfed references in command descriptions or help
                    $this->assertStringNotContainsString(
                        'Pixelfed',
                        $content,
                        "Command {$file->getFilename()} should not contain Pixelfed references"
                    );
                }
            }
        }
    }

    #[Test]
    public function migration_files_use_appropriate_naming()
    {
        $migrationsPath = database_path('migrations');
        
        if (is_dir($migrationsPath)) {
            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($migrationsPath)
            );

            foreach ($iterator as $file) {
                if ($file->getExtension() === 'php') {
                    $content = file_get_contents($file->getPathname());
                    
                    // Migration files should not contain Pixelfed references in comments
                    // (table names and column names might be legacy and that's OK)
                    if (preg_match('/\/\*.*?\*\/|\/\/.*$/m', $content, $matches)) {
                        foreach ($matches as $comment) {
                            $this->assertStringNotContainsString(
                                'Pixelfed',
                                $comment,
                                "Migration {$file->getFilename()} comments should not contain Pixelfed references"
                            );
                        }
                    }
                }
            }
        }
    }

    #[Test]
    public function seed_files_use_negarin_branding()
    {
        $seedsPath = database_path('seeds');
        
        if (is_dir($seedsPath)) {
            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($seedsPath)
            );

            foreach ($iterator as $file) {
                if ($file->getExtension() === 'php') {
                    $content = file_get_contents($file->getPathname());
                    
                    // Seed files should not contain Pixelfed references in default data
                    $this->assertStringNotContainsString(
                        'Pixelfed',
                        $content,
                        "Seed {$file->getFilename()} should not contain Pixelfed references"
                    );
                }
            }
        }
    }

    #[Test]
    public function route_files_use_appropriate_naming()
    {
        $routesPath = base_path('routes');
        
        if (is_dir($routesPath)) {
            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($routesPath)
            );

            foreach ($iterator as $file) {
                if ($file->getExtension() === 'php') {
                    $content = file_get_contents($file->getPathname());
                    
                    // Route files should not contain Pixelfed references in comments
                    $this->assertStringNotContainsString(
                        'Pixelfed',
                        $content,
                        "Route file {$file->getFilename()} should not contain Pixelfed references"
                    );
                }
            }
        }
    }

    #[Test]
    public function no_pixelfed_references_in_source_code()
    {
        // This is a comprehensive test to catch any remaining Pixelfed references
        $sourcePaths = [
            app_path(),
            resource_path('assets'),
            config_path(),
        ];

        foreach ($sourcePaths as $sourcePath) {
            if (is_dir($sourcePath)) {
                $iterator = new \RecursiveIteratorIterator(
                    new \RecursiveDirectoryIterator($sourcePath)
                );

                foreach ($iterator as $file) {
                    if (in_array($file->getExtension(), ['php', 'js', 'vue', 'scss', 'css'])) {
                        $content = file_get_contents($file->getPathname());
                        
                        // Count Pixelfed references (some might be acceptable in comments about migration)
                        $pixelfedCount = substr_count($content, 'Pixelfed');
                        $pixelfedLowerCount = substr_count($content, 'pixelfed');
                        
                        // If there are many references, it's likely not properly rebranded
                        if ($pixelfedCount > 2 || $pixelfedLowerCount > 2) {
                            $this->fail(
                                "File {$file->getPathname()} contains too many Pixelfed references ({$pixelfedCount} 'Pixelfed', {$pixelfedLowerCount} 'pixelfed'). Please review for proper rebranding."
                            );
                        }
                    }
                }
            }
        }
    }
}
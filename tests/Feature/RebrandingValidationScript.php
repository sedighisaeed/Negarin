<?php

/**
 * Rebranding Validation Script
 * 
 * This script validates that the Pixelfed to Negarin rebranding has been completed successfully.
 * It can be run independently of the test suite to verify rebranding compliance.
 */

class RebrandingValidator
{
    private $errors = [];
    private $warnings = [];
    private $passed = [];

    public function validate()
    {
        echo "Starting Negarin Rebranding Validation...\n\n";

        $this->validateEnvironmentVariables();
        $this->validateConfigurationFiles();
        $this->validateAssetFiles();
        $this->validateSourceCode();
        $this->validateDocumentation();
        $this->validatePackageFiles();

        $this->printResults();
    }

    private function validateEnvironmentVariables()
    {
        echo "Validating Environment Variables...\n";

        // Check .env.example file
        $envExamplePath = __DIR__ . '/../../.env.example';
        if (file_exists($envExamplePath)) {
            $content = file_get_contents($envExamplePath);
            
            if (strpos($content, 'PF_') !== false) {
                $this->errors[] = '.env.example still contains PF_ prefixed variables';
            } else {
                $this->passed[] = '.env.example does not contain old PF_ variables';
            }

            if (strpos($content, 'NEGARIN_') !== false) {
                $this->passed[] = '.env.example contains NEGARIN_ prefixed variables';
            } else {
                $this->warnings[] = '.env.example does not contain NEGARIN_ variables';
            }
        }

        // Check config/pixelfed.php
        $configPath = __DIR__ . '/../../config/pixelfed.php';
        if (file_exists($configPath)) {
            $content = file_get_contents($configPath);
            
            if (strpos($content, 'NEGARIN_') !== false) {
                $this->passed[] = 'config/pixelfed.php uses NEGARIN_ environment variables';
            } else {
                $this->errors[] = 'config/pixelfed.php does not use NEGARIN_ environment variables';
            }

            if (strpos($content, 'PF_') !== false) {
                $this->errors[] = 'config/pixelfed.php still contains PF_ environment variables';
            } else {
                $this->passed[] = 'config/pixelfed.php does not contain old PF_ variables';
            }
        }
    }

    private function validateConfigurationFiles()
    {
        echo "Validating Configuration Files...\n";

        $configFiles = [
            'config/app.php',
            'config/pixelfed.php',
            'config/services.php',
        ];

        foreach ($configFiles as $configFile) {
            $fullPath = __DIR__ . '/../../' . $configFile;
            if (file_exists($fullPath)) {
                $content = file_get_contents($fullPath);
                
                if (strpos($content, 'Pixelfed') !== false) {
                    $this->errors[] = "{$configFile} contains Pixelfed references";
                } else {
                    $this->passed[] = "{$configFile} does not contain Pixelfed references";
                }
            }
        }
    }

    private function validateAssetFiles()
    {
        echo "Validating Asset Files...\n";

        // Check for Negarin logo files
        $negarinLogos = [
            'public/img/negarin-icon-grey.svg',
            'public/img/negarin-icon-color.svg',
            'public/img/negarin-icon-white.svg',
            'public/img/negarin-icon-color.png',
        ];

        foreach ($negarinLogos as $logo) {
            $fullPath = __DIR__ . '/../../' . $logo;
            if (file_exists($fullPath)) {
                $this->passed[] = "Negarin logo exists: {$logo}";
            } else {
                $this->errors[] = "Missing Negarin logo: {$logo}";
            }
        }

        // Check for old Pixelfed logo files
        $pixelfedLogos = [
            'public/img/pixelfed-icon-grey.svg',
            'public/img/pixelfed-icon-color.svg',
            'public/img/pixelfed-icon-white.svg',
            'public/img/pixelfed-icon-color.png',
        ];

        foreach ($pixelfedLogos as $logo) {
            $fullPath = __DIR__ . '/../../' . $logo;
            if (file_exists($fullPath)) {
                $this->errors[] = "Old Pixelfed logo still exists: {$logo}";
            } else {
                $this->passed[] = "Old Pixelfed logo removed: {$logo}";
            }
        }
    }

    private function validateSourceCode()
    {
        echo "Validating Source Code...\n";

        // Check Vue.js components
        $vueComponentsPath = __DIR__ . '/../../resources/assets/js/components';
        if (is_dir($vueComponentsPath)) {
            $this->checkDirectoryForPixelfedReferences($vueComponentsPath, '*.vue', 'Vue components');
        }

        // Check Blade templates
        $viewsPath = __DIR__ . '/../../resources/views';
        if (is_dir($viewsPath)) {
            $this->checkDirectoryForPixelfedReferences($viewsPath, '*.php', 'Blade templates');
        }

        // Check email templates
        $emailViewsPath = __DIR__ . '/../../resources/views/emails';
        if (is_dir($emailViewsPath)) {
            $this->checkDirectoryForPixelfedReferences($emailViewsPath, '*.php', 'Email templates');
        }
    }

    private function validateDocumentation()
    {
        echo "Validating Documentation...\n";

        $docFiles = [
            'README.md',
            'CONTRIBUTING.md',
            'CODE_OF_CONDUCT.md',
            'SECURITY.md',
        ];

        foreach ($docFiles as $docFile) {
            $fullPath = __DIR__ . '/../../' . $docFile;
            if (file_exists($fullPath)) {
                $content = file_get_contents($fullPath);
                
                if (strpos($content, 'Pixelfed') !== false) {
                    $this->errors[] = "{$docFile} contains Pixelfed references";
                } else {
                    $this->passed[] = "{$docFile} does not contain Pixelfed references";
                }

                if (strpos($content, 'Negarin') !== false) {
                    $this->passed[] = "{$docFile} contains Negarin references";
                } else {
                    $this->warnings[] = "{$docFile} does not contain Negarin references";
                }
            }
        }
    }

    private function validatePackageFiles()
    {
        echo "Validating Package Files...\n";

        // Check composer.json
        $composerPath = __DIR__ . '/../../composer.json';
        if (file_exists($composerPath)) {
            $content = file_get_contents($composerPath);
            $composer = json_decode($content, true);

            if (isset($composer['name']) && strpos($composer['name'], 'pixelfed') !== false) {
                $this->errors[] = 'composer.json package name contains pixelfed';
            } else {
                $this->passed[] = 'composer.json package name does not contain pixelfed';
            }

            if (isset($composer['description']) && strpos($composer['description'], 'Pixelfed') !== false) {
                $this->errors[] = 'composer.json description contains Pixelfed';
            } else {
                $this->passed[] = 'composer.json description does not contain Pixelfed';
            }
        }

        // Check package.json
        $packagePath = __DIR__ . '/../../package.json';
        if (file_exists($packagePath)) {
            $content = file_get_contents($packagePath);
            
            if (strpos($content, 'pixelfed') !== false) {
                $this->errors[] = 'package.json contains pixelfed references';
            } else {
                $this->passed[] = 'package.json does not contain pixelfed references';
            }
        }
    }

    private function checkDirectoryForPixelfedReferences($directory, $pattern, $description)
    {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($directory)
        );

        $pixelfedCount = 0;
        $totalFiles = 0;

        foreach ($iterator as $file) {
            if ($file->isFile()) {
                $extension = $file->getExtension();
                if (in_array($extension, ['php', 'vue', 'js', 'css', 'scss'])) {
                    $totalFiles++;
                    $content = file_get_contents($file->getPathname());
                    
                    if (strpos($content, 'Pixelfed') !== false || strpos($content, 'pixelfed') !== false) {
                        $pixelfedCount++;
                    }
                }
            }
        }

        if ($pixelfedCount > 0) {
            $this->errors[] = "{$description}: {$pixelfedCount} out of {$totalFiles} files contain Pixelfed references";
        } else {
            $this->passed[] = "{$description}: No Pixelfed references found in {$totalFiles} files";
        }
    }

    private function printResults()
    {
        echo "\n" . str_repeat("=", 60) . "\n";
        echo "REBRANDING VALIDATION RESULTS\n";
        echo str_repeat("=", 60) . "\n\n";

        if (!empty($this->passed)) {
            echo "✅ PASSED (" . count($this->passed) . "):\n";
            foreach ($this->passed as $pass) {
                echo "  ✓ {$pass}\n";
            }
            echo "\n";
        }

        if (!empty($this->warnings)) {
            echo "⚠️  WARNINGS (" . count($this->warnings) . "):\n";
            foreach ($this->warnings as $warning) {
                echo "  ⚠ {$warning}\n";
            }
            echo "\n";
        }

        if (!empty($this->errors)) {
            echo "❌ ERRORS (" . count($this->errors) . "):\n";
            foreach ($this->errors as $error) {
                echo "  ✗ {$error}\n";
            }
            echo "\n";
        }

        $total = count($this->passed) + count($this->warnings) + count($this->errors);
        $successRate = $total > 0 ? round((count($this->passed) / $total) * 100, 1) : 0;

        echo "SUMMARY:\n";
        echo "  Total Checks: {$total}\n";
        echo "  Passed: " . count($this->passed) . "\n";
        echo "  Warnings: " . count($this->warnings) . "\n";
        echo "  Errors: " . count($this->errors) . "\n";
        echo "  Success Rate: {$successRate}%\n\n";

        if (empty($this->errors)) {
            echo "🎉 Rebranding validation completed successfully!\n";
            return true;
        } else {
            echo "❌ Rebranding validation failed. Please address the errors above.\n";
            return false;
        }
    }
}

// Run the validation if this script is executed directly
if (basename(__FILE__) === basename($_SERVER['SCRIPT_NAME'])) {
    $validator = new RebrandingValidator();
    $success = $validator->validate();
    exit($success ? 0 : 1);
}
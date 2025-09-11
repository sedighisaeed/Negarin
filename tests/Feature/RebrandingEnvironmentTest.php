<?php

namespace Tests\Feature;

use Tests\TestCase;

class RebrandingEnvironmentTest extends TestCase
{
    /** @test */
    public function environment_variables_use_negarin_prefix()
    {
        // Test that all required NEGARIN_ environment variables are properly configured
        $negarinEnvVars = [
            'NEGARIN_MAX_USERS',
            'NEGARIN_OPTIMIZE_IMAGES',
            'NEGARIN_OPTIMIZE_VIDEOS',
            'NEGARIN_USER_INVITES',
            'NEGARIN_USER_INVITES_TOTAL_LIMIT',
            'NEGARIN_USER_INVITES_DAILY_LIMIT',
            'NEGARIN_USER_INVITES_MONTHLY_LIMIT',
            'NEGARIN_MAX_COLLECTION_LENGTH',
            'NEGARIN_BOUNCER_ENABLED',
            'NEGARIN_BOUNCER_BAN_CLOUD_LOGINS',
            'NEGARIN_BOUNCER_BAN_CLOUD_SIGNUPS',
            'NEGARIN_BOUNCER_BAN_CLOUD_API',
            'NEGARIN_BOUNCER_BAN_CLOUD_API_STRICT_MODE',
            'NEGARIN_MEDIA_FAST_PROCESS',
            'NEGARIN_MEDIA_MAX_ALTTEXT_LENGTH',
            'NEGARIN_ALLOW_APP_REGISTRATION',
            'NEGARIN_IAR_RL_ATTEMPTS',
            'NEGARIN_IAR_RL_DECAY',
            'NEGARIN_IARC_RL_ATTEMPTS',
            'NEGARIN_ENABLE_CLOUD',
        ];

        foreach ($negarinEnvVars as $envVar) {
            // Check that the environment variable exists in config
            $configKey = strtolower(str_replace('NEGARIN_', '', $envVar));
            $configValue = config("pixelfed.{$configKey}");
            
            // The config should be accessible (not null unless intentionally set to null)
            $this->assertTrue(
                $configValue !== null || env($envVar) !== null,
                "Environment variable {$envVar} should be accessible through config or env"
            );
        }
    }

    /** @test */
    public function no_pixelfed_environment_variables_remain()
    {
        // Test that old PF_ environment variables are not being used
        $oldPixelfedEnvVars = [
            'PF_MAX_USERS',
            'PF_OPTIMIZE_IMAGES',
            'PF_OPTIMIZE_VIDEOS',
            'PF_USER_INVITES',
            'PF_USER_INVITES_TOTAL_LIMIT',
            'PF_USER_INVITES_DAILY_LIMIT',
            'PF_USER_INVITES_MONTHLY_LIMIT',
            'PF_MAX_COLLECTION_LENGTH',
            'PF_BOUNCER_ENABLED',
            'PF_BOUNCER_BAN_CLOUD_LOGINS',
            'PF_BOUNCER_BAN_CLOUD_SIGNUPS',
            'PF_BOUNCER_BAN_CLOUD_API',
            'PF_BOUNCER_BAN_CLOUD_API_STRICT_MODE',
            'PF_MEDIA_FAST_PROCESS',
            'PF_MEDIA_MAX_ALTTEXT_LENGTH',
            'PF_ALLOW_APP_REGISTRATION',
            'PF_IAR_RL_ATTEMPTS',
            'PF_IAR_RL_DECAY',
            'PF_IARC_RL_ATTEMPTS',
            'PF_ENABLE_CLOUD',
        ];

        foreach ($oldPixelfedEnvVars as $envVar) {
            // These should not be set in the environment
            $this->assertNull(
                env($envVar),
                "Old Pixelfed environment variable {$envVar} should not be set"
            );
        }
    }

    /** @test */
    public function config_files_use_negarin_references()
    {
        // Test that configuration files reference Negarin instead of Pixelfed
        $configContent = file_get_contents(config_path('pixelfed.php'));
        
        // Should not contain old Pixelfed references in comments or strings
        $this->assertStringNotContainsString(
            'Pixelfed',
            $configContent,
            'Config file should not contain Pixelfed references'
        );
        
        // Should contain Negarin references
        $this->assertStringContainsString(
            'Negarin',
            $configContent,
            'Config file should contain Negarin references'
        );
    }

    /** @test */
    public function env_example_file_uses_negarin_prefix()
    {
        $envExamplePath = base_path('.env.example');
        
        if (file_exists($envExamplePath)) {
            $envExampleContent = file_get_contents($envExamplePath);
            
            // Should not contain PF_ prefixed variables
            $this->assertStringNotContainsString(
                'PF_',
                $envExampleContent,
                '.env.example should not contain PF_ prefixed variables'
            );
            
            // Should contain NEGARIN_ prefixed variables
            $this->assertStringContainsString(
                'NEGARIN_',
                $envExampleContent,
                '.env.example should contain NEGARIN_ prefixed variables'
            );
        }
    }
}
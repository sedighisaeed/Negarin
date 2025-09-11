# Design Document

## Overview

This design outlines the systematic approach to rebrand the entire application from "Pixelfed" to "Negarin". Based on the codebase analysis, the rebranding effort involves updating configuration files, environment variables, frontend assets, user interface text, and removing all references to Pixelfed branding throughout the application.

The rebranding will maintain all existing functionality while establishing Negarin as a distinct platform identity. The approach prioritizes systematic replacement of brand references while ensuring no functionality is broken during the transition.

## Architecture

### Rebranding Scope Analysis

Based on the codebase research, the following areas require rebranding:

1. **Configuration Files**: The `config/pixelfed.php` file contains Negarin references but still uses PF_ environment variable prefixes
2. **Environment Variables**: Multiple PF_ prefixed variables in `.env.example` need to be updated to NEGARIN_ prefix
3. **Frontend Assets**: Extensive Pixelfed references in Vue.js components, including:
   - Logo image references (`/img/pixelfed-icon-*.svg`)
   - "Powered by Pixelfed" text in multiple components
   - Placeholder URLs and example text
4. **Package Configuration**: Dependencies still reference pixelfed packages that may need updating
5. **User Interface Text**: Various user-facing messages and labels

### Rebranding Strategy

The rebranding will follow a layered approach:

1. **Backend Configuration Layer**: Update environment variables, configuration files, and server-side references
2. **Frontend Asset Layer**: Replace logos, icons, and visual branding elements
3. **User Interface Layer**: Update all user-facing text and messages
4. **Package and Dependency Layer**: Update package references where possible

## Components and Interfaces

### Configuration Management Component

**Purpose**: Handle the systematic update of configuration files and environment variables

**Key Files**:
- `config/pixelfed.php` - Main configuration file (already partially updated)
- `.env.example` - Environment variable template
- Various config files that may reference PF_ variables

**Interface**:
- Environment variable mapping: `PF_*` → `NEGARIN_*`
- Configuration key updates where brand-specific
- Maintain backward compatibility during transition

### Frontend Asset Management Component

**Purpose**: Replace all visual branding elements and logo references

**Key Assets**:
- Logo files: `pixelfed-icon-*.svg` → `negarin-icon-*.svg`
- Favicon and manifest icons
- Any brand-specific imagery

**Interface**:
- Asset file renaming and replacement
- Update all image source references in components
- Maintain consistent visual identity

### User Interface Text Component

**Purpose**: Update all user-facing text to reflect Negarin branding

**Key Areas**:
- Vue.js components with hardcoded "Pixelfed" references
- Blade templates with brand mentions
- Email templates and notifications
- API response messages
- Error messages and help text

**Interface**:
- Text replacement in template files
- Component prop updates for dynamic branding
- Localization file updates if applicable

### Package and Dependency Component

**Purpose**: Update package references and dependencies where feasible

**Key Areas**:
- Composer package names and descriptions
- Internal package references
- Documentation and README files

**Interface**:
- Package metadata updates
- Dependency name changes where controlled by project
- Documentation consistency

## Data Models

### Environment Variable Mapping

The following environment variables need to be updated:

```
PF_MAX_USERS → NEGARIN_MAX_USERS
PF_OPTIMIZE_IMAGES → NEGARIN_OPTIMIZE_IMAGES
PF_OPTIMIZE_VIDEOS → NEGARIN_OPTIMIZE_VIDEOS
PF_USER_INVITES → NEGARIN_USER_INVITES
PF_USER_INVITES_TOTAL_LIMIT → NEGARIN_USER_INVITES_TOTAL_LIMIT
PF_USER_INVITES_DAILY_LIMIT → NEGARIN_USER_INVITES_DAILY_LIMIT
PF_USER_INVITES_MONTHLY_LIMIT → NEGARIN_USER_INVITES_MONTHLY_LIMIT
PF_MAX_COLLECTION_LENGTH → NEGARIN_MAX_COLLECTION_LENGTH
PF_BOUNCER_ENABLED → NEGARIN_BOUNCER_ENABLED
PF_BOUNCER_BAN_CLOUD_LOGINS → NEGARIN_BOUNCER_BAN_CLOUD_LOGINS
PF_BOUNCER_BAN_CLOUD_SIGNUPS → NEGARIN_BOUNCER_BAN_CLOUD_SIGNUPS
PF_BOUNCER_BAN_CLOUD_API → NEGARIN_BOUNCER_BAN_CLOUD_API
PF_BOUNCER_BAN_CLOUD_API_STRICT_MODE → NEGARIN_BOUNCER_BAN_CLOUD_API_STRICT_MODE
PF_MEDIA_FAST_PROCESS → NEGARIN_MEDIA_FAST_PROCESS
PF_MEDIA_MAX_ALTTEXT_LENGTH → NEGARIN_MEDIA_MAX_ALTTEXT_LENGTH
PF_ALLOW_APP_REGISTRATION → NEGARIN_ALLOW_APP_REGISTRATION
PF_IAR_RL_ATTEMPTS → NEGARIN_IAR_RL_ATTEMPTS
PF_IAR_RL_DECAY → NEGARIN_IAR_RL_DECAY
PF_IARC_RL_ATTEMPTS → NEGARIN_IARC_RL_ATTEMPTS
PF_ENABLE_CLOUD → NEGARIN_ENABLE_CLOUD
```

### Asset File Mapping

Logo and icon files that need to be renamed or replaced:

```
/img/pixelfed-icon-grey.svg → /img/negarin-icon-grey.svg
/img/pixelfed-icon-color.svg → /img/negarin-icon-color.svg
/img/pixelfed-icon-white.svg → /img/negarin-icon-white.svg
/img/pixelfed-icon-color.png → /img/negarin-icon-color.png
```

### Text Replacement Patterns

Common text patterns that need replacement:

```
"Pixelfed" → "Negarin"
"pixelfed" → "negarin"
"PIXELFED" → "NEGARIN"
"Powered by Pixelfed" → "Powered by Negarin"
"pixelfed.org" → "negarin.org" (or appropriate domain)
"pixelfed.dev" → "negarin.dev" (in examples)
```

## Error Handling

### Backward Compatibility

During the transition period, the system should:

1. **Environment Variables**: Support both old PF_ and new NEGARIN_ prefixes with deprecation warnings
2. **Configuration**: Gracefully handle missing new configuration keys by falling back to defaults
3. **Assets**: Provide fallback mechanisms if new asset files are missing

### Validation

1. **Environment Variable Validation**: Ensure all required NEGARIN_ variables are properly set
2. **Asset Validation**: Verify all new logo and icon files exist and are accessible
3. **Configuration Validation**: Check that all configuration updates maintain valid values

### Error Recovery

1. **Missing Assets**: Log warnings for missing Negarin assets and provide generic fallbacks
2. **Configuration Errors**: Provide clear error messages for misconfigured branding settings
3. **Template Errors**: Handle missing or malformed template updates gracefully

## Testing Strategy

### Unit Testing

1. **Configuration Tests**: Verify environment variable mapping works correctly
2. **Asset Loading Tests**: Ensure new asset files load properly
3. **Text Replacement Tests**: Validate that all text replacements are applied correctly

### Integration Testing

1. **Frontend Component Tests**: Test that all Vue.js components render with Negarin branding
2. **Email Template Tests**: Verify email notifications display Negarin branding
3. **API Response Tests**: Ensure API responses contain correct branding information

### Visual Regression Testing

1. **Logo Display Tests**: Verify logos appear correctly across all pages
2. **Branding Consistency Tests**: Check that all user-facing text is consistent
3. **Mobile Responsiveness Tests**: Ensure branding works on mobile devices

### Manual Testing Checklist

1. **User Registration Flow**: Verify all registration pages show Negarin branding
2. **Email Notifications**: Check that all email types contain Negarin references
3. **Admin Panel**: Ensure admin interface reflects Negarin branding
4. **API Documentation**: Verify API docs reference Negarin instead of Pixelfed
5. **Error Pages**: Check that error messages mention Negarin appropriately

### Performance Testing

1. **Asset Loading Performance**: Ensure new assets don't impact page load times
2. **Configuration Loading**: Verify environment variable changes don't affect startup time
3. **Template Rendering**: Check that text replacements don't slow down page rendering

## Implementation Considerations

### Deployment Strategy

1. **Staged Rollout**: Implement changes in logical groups to minimize risk
2. **Rollback Plan**: Maintain ability to revert changes if issues arise
3. **Documentation Updates**: Update all documentation simultaneously with code changes

### Maintenance

1. **Future Updates**: Establish processes to prevent Pixelfed references from being reintroduced
2. **Dependency Management**: Monitor upstream packages for Pixelfed references
3. **Community Contributions**: Provide guidelines for contributors to use Negarin branding

### Localization Impact

1. **Translation Files**: Update any translation keys that reference Pixelfed
2. **Multi-language Support**: Ensure branding changes work across all supported languages
3. **RTL Language Support**: Verify branding displays correctly in right-to-left languages
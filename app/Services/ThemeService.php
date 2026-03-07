<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class ThemeService
{
    protected $cacheKey = 'theme_settings';
    protected $cacheTtl = 3600; // 1 hour

    /**
     * Get all theme settings
     */
    public function getThemeSettings()
    {
        return Cache::remember($this->cacheKey, $this->cacheTtl, function () {
            return DB::table('settings')
                ->where('group', 'theme')
                ->pluck('value', 'key')
                ->toArray();
        });
    }

    /**
     * Get a specific theme setting
     */
    public function getThemeSetting(string $key, $default = null)
    {
        $settings = $this->getThemeSettings();
        return $settings[$key] ?? $default;
    }

    /**
     * Update theme settings
     */
    public function updateThemeSettings(array $settings)
    {
        DB::transaction(function () use ($settings) {
            foreach ($settings as $key => $value) {
                DB::table('settings')
                    ->where('key', $key)
                    ->where('group', 'theme')
                    ->update(['value' => $value, 'updated_at' => now()]);
            }
        });

        // Clear cache
        $this->clearCache();
    }

    /**
     * Get theme CSS variables for inline styles
     */
    public function getThemeCssVariables()
    {
        $settings = $this->getThemeSettings();

        return [
            '--theme-primary-color' => $settings['theme_primary_color'] ?? '#f59e0b',
            '--theme-secondary-color' => $settings['theme_secondary_color'] ?? '#3b82f6',
            '--theme-background-color' => $settings['theme_background_color'] ?? '#0f172a',
            '--theme-sidebar-color' => $settings['theme_sidebar_color'] ?? '#0b1220',
            '--theme-card-color' => $settings['theme_card_color'] ?? '#111827',
            '--theme-text-primary' => $settings['theme_text_primary'] ?? '#f8fafc',
            '--theme-text-secondary' => $settings['theme_text_secondary'] ?? '#cbd5e1',
            '--theme-text-tertiary' => $settings['theme_text_tertiary'] ?? '#94a3b8',
            '--theme-border-color' => $settings['theme_border_color'] ?? '#334155',
            '--theme-success-color' => $settings['theme_success_color'] ?? '#22c55e',
            '--theme-warning-color' => $settings['theme_warning_color'] ?? '#f59e0b',
            '--theme-danger-color' => $settings['theme_danger_color'] ?? '#ef4444',
            '--theme-info-color' => $settings['theme_info_color'] ?? '#3b82f6',
            '--theme-font-family' => $settings['theme_font_family'] ?? 'Figtree, system-ui, -apple-system, Segoe UI, Roboto, Ubuntu, Cantarell, Noto Sans, sans-serif',
            '--theme-radius' => $settings['theme_radius'] ?? '0.5rem',
            '--theme-shadow' => $settings['theme_shadow'] ?? '0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05)',
            '--theme-transition' => $settings['theme_transition'] ?? 'all 0.3s ease-in-out',
        ];
    }

    /**
     * Get theme CSS as a string for dynamic injection
     */
    public function getThemeCss()
    {
        $cssVariables = $this->getThemeCssVariables();
        $css = ":root {\n";

        foreach ($cssVariables as $variable => $value) {
            $css .= "    {$variable}: {$value};\n";
        }

        $css .= "}\n\n";

        // Add utility classes
        $css .= $this->getUtilityClasses();

        return $css;
    }

    /**
     * Get utility classes based on theme settings
     */
    protected function getUtilityClasses()
    {
        $settings = $this->getThemeSettings();

        $primary = $settings['theme_primary_color'] ?? '#f59e0b';
        $secondary = $settings['theme_secondary_color'] ?? '#3b82f6';
        $success = $settings['theme_success_color'] ?? '#22c55e';
        $warning = $settings['theme_warning_color'] ?? '#f59e0b';
        $danger = $settings['theme_danger_color'] ?? '#ef4444';
        $info = $settings['theme_info_color'] ?? '#3b82f6';
        $radius = $settings['theme_radius'] ?? '0.5rem';
        $shadow = $settings['theme_shadow'] ?? '0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05)';
        $transition = $settings['theme_transition'] ?? 'all 0.3s ease-in-out';

        return "
/* Dynamic Theme Classes */
.bg-theme-primary { background-color: {$primary}; }
.bg-theme-secondary { background-color: {$secondary}; }
.bg-theme-success { background-color: {$success}; }
.bg-theme-warning { background-color: {$warning}; }
.bg-theme-danger { background-color: {$danger}; }
.bg-theme-info { background-color: {$info}; }

.text-theme-primary { color: {$primary}; }
.text-theme-secondary { color: {$secondary}; }
.text-theme-success { color: {$success}; }
.text-theme-warning { color: {$warning}; }
.text-theme-danger { color: {$danger}; }
.text-theme-info { color: {$info}; }

.border-theme-primary { border-color: {$primary}; }
.border-theme-secondary { border-color: {$secondary}; }
.border-theme-success { border-color: {$success}; }
.border-theme-warning { border-color: {$warning}; }
.border-theme-danger { border-color: {$danger}; }
.border-theme-info { border-color: {$info}; }

/* Button Variants */
.btn-theme-primary { background-color: {$primary}; color: #000; border-radius: {$radius}; box-shadow: {$shadow}; transition: {$transition}; }
.btn-theme-primary:hover { filter: brightness(0.9); }
.btn-theme-secondary { background-color: {$secondary}; color: white; border-radius: {$radius}; box-shadow: {$shadow}; transition: {$transition}; }
.btn-theme-secondary:hover { filter: brightness(0.9); }
.btn-theme-success { background-color: {$success}; color: white; border-radius: {$radius}; box-shadow: {$shadow}; transition: {$transition}; }
.btn-theme-success:hover { filter: brightness(0.9); }
.btn-theme-warning { background-color: {$warning}; color: #000; border-radius: {$radius}; box-shadow: {$shadow}; transition: {$transition}; }
.btn-theme-warning:hover { filter: brightness(0.9); }
.btn-theme-danger { background-color: {$danger}; color: white; border-radius: {$radius}; box-shadow: {$shadow}; transition: {$transition}; }
.btn-theme-danger:hover { filter: brightness(0.9); }
.btn-theme-info { background-color: {$info}; color: white; border-radius: {$radius}; box-shadow: {$shadow}; transition: {$transition}; }
.btn-theme-info:hover { filter: brightness(0.9); }

/* Card and Container Styles */
.card-theme { background-color: var(--theme-card-color); border: 1px solid var(--theme-border-color); border-radius: {$radius}; box-shadow: {$shadow}; transition: {$transition}; }
.card-theme:hover { transform: translateY(-2px); box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1); }

/* Input and Form Styles */
.input-theme { background-color: var(--theme-background-color); border: 1px solid var(--theme-border-color); color: var(--theme-text-primary); border-radius: {$radius}; transition: {$transition}; }
.input-theme:focus { outline: none; border-color: {$primary}; box-shadow: 0 0 0 3px {$primary}33; }

/* Sidebar and Navigation */
.sidebar-theme { background-color: var(--theme-sidebar-color); border-right: 1px solid var(--theme-border-color); }
.nav-item-theme { color: var(--theme-text-secondary); transition: {$transition}; }
.nav-item-theme:hover { color: {$primary}; background-color: {$primary}20; }
.nav-item-theme.active { color: {$primary}; background-color: {$primary}30; }

/* Text Categories */
.text-theme-primary { color: var(--theme-text-primary); }
.text-theme-secondary { color: var(--theme-text-secondary); }
.text-theme-tertiary { color: var(--theme-text-tertiary); }

/* Background Categories */
.bg-theme-background { background-color: var(--theme-background-color); }
.bg-theme-card { background-color: var(--theme-card-color); }
.bg-theme-sidebar { background-color: var(--theme-sidebar-color); }

/* Border Categories */
.border-theme { border-color: var(--theme-border-color); }
.border-theme-light { border-color: var(--theme-border-color); opacity: 0.5; }

/* Shadow Categories */
.shadow-theme { box-shadow: {$shadow}; }
.shadow-theme-hover:hover { box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04); }

/* Transition Categories */
.transition-theme { transition: {$transition}; }
";
    }

    /**
     * Clear theme cache
     */
    public function clearCache()
    {
        Cache::forget($this->cacheKey);
    }

    /**
     * Reset theme to default values (Kotel Hotel Management System theme)
     */
    public function resetTheme()
    {
        $defaultSettings = [
            'theme_mode' => 'dark',
            'theme_primary_color' => '#FFD700', // Kotel Yellow
            'theme_secondary_color' => '#87CEEB', // Sky Blue
            'theme_background_color' => '#000000', // Black
            'theme_sidebar_color' => '#1a1a1a', // Dark Gray
            'theme_card_color' => '#111827', // Dark Blue-Gray
            'theme_text_primary' => '#ffffff', // White
            'theme_text_secondary' => '#87CEEB', // Sky Blue
            'theme_text_tertiary' => '#D1D5DB', // Light Gray
            'theme_border_color' => '#374151', // Gray
            'theme_success_color' => '#10B981', // Green
            'theme_warning_color' => '#F97316', // Orange
            'theme_danger_color' => '#EF4444', // Red
            'theme_info_color' => '#8B5CF6', // Purple
            'theme_font_family' => 'Figtree, system-ui, -apple-system, Segoe UI, Roboto, Ubuntu, Cantarell, Noto Sans, sans-serif',
            'theme_radius' => '0.5rem',
            'theme_shadow' => '0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05)',
            'theme_transition' => 'all 0.3s ease-in-out',
        ];

        $this->updateThemeSettings($defaultSettings);
    }
}

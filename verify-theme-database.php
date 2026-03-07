<?php

/**
 * Theme Database Verification Script
 * Run this to verify theme settings are saved in the database
 * Usage: php verify-theme-database.php
 */

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Setting;

echo "=== Theme Database Verification ===\n\n";

// Get all theme settings from database
$themeSettings = Setting::where('group', 'theme')->get();

if ($themeSettings->isEmpty()) {
    echo "❌ No theme settings found in database!\n";
    echo "This means theme settings are not being saved properly.\n";
} else {
    echo "✅ Found " . $themeSettings->count() . " theme settings in database:\n\n";
    
    foreach ($themeSettings as $setting) {
        echo "📦 {$setting->key}: {$setting->value}\n";
    }
    
    echo "\n🎯 Checking for specific theme settings:\n";
    
    $requiredSettings = [
        'theme_mode',
        'theme_primary_color',
        'theme_secondary_color',
        'theme_background_color',
        'theme_sidebar_color',
        'theme_card_color',
        'theme_text_primary',
        'theme_text_secondary',
        'theme_text_tertiary',
        'theme_border_color',
        'theme_success_color',
        'theme_warning_color',
        'theme_danger_color'
    ];
    
    $missingSettings = [];
    
    foreach ($requiredSettings as $key) {
        $setting = $themeSettings->where('key', $key)->first();
        if ($setting) {
            echo "✅ {$key}: {$setting->value}\n";
        } else {
            echo "❌ {$key}: MISSING\n";
            $missingSettings[] = $key;
        }
    }
    
    if (!empty($missingSettings)) {
        echo "\n⚠️ Missing theme settings: " . implode(', ', $missingSettings) . "\n";
    } else {
        echo "\n🎉 All required theme settings are present!\n";
    }
}

// Check recent log entries for theme saves
echo "\n=== Recent Theme Save Logs ===\n";

$logFile = storage_path('logs/laravel.log');
if (file_exists($logFile)) {
    $logs = file_get_contents($logFile);
    $recentLogs = array_slice(explode("\n", $logs), -50); // Last 50 lines
    
    $themeLogs = array_filter($recentLogs, function($line) {
        return strpos($line, 'Theme settings saved to database') !== false;
    });
    
    if (!empty($themeLogs)) {
        echo "✅ Found recent theme save logs:\n";
        foreach (array_slice($themeLogs, -5) as $log) {
            echo "📝 " . trim($log) . "\n";
        }
    } else {
        echo "❌ No recent theme save logs found\n";
    }
} else {
    echo "⚠️ Log file not found\n";
}

echo "\n=== Verification Complete ===\n";

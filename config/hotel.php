<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Hotel Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains configuration settings for the hotel management system.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Automation Settings
    |--------------------------------------------------------------------------
    |
    | Configure automated processes for reservation management.
    |
    */
    'automation' => [
        // Hours after check-in time to mark reservation as no-show
        'no_show_hours' => env('HOTEL_NO_SHOW_HOURS', 2),
        
        // Hour of day to perform automatic check-out (24-hour format)
        'auto_checkout_hour' => env('HOTEL_AUTO_CHECKOUT_HOUR', 11),
        
        // Hours to wait before cancelling unconfirmed pending reservations
        'pending_confirmation_hours' => env('HOTEL_PENDING_CONFIRMATION_HOURS', 24),
        
        // Enable email notifications for automated status changes
        'enable_notifications' => env('HOTEL_ENABLE_NOTIFICATIONS', true),
        
        // Enable room status updates
        'enable_room_updates' => env('HOTEL_ENABLE_ROOM_UPDATES', true),
        
        // Enable housekeeping task creation
        'enable_housekeeping_tasks' => env('HOTEL_ENABLE_HOUSEKEEPING_TASKS', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Check-in/Check-out Settings
    |--------------------------------------------------------------------------
    |
    | Configure check-in and check-out policies.
    |
    */
    'check_in' => [
        'default_time' => env('HOTEL_CHECK_IN_TIME', '14:00'),
        'earliest_time' => env('HOTEL_EARLIEST_CHECK_IN', '12:00'),
        'latest_time' => env('HOTEL_LATEST_CHECK_IN', '22:00'),
    ],

    'check_out' => [
        'default_time' => env('HOTEL_CHECK_OUT_TIME', '11:00'),
        'latest_time' => env('HOTEL_LATEST_CHECK_OUT', '12:00'),
    ],

    /*
    |--------------------------------------------------------------------------
    | No-show and Cancellation Policies
    |--------------------------------------------------------------------------
    |
    | Configure policies for no-shows and cancellations.
    |
    */
    'policies' => [
        'no_show_charge' => env('HOTEL_NO_SHOW_CHARGE', 'first_night'), // first_night, full_stay, percentage, fixed
        'no_show_charge_percentage' => env('HOTEL_NO_SHOW_CHARGE_PERCENTAGE', 100),
        'no_show_charge_fixed' => env('HOTEL_NO_SHOW_CHARGE_FIXED', 0),
        
        'cancellation_policy' => env('HOTEL_CANCELLATION_POLICY', 'flexible'), // flexible, moderate, strict
        'cancellation_deadline_hours' => env('HOTEL_CANCELLATION_DEADLINE_HOURS', 24),
    ],

    /*
    |--------------------------------------------------------------------------
    | Notification Settings
    |--------------------------------------------------------------------------
    |
    | Configure notification preferences.
    |
    */
    'notifications' => [
        'email' => [
            'enabled' => env('HOTEL_EMAIL_NOTIFICATIONS', true),
            'from_address' => env('HOTEL_FROM_EMAIL', 'reservations@hotel.com'),
            'from_name' => env('HOTEL_FROM_NAME', 'Hotel Management'),
        ],
        
        'sms' => [
            'enabled' => env('HOTEL_SMS_NOTIFICATIONS', false),
            'provider' => env('HOTEL_SMS_PROVIDER', 'twilio'),
        ],
        
        'in_app' => [
            'enabled' => env('HOTEL_IN_APP_NOTIFICATIONS', true),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Room Management
    |--------------------------------------------------------------------------
    |
    | Configure room management settings.
    |
    */
    'rooms' => [
        'auto_release_on_no_show' => env('HOTEL_AUTO_RELEASE_NO_SHOW', true),
        'auto_set_needs_cleaning' => env('HOTEL_AUTO_SET_NEEDS_CLEANING', true),
        'buffer_time_minutes' => env('HOTEL_ROOM_BUFFER_TIME', 30),
    ],

    /*
    |--------------------------------------------------------------------------
    | Reporting Settings
    |--------------------------------------------------------------------------
    |
    | Configure reporting and analytics settings.
    |
    */
    'reporting' => [
        'daily_summary_time' => env('HOTEL_DAILY_SUMMARY_TIME', '23:59'),
        'weekly_summary_day' => env('HOTEL_WEEKLY_SUMMARY_DAY', 'sunday'),
        'monthly_summary_day' => env('HOTEL_MONTHLY_SUMMARY_DAY', 1),
    ],
];

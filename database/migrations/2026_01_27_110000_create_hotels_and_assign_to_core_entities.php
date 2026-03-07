<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Hotels (for future multi‑property support)
        if (!Schema::hasTable('hotels')) {
            Schema::create('hotels', function (Blueprint $table) {
                $table->id();
                $table->string('code')->unique();
                $table->string('name');
                $table->string('legal_name')->nullable();
                $table->string('brand_name')->nullable();
                $table->string('email')->nullable();
                $table->string('phone')->nullable();
                $table->string('alternate_phone')->nullable();
                $table->string('website')->nullable();
                $table->string('address_line1')->nullable();
                $table->string('address_line2')->nullable();
                $table->string('city')->nullable();
                $table->string('state')->nullable();
                $table->string('postal_code')->nullable();
                $table->string('country', 2)->default('NG');
                $table->string('timezone')->default('Africa/Lagos');
                $table->string('currency', 3)->default('NGN');
                $table->json('settings')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        // Attach hotel_id to core entities (nullable for backward compatibility)
        if (!Schema::hasColumn('rooms', 'hotel_id')) {
            Schema::table('rooms', function (Blueprint $table) {
                $table->foreignId('hotel_id')
                    ->nullable()
                    ->after('id')
                    ->constrained('hotels')
                    ->nullOnDelete();
            });
        }

        if (!Schema::hasColumn('guests', 'hotel_id')) {
            Schema::table('guests', function (Blueprint $table) {
                $table->foreignId('hotel_id')
                    ->nullable()
                    ->after('id')
                    ->constrained('hotels')
                    ->nullOnDelete();
            });
        }

        if (!Schema::hasColumn('reservations', 'hotel_id')) {
            Schema::table('reservations', function (Blueprint $table) {
                $table->foreignId('hotel_id')
                    ->nullable()
                    ->after('id')
                    ->constrained('hotels')
                    ->nullOnDelete();
            });
        }

        if (!Schema::hasColumn('guest_folios', 'hotel_id')) {
            Schema::table('guest_folios', function (Blueprint $table) {
                $table->foreignId('hotel_id')
                    ->nullable()
                    ->after('id')
                    ->constrained('hotels')
                    ->nullOnDelete();
            });
        }

        if (!Schema::hasColumn('hotel_services', 'hotel_id')) {
            Schema::table('hotel_services', function (Blueprint $table) {
                $table->foreignId('hotel_id')
                    ->nullable()
                    ->after('id')
                    ->constrained('hotels')
                    ->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        // Drop foreign keys / columns first
        if (Schema::hasColumn('hotel_services', 'hotel_id')) {
            Schema::table('hotel_services', function (Blueprint $table) {
                $table->dropConstrainedForeignId('hotel_id');
            });
        }

        if (Schema::hasColumn('guest_folios', 'hotel_id')) {
            Schema::table('guest_folios', function (Blueprint $table) {
                $table->dropConstrainedForeignId('hotel_id');
            });
        }

        if (Schema::hasColumn('reservations', 'hotel_id')) {
            Schema::table('reservations', function (Blueprint $table) {
                $table->dropConstrainedForeignId('hotel_id');
            });
        }

        if (Schema::hasColumn('guests', 'hotel_id')) {
            Schema::table('guests', function (Blueprint $table) {
                $table->dropConstrainedForeignId('hotel_id');
            });
        }

        if (Schema::hasColumn('rooms', 'hotel_id')) {
            Schema::table('rooms', function (Blueprint $table) {
                $table->dropConstrainedForeignId('hotel_id');
            });
        }

        if (Schema::hasTable('hotels')) {
            Schema::dropIfExists('hotels');
        }
    }
};


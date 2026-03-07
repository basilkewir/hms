<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('hall_booking_group_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hall_id')->constrained()->onDelete('cascade');
            $table->foreignId('group_booking_id')->constrained()->onDelete('cascade');
            $table->string('reviewer_type'); // customer, staff
            $table->string('reviewer_id')->nullable();
            $table->integer('rating'); // 1-5
            $table->text('review_text')->nullable();
            $table->json('review_details')->nullable(); // additional review details
            $table->boolean('is_approved')->default(false);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('hall_booking_group_reviews');
    }
};

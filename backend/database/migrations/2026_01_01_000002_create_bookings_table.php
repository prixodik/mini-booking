<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('slot_id')->constrained('slots');
            $table->string('customer_name');
            $table->string('customer_email');
            // Мягкая отмена: строка остаётся, ставится время отмены.
            $table->dateTime('cancelled_at')->nullable();
            // Равен slot_id у активной брони и NULL у отменённой.
            // UNIQUE-индекс допускает много NULL, но не две активные брони одного слота:
            // это гарантия «одно активное бронирование на слот» на уровне БД.
            $table->unsignedBigInteger('active_slot_id')->nullable()->unique();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};

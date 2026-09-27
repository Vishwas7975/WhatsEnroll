<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chat_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('whatsapp_users')->onDelete('cascade');
            $table->enum('direction', ['inbound', 'outbound']);
            $table->string('message_type')->default('text');
            $table->text('body')->nullable();
            $table->string('media_url')->nullable();
            $table->timestamp('timestamp')->useCurrent();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chat_history');
    }
};
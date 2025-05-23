<?php

use App\Enums\AuthType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('logo_url')->nullable();
            $table->string('default_user_role')->nullable();
            $table->integer('auth_type')->default(AuthType::MAGIC_MK);
            $table->string('magicmk_slug')->nullable();
            $table->string('magicmk_api_key')->nullable();
            $table->boolean('has_admin')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};

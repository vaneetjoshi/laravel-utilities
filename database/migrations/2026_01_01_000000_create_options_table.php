<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Use the table name defined in the config (defaults to 'options')
        $tableName = config('utilities.options_table', 'options');

        if (!Schema::hasTable($tableName)) {
            Schema::create($tableName, function (Blueprint $table) {
                $table->id();
                $table->string('type', 150)->unique()->index();
                $table->longText('value')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        $tableName = config('utilities.options_table', 'options');
        Schema::dropIfExists($tableName);
    }
};
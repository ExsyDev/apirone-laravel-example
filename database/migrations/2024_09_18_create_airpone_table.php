<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create(config('apirone.table_prefix') . 'invoice', function ($table) {
            $table->bigIncrements('id');
            $table->integer('order');
            $table->string('invoice', 64);
            $table->string('status', 10);
            $table->json('details')->nullable();
            $table->json('meta')->nullable();

            $table->timestamps();

            $table->index('order');
            $table->index('invoice');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists(config('apirone.table_prefix') . 'invoice');
    }
};

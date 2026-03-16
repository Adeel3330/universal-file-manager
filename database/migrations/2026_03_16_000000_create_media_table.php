<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('media', function (Blueprint $column) {
            $column->id();
            $column->string('name');
            $column->string('file_name')->nullable();
            $column->string('mime_type')->nullable();
            $column->string('path')->nullable();
            $column->string('disk')->default('public');
            $column->unsignedBigInteger('size')->nullable();
            $column->unsignedInteger('width')->nullable();
            $column->unsignedInteger('height')->nullable();
            $column->boolean('is_folder')->default(false);
            $column->unsignedBigInteger('parent_id')->nullable();
            $column->timestamps();

            $column->foreign('parent_id')
                ->references('id')
                ->on('media')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('media');
    }
};

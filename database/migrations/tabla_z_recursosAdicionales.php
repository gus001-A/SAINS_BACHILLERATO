<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('recursos_clase', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_clase');
            $table->string('titulo', 255);
            $table->enum('tipo', [
                'pdf', 'video_youtube', 'video_vimeo', 'video_drive', 
                'presentacion', 'documento', 'podcast', 'imagen', 
                'enlace', 'otros'
            ])->default('otros');
            $table->text('url');
            $table->text('descripcion')->nullable();
            $table->integer('orden')->default(0);
            $table->timestamps();
            
            $table->foreign('id_clase')
                  ->references('id')
                  ->on('clases')
                  ->onDelete('cascade');
                  
            $table->index('id_clase');
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('recursos_clase');
    }
};
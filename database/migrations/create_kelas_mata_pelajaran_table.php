<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateKelasMataPelajaranTable extends Migration
{
    public function up()
    {
        Schema::create('kelas_mata_pelajaran', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('kelas_id');
            $table->uuid('mata_pelajaran_id');
            $table->uuid('sekolah_id');
            $table->timestamps();
            
            $table->foreign('kelas_id')->references('id')->on('kelas')->onDelete('cascade');
            $table->foreign('mata_pelajaran_id')->references('id')->on('mata_pelajaran')->onDelete('cascade');
            $table->foreign('sekolah_id')->references('id')->on('sekolah')->onDelete('cascade');
            
            $table->unique(['kelas_id', 'mata_pelajaran_id', 'sekolah_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('kelas_mata_pelajaran');
    }
} 
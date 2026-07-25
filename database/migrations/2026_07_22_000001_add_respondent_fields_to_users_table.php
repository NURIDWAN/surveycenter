<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->tinyInteger('is_responden')->default(0)->after('is_admin');
            $table->string('whatsapp_number', 15)->nullable()->after('phone');
            $table->date('tanggal_lahir')->nullable()->after('whatsapp_number');
            $table->enum('jenis_kelamin', ['Laki-laki', 'Perempuan'])->nullable()->after('tanggal_lahir');
            $table->string('provinsi', 100)->nullable()->after('jenis_kelamin');
            $table->string('kota', 100)->nullable()->after('provinsi');
            $table->string('pendidikan', 100)->nullable()->after('kota');
            $table->string('pekerjaan', 100)->nullable()->after('pendidikan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'is_responden',
                'whatsapp_number',
                'tanggal_lahir',
                'jenis_kelamin',
                'provinsi',
                'kota',
                'pendidikan',
                'pekerjaan',
            ]);
        });
    }
};

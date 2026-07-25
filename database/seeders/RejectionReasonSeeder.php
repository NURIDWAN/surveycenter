<?php

namespace Database\Seeders;

use App\Models\RejectionReason;
use Illuminate\Database\Seeder;

class RejectionReasonSeeder extends Seeder
{
    public function run(): void
    {
        $reasons = [
            'Screenshot tidak jelas / tidak terbaca',
            'Screenshot bukan dari Google Form yang dimaksud',
            'Jawaban tidak sesuai kriteria',
            'Bukti pengisian tidak lengkap',
            'Duplikat pengisian',
            'Lainnya',
        ];

        foreach ($reasons as $label) {
            RejectionReason::updateOrCreate(
                ['label' => $label],
            );
        }
    }
}

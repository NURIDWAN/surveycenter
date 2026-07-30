<?php

return [
    'min_withdrawal' => (int) env('RESPONDENT_MIN_WITHDRAWAL', 50000),
    'proof_disk' => env('RESPONDENT_PROOF_DISK', 'public'),
    'proof_path' => env('RESPONDENT_PROOF_PATH', 'proofs/screenshots'),
    'proof_max_size_kb' => 2048,
    'proof_allowed_mimes' => ['jpg', 'jpeg', 'png'],
];

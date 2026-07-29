<?php

namespace Tests\Unit;

use App\Models\User;
use App\Services\SurveyEligibilityService;
use Carbon\Carbon;
use PHPUnit\Framework\TestCase;

class SurveyEligibilityServiceTest extends TestCase
{
    private SurveyEligibilityService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new SurveyEligibilityService();
    }

    public function test_matches_criteria_always_returns_true_when_disabled(): void
    {
        $user = $this->makeUser();
        $this->assertTrue($this->service->matchesCriteria($user, null));
        $this->assertTrue($this->service->matchesCriteria($user, ['jenis_kelamin' => ['Laki-laki']]));
    }

    private function makeUser(array $attributes = []): User
    {
        $defaults = [
            'id' => 1,
            'name' => 'Test User',
            'email' => 'test@example.com',
            'jenis_kelamin' => null,
            'tanggal_lahir' => null,
            'provinsi' => null,
            'kota' => null,
            'pendidikan' => null,
            'pekerjaan' => null,
        ];

        $merged = array_merge($defaults, $attributes);

        if ($merged['tanggal_lahir'] instanceof Carbon) {
            $merged['tanggal_lahir'] = $merged['tanggal_lahir']->format('Y-m-d');
        }

        $user = new User();
        $user->setRawAttributes($merged, true);

        return $user;
    }
}

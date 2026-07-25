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

    public function test_null_criteria_returns_true(): void
    {
        $user = $this->makeUser();
        $this->assertTrue($this->service->matchesCriteria($user, null));
    }

    public function test_empty_array_criteria_returns_true(): void
    {
        $user = $this->makeUser();
        $this->assertTrue($this->service->matchesCriteria($user, []));
    }

    public function test_matching_jenis_kelamin(): void
    {
        $user = $this->makeUser(['jenis_kelamin' => 'Laki-laki']);
        $criteria = ['jenis_kelamin' => ['Laki-laki', 'Perempuan']];

        $this->assertTrue($this->service->matchesCriteria($user, $criteria));
    }

    public function test_non_matching_jenis_kelamin(): void
    {
        $user = $this->makeUser(['jenis_kelamin' => 'Perempuan']);
        $criteria = ['jenis_kelamin' => ['Laki-laki']];

        $this->assertFalse($this->service->matchesCriteria($user, $criteria));
    }

    public function test_null_jenis_kelamin_with_criteria_returns_false(): void
    {
        $user = $this->makeUser(['jenis_kelamin' => null]);
        $criteria = ['jenis_kelamin' => ['Laki-laki']];

        $this->assertFalse($this->service->matchesCriteria($user, $criteria));
    }

    public function test_empty_jenis_kelamin_array_means_no_restriction(): void
    {
        $user = $this->makeUser(['jenis_kelamin' => null]);
        $criteria = ['jenis_kelamin' => []];

        $this->assertTrue($this->service->matchesCriteria($user, $criteria));
    }

    public function test_age_within_range(): void
    {
        $user = $this->makeUser(['tanggal_lahir' => Carbon::now()->subYears(25)]);
        $criteria = ['age_min' => 18, 'age_max' => 35];

        $this->assertTrue($this->service->matchesCriteria($user, $criteria));
    }

    public function test_age_below_minimum(): void
    {
        $user = $this->makeUser(['tanggal_lahir' => Carbon::now()->subYears(16)]);
        $criteria = ['age_min' => 18, 'age_max' => 35];

        $this->assertFalse($this->service->matchesCriteria($user, $criteria));
    }

    public function test_age_above_maximum(): void
    {
        $user = $this->makeUser(['tanggal_lahir' => Carbon::now()->subYears(40)]);
        $criteria = ['age_min' => 18, 'age_max' => 35];

        $this->assertFalse($this->service->matchesCriteria($user, $criteria));
    }

    public function test_null_tanggal_lahir_with_age_criteria_returns_false(): void
    {
        $user = $this->makeUser(['tanggal_lahir' => null]);
        $criteria = ['age_min' => 18];

        $this->assertFalse($this->service->matchesCriteria($user, $criteria));
    }

    public function test_matching_provinsi(): void
    {
        $user = $this->makeUser(['provinsi' => 'Jawa Barat']);
        $criteria = ['provinsi' => ['Jawa Barat', 'DKI Jakarta']];

        $this->assertTrue($this->service->matchesCriteria($user, $criteria));
    }

    public function test_non_matching_provinsi(): void
    {
        $user = $this->makeUser(['provinsi' => 'Jawa Tengah']);
        $criteria = ['provinsi' => ['Jawa Barat', 'DKI Jakarta']];

        $this->assertFalse($this->service->matchesCriteria($user, $criteria));
    }

    public function test_null_provinsi_with_criteria_returns_false(): void
    {
        $user = $this->makeUser(['provinsi' => null]);
        $criteria = ['provinsi' => ['Jawa Barat']];

        $this->assertFalse($this->service->matchesCriteria($user, $criteria));
    }

    public function test_matching_multiple_criteria(): void
    {
        $user = $this->makeUser([
            'jenis_kelamin' => 'Laki-laki',
            'tanggal_lahir' => Carbon::now()->subYears(25),
            'provinsi' => 'Jawa Barat',
            'kota' => 'Bandung',
            'pendidikan' => 'S1',
            'pekerjaan' => 'Karyawan Swasta',
        ]);

        $criteria = [
            'jenis_kelamin' => ['Laki-laki'],
            'age_min' => 18,
            'age_max' => 35,
            'provinsi' => ['Jawa Barat', 'DKI Jakarta'],
            'kota' => ['Bandung', 'Jakarta'],
            'pendidikan' => ['S1', 'S2'],
            'pekerjaan' => ['Karyawan Swasta', 'Mahasiswa'],
        ];

        $this->assertTrue($this->service->matchesCriteria($user, $criteria));
    }

    public function test_fails_on_one_non_matching_criterion(): void
    {
        $user = $this->makeUser([
            'jenis_kelamin' => 'Laki-laki',
            'tanggal_lahir' => Carbon::now()->subYears(25),
            'provinsi' => 'Jawa Barat',
            'pendidikan' => 'SMA', // Does not match criteria
            'pekerjaan' => 'Karyawan Swasta',
        ]);

        $criteria = [
            'jenis_kelamin' => ['Laki-laki'],
            'age_min' => 18,
            'age_max' => 35,
            'provinsi' => ['Jawa Barat'],
            'pendidikan' => ['S1', 'S2'],
            'pekerjaan' => ['Karyawan Swasta'],
        ];

        $this->assertFalse($this->service->matchesCriteria($user, $criteria));
    }

    /**
     * Create a User stub with the given attributes.
     */
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

        // Convert tanggal_lahir to a string format before setting on model
        // to avoid the cast trying to hit the database
        if ($merged['tanggal_lahir'] instanceof Carbon) {
            $merged['tanggal_lahir'] = $merged['tanggal_lahir']->format('Y-m-d');
        }

        $user = new User();
        $user->setRawAttributes($merged, true);

        return $user;
    }
}

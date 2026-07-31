<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Survey;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RespondentSurveyVisibilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_survey_directory_renders_for_authenticated_roles_and_guests(): void
    {
        $client = User::factory()->create(['is_responden' => 0, 'is_admin' => 0]);
        $responden = User::factory()->create(['is_responden' => 1, 'is_admin' => 0]);
        $survey = Survey::create([
            'user_id' => $client->id,
            'title' => 'Survey Direktori Publik',
            'question_count' => 5,
            'respondent_count' => 100,
            'status' => Survey::STATUS_ACTIVE,
        ]);

        $this->assertTrue($responden->isResponden());
        $this->assertFalse($client->isResponden());

        $this->actingAs($responden)
            ->get(route('kumpulan-quisioner'))
            ->assertOk()
            ->assertSee('Isi Kuisioner Sekarang')
            ->assertSee(route('responden.surveys.show', $survey), false);

        $this->actingAs($client)
            ->get(route('kumpulan-quisioner'))
            ->assertOk()
            ->assertSee('Lihat Detail Kuisioner');

        auth()->logout();

        $this->get(route('kumpulan-quisioner'))
            ->assertOk()
            ->assertSee('Isi Kuisioner (Login Responden)')
            ->assertSee(route('login'), false);
    }

    public function test_active_surveys_with_default_criteria_are_visible_to_responden_with_profile(): void
    {
        $client = User::factory()->create(['is_responden' => 0, 'is_admin' => 0]);
        $responden = User::factory()->create([
            'is_responden' => 1,
            'is_admin' => 0,
            'jenis_kelamin' => 'Laki-laki',
            'provinsi' => 'DKI Jakarta',
            'kota' => 'Jakarta Selatan',
            'pendidikan' => 'S1',
            'pekerjaan' => 'Karyawan Swasta',
            'tanggal_lahir' => '1995-05-15',
        ]);

        // Survey created with default empty eligibility criteria JSON
        $survey1 = Survey::create([
            'user_id' => $client->id,
            'title' => 'Survey Kepuasan 1',
            'question_count' => 5,
            'respondent_count' => 100,
            'status' => Survey::STATUS_ACTIVE,
            'form_link' => 'https://forms.google.com/test1',
            'eligibility_criteria' => [
                'jenis_kelamin' => [],
                'provinsi' => [],
                'kota' => [],
                'pendidikan' => [],
                'pekerjaan' => [],
                'age_min' => null,
                'age_max' => null,
            ],
        ]);

        $survey2 = Survey::create([
            'user_id' => $client->id,
            'title' => 'Survey Kepuasan 2',
            'question_count' => 10,
            'respondent_count' => 50,
            'status' => Survey::STATUS_ACTIVE,
            'form_link' => 'https://forms.google.com/test2',
            'eligibility_criteria' => null,
        ]);

        $response = $this->actingAs($responden)->get(route('responden.surveys.index'));

        $response->assertOk();
        $response->assertSee('Survey Kepuasan 1');
        $response->assertSee('Survey Kepuasan 2');
    }
}

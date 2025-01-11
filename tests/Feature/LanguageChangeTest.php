<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class LanguageChangeTest extends TestCase
{
    /** @test */
    public function it_changes_language_successfully_when_locale_is_supported()
    {
        $locale = 'ar';

        $response = $this->getJson("/api/lang/{$locale}");

        $response->assertStatus(200)
            ->assertJson([
                'status' => true,
                'code' => 200,
                'msg' => 'Language changed successfully',
            ]);

        $this->assertEquals(app()->getLocale(), $locale);
    }

    /** @test */
    public function it_returns_error_when_locale_is_not_supported()
    {
        $locale = 'es';

        $response = $this->getJson("/api/lang/{$locale}");

        $response->assertStatus(400)
            ->assertJson([
                'status' => false,
                'code' => 400,
                'msg' => 'Language not supported',
            ]);
    }
}

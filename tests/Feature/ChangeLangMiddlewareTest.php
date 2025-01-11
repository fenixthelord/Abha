<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ChangeLangMiddlewareTest extends TestCase
{
    /** @test */
    public function test_it_sets_locale_based_on_accept_language_header()
    {
        $response = $this->withHeaders([
            'Accept-Language' => 'ar', 
        ])->getJson('/api/lang/ar');

        $this->assertEquals('ar', app()->getLocale());

        $response->assertStatus(200);
    }

    /** @test */
    public function test_it_defaults_to_en_when_accept_language_header_is_missing()
    {
        $response = $this->getJson('/api/lang');

        $this->assertEquals('en', app()->getLocale());

        $response->assertStatus(200);
    }
}

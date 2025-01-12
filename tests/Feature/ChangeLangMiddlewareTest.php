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
        ])->getJson('/api/lang/error');

        $this->assertEquals('en', app()->getLocale());
    }

    /** @test */
    public function test_it_defaults_to_en_when_accept_language_header_is_missing()
    {
        $response = $this->withHeaders(['Accept-Language' => ''])->getJson('/api/lang/n');

        $this->assertEquals('en', app()->getLocale());
    }
}

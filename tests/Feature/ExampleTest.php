<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example — contact page needs no database.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        $response = $this->get('/contact');

        $response->assertStatus(200);
    }
}

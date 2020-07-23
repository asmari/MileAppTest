<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class AuthTest extends TestCase
{
    /**
     * A basic unit test example.
     *
     * @return void
     */
    public function test_can_login() {

        $data = [
            'email' => $this->faker->email,
            'password' => $this->faker->password,
        ];

        $this->post(route('login'), $data)
            ->assertStatus(200)
            ->assertJson($data);
    }
}

<?php

use Livewire\Volt\Volt;

// Registration is disabled in this application
// Route /register is commented out in routes/auth.php
test('registration screen can be rendered', function () {
    $response = $this->get('/register');

    // Registration route is disabled, so expect 404
    $response->assertStatus(404);
})->skip('Registration is disabled in this application');

test('new users can register', function () {
    // Registration is disabled in this application
    $this->markTestSkipped('Registration is disabled in this application');
});

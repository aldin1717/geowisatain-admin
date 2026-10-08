<?php

namespace Tests\Feature;

use Tests\TestCase;

class DashboardAccessTest extends TestCase
{
    public function test_guest_is_redirected_to_login_when_opening_dashboard_url(): void
    {
        $this->get('/dashboard')
            ->assertRedirect(route('login'));
    }
}

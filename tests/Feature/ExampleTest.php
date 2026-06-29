<?php

namespace Tests\Feature;

use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * La raíz manda al panel; un invitado termina redirigido al login.
     */
    public function test_the_root_redirects_guests(): void
    {
        $response = $this->get('/');

        $response->assertRedirect();
    }
}

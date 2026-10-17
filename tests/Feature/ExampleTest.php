<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Главная страница отдаёт редирект на каталог.
     */
    public function test_the_home_page_redirects_to_the_catalog(): void
    {
        $response = $this->get('/');

        $response->assertRedirect(route('products.index'));
    }

    /**
     * Каталог открывается и рендерится.
     */
    public function test_the_catalog_page_is_successful(): void
    {
        $response = $this->get(route('products.index'));

        $response->assertOk();
    }
}

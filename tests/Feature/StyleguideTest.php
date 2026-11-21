<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class StyleguideTest extends TestCase
{
    public function test_styleguide_is_hidden_outside_local_environment(): void
    {
        $this->get('/styleguide')->assertNotFound();
    }

    public function test_styleguide_renders_when_registered(): void
    {
        Route::view('/styleguide', 'styleguide')->name('styleguide');
        Route::view('/styleguide/admin', 'styleguide-admin')->name('styleguide.admin');
        Route::getRoutes()->refreshNameLookups();

        $this->get('/styleguide')
            ->assertOk()
            ->assertSee('Styleguide Rumusin')
            ->assertSee('Lembar Rumus')
            ->assertSee('Dikembangkan oleh');

        $this->get('/styleguide/admin')
            ->assertOk()
            ->assertSee('Contoh layout admin');
    }
}
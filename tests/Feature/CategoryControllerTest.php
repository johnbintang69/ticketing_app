<?php

use App\Models\User;
use App\Models\Kategori;

test('admin can access categories index page with pagination', function () {
    User::unguard();
    $admin = User::create([
        'name' => 'Admin',
        'email' => 'admin_categories@example.com',
        'password' => bcrypt('password'),
        'role' => 'admin',
    ]);
    User::reguard();

    Kategori::create(['nama' => 'Kategori 1']);

    $response = $this->actingAs($admin)->get(route('categories.index'));

    $response->assertStatus(200);
    $response->assertViewIs('pages.admin.categories.index');
    $response->assertViewHas('categories');
});

test('admin can store a new category', function () {
    User::unguard();
    $admin = User::create([
        'name' => 'Admin',
        'email' => 'admin_cat_store@example.com',
        'password' => bcrypt('password'),
        'role' => 'admin',
    ]);
    User::reguard();

    $response = $this->actingAs($admin)->post(route('categories.store'), [
        'nama' => 'Kategori Baru'
    ]);

    $response->assertRedirect(route('categories.index'));
    $this->assertDatabaseHas('kategoris', ['nama' => 'Kategori Baru']);
});

test('admin can update an existing category', function () {
    User::unguard();
    $admin = User::create([
        'name' => 'Admin',
        'email' => 'admin_cat_up@example.com',
        'password' => bcrypt('password'),
        'role' => 'admin',
    ]);
    User::reguard();

    $category = Kategori::create(['nama' => 'Kategori Awal']);

    $response = $this->actingAs($admin)->put(route('categories.update', $category->id), [
        'nama' => 'Kategori Diubah'
    ]);

    $response->assertRedirect(route('categories.index'));
    $this->assertDatabaseHas('kategoris', [
        'id' => $category->id,
        'nama' => 'Kategori Diubah'
    ]);
});

test('admin can destroy a category', function () {
    User::unguard();
    $admin = User::create([
        'name' => 'Admin',
        'email' => 'admin_cat_del@example.com',
        'password' => bcrypt('password'),
        'role' => 'admin',
    ]);
    User::reguard();

    $category = Kategori::create(['nama' => 'Kategori Dihapus']);

    $response = $this->actingAs($admin)->delete(route('categories.destroy', $category->id));

    $response->assertRedirect(route('categories.index'));
    $this->assertDatabaseMissing('kategoris', ['id' => $category->id]);
});

test('admin can access dashboard analytics page', function () {
    User::unguard();
    $admin = User::create([
        'name' => 'Admin',
        'email' => 'admin_dashboard@example.com',
        'password' => bcrypt('password'),
        'role' => 'admin',
    ]);
    User::reguard();

    $response = $this->actingAs($admin)->get(route('dashboard'));

    $response->assertStatus(200);
    $response->assertViewIs('pages.admin.dashboard');
    $response->assertViewHasAll(['stats', 'recent_events', 'recent_orders']);
});

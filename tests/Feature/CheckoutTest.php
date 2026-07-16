<?php

use App\Models\User;
use App\Models\Event;
use App\Models\Kategori;
use App\Models\Tiket;
use App\Models\MetodePembayaran;

test('guest is redirected to login when trying to checkout', function () {
    User::unguard();
    $user = User::create([
        'name' => 'Regular User',
        'email' => 'user_guest_test@example.com',
        'password' => bcrypt('password'),
        'role' => 'user',
    ]);
    User::reguard();

    $kategori = Kategori::create(['nama' => 'Konser']);
    Event::unguard();
    $event = Event::create([
        'user_id' => $user->id,
        'kategori_id' => $kategori->id,
        'judul' => 'Event Test',
        'deskripsi' => 'Deskripsi',
        'lokasi' => 'Bandung',
        'gambar' => 'konser.jpg',
        'tanggal_waktu' => now()->addDays(2),
    ]);
    Event::reguard();

    $tiket = $event->tikets()->create([
        'tipe' => 'reguler',
        'harga' => 50000,
        'stok' => 10,
    ]);

    $response = $this->get(route('checkout.show', $tiket));
    $response->assertRedirect(route('login'));
});

test('authenticated user can view checkout page', function () {
    User::unguard();
    $user = User::create([
        'name' => 'Regular User',
        'email' => 'user_test@example.com',
        'password' => bcrypt('password'),
        'role' => 'user',
    ]);
    User::reguard();

    $kategori = Kategori::create(['nama' => 'Konser']);
    Event::unguard();
    $event = Event::create([
        'user_id' => $user->id,
        'kategori_id' => $kategori->id,
        'judul' => 'Event Test',
        'deskripsi' => 'Deskripsi',
        'lokasi' => 'Bandung',
        'gambar' => 'konser.jpg',
        'tanggal_waktu' => now()->addDays(2),
    ]);
    Event::reguard();

    $tiket = $event->tikets()->create([
        'tipe' => 'reguler',
        'harga' => 50000,
        'stok' => 10,
    ]);

    $response = $this->actingAs($user)->get(route('checkout.show', $tiket));

    $response->assertStatus(200);
    $response->assertViewIs('pages.checkout');
    $response->assertViewHasAll(['tiket', 'paymentMethods']);
});

test('authenticated user can complete checkout and deduct ticket stock', function () {
    User::unguard();
    $user = User::create([
        'name' => 'Regular User',
        'email' => 'user_test_2@example.com',
        'password' => bcrypt('password'),
        'role' => 'user',
    ]);
    User::reguard();

    $kategori = Kategori::create(['nama' => 'Konser']);
    Event::unguard();
    $event = Event::create([
        'user_id' => $user->id,
        'kategori_id' => $kategori->id,
        'judul' => 'Event Test',
        'deskripsi' => 'Deskripsi',
        'lokasi' => 'Bandung',
        'gambar' => 'konser.jpg',
        'tanggal_waktu' => now()->addDays(2),
    ]);
    Event::reguard();

    $tiket = $event->tikets()->create([
        'tipe' => 'reguler',
        'harga' => 50000,
        'stok' => 10,
    ]);

    $paymentMethod = MetodePembayaran::create([
        'nama' => 'Bank BCA',
        'tipe' => 'Transfer Bank',
        'nomor_rekening' => '12345',
        'instruksi' => 'Transfer nominal tepat',
        'is_active' => true,
    ]);

    $response = $this->actingAs($user)->post(route('checkout.store'), [
        'tiket_id' => $tiket->id,
        'jumlah' => 2,
        'metode_pembayaran_id' => $paymentMethod->id,
    ]);

    $response->assertRedirect(route('checkout.success'));
    
    // Assert stock was decremented
    $tiket->refresh();
    expect($tiket->stok)->toBe(8);

    // Assert order was stored
    $this->assertDatabaseHas('orders', [
        'user_id' => $user->id,
        'event_id' => $event->id,
        'total_harga' => 100000,
        'metode_pembayaran_id' => $paymentMethod->id,
    ]);
});

test('authenticated user can view checkout success page', function () {
    User::unguard();
    $user = User::create([
        'name' => 'Regular User',
        'email' => 'user_success_test@example.com',
        'password' => bcrypt('password'),
        'role' => 'user',
    ]);
    User::reguard();

    $response = $this->actingAs($user)->get(route('checkout.success'));

    $response->assertStatus(200);
    $response->assertViewIs('pages.checkout_success');
});

test('admin can access payment methods index page', function () {
    User::unguard();
    $admin = User::create([
        'name' => 'Admin User',
        'email' => 'admin_pay@example.com',
        'password' => bcrypt('password'),
        'role' => 'admin',
    ]);
    User::reguard();

    $response = $this->actingAs($admin)->get(route('payment-methods.index'));

    $response->assertStatus(200);
    $response->assertViewIs('pages.admin.payment_methods.index');
    $response->assertViewHas('methods');
});

test('admin can store a new payment method', function () {
    User::unguard();
    $admin = User::create([
        'name' => 'Admin User',
        'email' => 'admin_pay_store@example.com',
        'password' => bcrypt('password'),
        'role' => 'admin',
    ]);
    User::reguard();

    $response = $this->actingAs($admin)->post(route('payment-methods.store'), [
        'nama' => 'Bank Mandiri',
        'tipe' => 'Transfer Bank',
        'is_active' => 1,
    ]);

    $response->assertRedirect(route('payment-methods.index'));
    $this->assertDatabaseHas('metode_pembayarans', ['nama' => 'Bank Mandiri']);
});

test('admin can deactivate an existing payment method', function () {
    User::unguard();
    $admin = User::create([
        'name' => 'Admin User',
        'email' => 'admin_pay_deactivate@example.com',
        'password' => bcrypt('password'),
        'role' => 'admin',
    ]);
    User::reguard();

    $paymentMethod = MetodePembayaran::create([
        'nama' => 'Bank Danamon',
        'tipe' => 'Transfer Bank',
        'is_active' => true,
    ]);

    // Send PUT request WITHOUT 'is_active' parameter to simulate unchecking the checkbox
    $response = $this->actingAs($admin)->put(route('payment-methods.update', $paymentMethod->id), [
        'nama' => 'Bank Danamon',
        'tipe' => 'Transfer Bank',
    ]);

    $response->assertRedirect(route('payment-methods.index'));
    
    $paymentMethod->refresh();
    expect($paymentMethod->is_active)->toBeFalse();
});

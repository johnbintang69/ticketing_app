<?php

use App\Models\Event;
use App\Models\Order;
use App\Models\Kategori;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(Tests\TestCase::class, RefreshDatabase::class);

test('event status is upcoming when tanggal_waktu is in the future', function () {
    $event = new Event([
        'tanggal_waktu' => now()->addDay(),
    ]);

    expect($event->status)->toBe('Upcoming');
});

test('event status is ongoing when tanggal_waktu is within the last 3 hours', function () {
    $event = new Event([
        'tanggal_waktu' => now()->subHours(2),
    ]);

    expect($event->status)->toBe('Ongoing');
});

test('event status is completed when tanggal_waktu is older than 3 hours', function () {
    $event = new Event([
        'tanggal_waktu' => now()->subHours(4),
    ]);

    expect($event->status)->toBe('Completed');
});

test('event hasSales returns true if it has orders', function () {
    $user = User::create([
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => bcrypt('password'),
    ]);

    $kategori = Kategori::create([
        'nama' => 'Test Category',
    ]);

    // Force create or disable mass-assignment protection for the test
    Event::unguard();
    $event = Event::create([
        'user_id' => $user->id,
        'kategori_id' => $kategori->id,
        'judul' => 'Test Event',
        'deskripsi' => 'Test Deskripsi',
        'lokasi' => 'Test Lokasi',
        'gambar' => 'test.jpg',
        'tanggal_waktu' => now(),
    ]);
    Event::reguard();

    expect($event->hasSales())->toBeFalse();

    Order::unguard();
    Order::create([
        'user_id' => $user->id,
        'event_id' => $event->id,
        'order_date' => now(),
        'total_harga' => 100000,
    ]);
    Order::reguard();

    expect($event->hasSales())->toBeTrue();
});

test('query scopes filter events correctly', function () {
    $user = User::create([
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => bcrypt('password'),
    ]);

    $kategori = Kategori::create([
        'nama' => 'Test Category',
    ]);

    Event::unguard();
    // 1. Upcoming event (tomorrow)
    $upcoming = Event::create([
        'user_id' => $user->id,
        'kategori_id' => $kategori->id,
        'judul' => 'Upcoming Event',
        'deskripsi' => 'Desc',
        'lokasi' => 'Loc',
        'gambar' => 'test.jpg',
        'tanggal_waktu' => now()->addDay(),
    ]);

    // 2. Ongoing event (1 hour ago)
    $ongoing = Event::create([
        'user_id' => $user->id,
        'kategori_id' => $kategori->id,
        'judul' => 'Ongoing Event',
        'deskripsi' => 'Desc',
        'lokasi' => 'Loc',
        'gambar' => 'test.jpg',
        'tanggal_waktu' => now()->subHours(1),
    ]);

    // 3. Completed event (4 hours ago)
    $completed = Event::create([
        'user_id' => $user->id,
        'kategori_id' => $kategori->id,
        'judul' => 'Completed Event',
        'deskripsi' => 'Desc',
        'lokasi' => 'Loc',
        'gambar' => 'test.jpg',
        'tanggal_waktu' => now()->subHours(4),
    ]);
    Event::reguard();

    // Check Upcoming scope
    $upcomingIds = Event::upcoming()->pluck('id')->toArray();
    expect($upcomingIds)->toContain($upcoming->id);
    expect($upcomingIds)->not->toContain($ongoing->id);
    expect($upcomingIds)->not->toContain($completed->id);

    // Check Ongoing scope
    $ongoingIds = Event::ongoing()->pluck('id')->toArray();
    expect($ongoingIds)->toContain($ongoing->id);
    expect($ongoingIds)->not->toContain($upcoming->id);
    expect($ongoingIds)->not->toContain($completed->id);

    // Check Completed scope
    $completedIds = Event::completed()->pluck('id')->toArray();
    expect($completedIds)->toContain($completed->id);
    expect($completedIds)->not->toContain($upcoming->id);
    expect($completedIds)->not->toContain($ongoing->id);
});

test('image_url accessor returns valid values', function () {
    // 1. Valid URL
    $event1 = new Event(['gambar' => 'https://example.com/some-image.jpg']);
    expect($event1->image_url)->toBe('https://example.com/some-image.jpg');

    // 2. File doesn't exist in storage -> returns fallback
    $event2 = new Event(['gambar' => 'nonexistent-file.jpg']);
    expect($event2->image_url)->toBe(asset('storage/konser.jpg'));

    // 3. Empty or null -> returns fallback
    $event3 = new Event(['gambar' => '']);
    expect($event3->image_url)->toBe(asset('storage/konser.jpg'));
});


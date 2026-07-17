<?php

use App\Models\Event;
use App\Models\Kategori;
use App\Models\User;
use App\Http\Controllers\EventController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;

test('event controller index filters and paginates correctly', function () {
    $user = User::create([
        'name' => 'User',
        'email' => 'user_ctrl@example.com',
        'password' => bcrypt('password'),
    ]);

    $kategori1 = Kategori::create(['nama' => 'Seminar']);
    $kategori2 = Kategori::create(['nama' => 'Konser']);

    Event::unguard();
    $event1 = Event::create([
        'user_id' => $user->id,
        'kategori_id' => $kategori1->id,
        'judul' => 'Belajar Laravel',
        'deskripsi' => 'Deskripsi',
        'lokasi' => 'Bandung',
        'gambar' => 'test.jpg',
        'tanggal_waktu' => now()->addDays(2),
    ]);

    $event2 = Event::create([
        'user_id' => $user->id,
        'kategori_id' => $kategori2->id,
        'judul' => 'Konser Rock',
        'deskripsi' => 'Deskripsi',
        'lokasi' => 'Jakarta',
        'gambar' => 'test.jpg',
        'tanggal_waktu' => now()->addDays(1),
    ]);
    Event::reguard();

    // Create a request mock
    $request = Request::create('/events', 'GET', [
        'search' => 'Laravel',
    ]);
    $request->headers->set('Accept', 'application/json');

    $controller = new EventController();
    $response = $controller->index($request);

    $data = json_decode($response->getContent(), true);

    expect($data['data'])->toHaveCount(1);
    expect($data['data'][0]['judul'])->toBe('Belajar Laravel');

    // Test category filter
    $request2 = Request::create('/events', 'GET', [
        'kategori_id' => $kategori2->id,
    ]);
    $request2->headers->set('Accept', 'application/json');
    $response2 = $controller->index($request2);
    $data2 = json_decode($response2->getContent(), true);

    expect($data2['data'])->toHaveCount(1);
    expect($data2['data'][0]['judul'])->toBe('Konser Rock');

    // Test sorting
    $request3 = Request::create('/events', 'GET', [
        'sort' => 'desc',
    ]);
    $request3->headers->set('Accept', 'application/json');
    $response3 = $controller->index($request3);
    $data3 = json_decode($response3->getContent(), true);

    expect($data3['data'])->toHaveCount(2);
    // desc order: event1 (now+2 days) then event2 (now+1 day)
    expect($data3['data'][0]['judul'])->toBe('Belajar Laravel');
    expect($data3['data'][1]['judul'])->toBe('Konser Rock');
});

test('admin can access create event page', function () {
    User::unguard();
    $admin = User::create([
        'name' => 'Admin',
        'email' => 'admin_ctrl@example.com',
        'password' => bcrypt('password'),
        'role' => 'admin',
    ]);
    User::reguard();

    $response = $this->actingAs($admin)->get(route('admin.events.create'));

    $response->assertStatus(200);
    $response->assertViewIs('pages.admin.events.create');
    $response->assertViewHas('categories');
});

test('admin can store event with tickets and redirect', function () {
    Storage::fake('public');

    User::unguard();
    $admin = User::create([
        'name' => 'Admin',
        'email' => 'admin_ctrl2@example.com',
        'password' => bcrypt('password'),
        'role' => 'admin',
    ]);
    User::reguard();

    $kategori = Kategori::create(['nama' => 'Konser']);
    $lokasi = \App\Models\ManagementLokasi::create(['nama_lokasi' => 'Stadion Utama', 'is_active' => true]);

    $file = UploadedFile::fake()->create('event.jpg', 100);

    $response = $this->actingAs($admin)->post(route('admin.events.store'), [
        'kategori_id' => $kategori->id,
        'judul' => 'Konser Rock Spesial',
        'deskripsi' => 'Konser musik rock paling asik.',
        'lokasi_id' => $lokasi->id,
        'gambar' => $file,
        'tanggal_waktu' => now()->addDays(3)->toDateTimeString(),
        'tikets' => [
            [
                'tipe' => 'reguler',
                'harga' => 100000,
                'stok' => 100,
            ],
            [
                'tipe' => 'premium',
                'harga' => 200000,
                'stok' => 50,
            ]
        ]
    ]);

    $response->assertRedirect(route('admin.events.index'));

    $this->assertDatabaseHas('events', [
        'judul' => 'Konser Rock Spesial',
        'lokasi_id' => $lokasi->id,
    ]);

    $event = Event::where('judul', 'Konser Rock Spesial')->first();
    expect($event->tikets)->toHaveCount(2);

    // Verify image was stored
    Storage::disk('public')->assertExists($event->gambar);
});

test('admin can access edit event page', function () {
    User::unguard();
    $admin = User::create([
        'name' => 'Admin',
        'email' => 'admin_edit@example.com',
        'password' => bcrypt('password'),
        'role' => 'admin',
    ]);
    User::reguard();

    $kategori = Kategori::create(['nama' => 'Konser']);

    Event::unguard();
    $event = Event::create([
        'user_id' => $admin->id,
        'kategori_id' => $kategori->id,
        'judul' => 'Event Edit',
        'deskripsi' => 'Deskripsi',
        'lokasi' => 'Bandung',
        'gambar' => 'konser.jpg',
        'tanggal_waktu' => now()->addDays(2),
    ]);
    Event::reguard();

    $response = $this->actingAs($admin)->get(route('admin.events.edit', $event));

    $response->assertStatus(200);
    $response->assertViewIs('pages.admin.events.edit');
    $response->assertViewHas('event');
    $response->assertViewHas('categories');
    $response->assertViewHas('hasSales');
});

test('admin can update event data and tickets', function () {
    Storage::fake('public');

    User::unguard();
    $admin = User::create([
        'name' => 'Admin',
        'email' => 'admin_update@example.com',
        'password' => bcrypt('password'),
        'role' => 'admin',
    ]);
    User::reguard();

    $kategori = Kategori::create(['nama' => 'Konser']);

    Event::unguard();
    $event = Event::create([
        'user_id' => $admin->id,
        'kategori_id' => $kategori->id,
        'judul' => 'Event Asli',
        'deskripsi' => 'Deskripsi',
        'lokasi' => 'Bandung',
        'gambar' => 'konser.jpg',
        'tanggal_waktu' => now()->addDays(2),
    ]);
    Event::reguard();

    // Create two tickets initially
    $ticket1 = $event->tikets()->create(['tipe' => 'reguler', 'harga' => 100000, 'stok' => 50]);
    $ticket2 = $event->tikets()->create(['tipe' => 'premium', 'harga' => 300000, 'stok' => 10]);

    $newFile = UploadedFile::fake()->create('new_event.jpg', 100);

    // Update payload:
    // Update ticket1 (change stok), omit ticket2 (should delete it), add new ticket3
    $lokasiJakarta = \App\Models\ManagementLokasi::create(['nama_lokasi' => 'Jakarta', 'is_active' => true]);

    $response = $this->actingAs($admin)->put(route('admin.events.update', $event), [
        'kategori_id' => $kategori->id,
        'judul' => 'Event Baru',
        'deskripsi' => 'Deskripsi Baru',
        'lokasi_id' => $lokasiJakarta->id,
        'gambar' => $newFile,
        'tanggal_waktu' => now()->addDays(3)->toDateTimeString(),
        'tikets' => [
            [
                'id' => $ticket1->id,
                'tipe' => 'reguler',
                'harga' => 100000,
                'stok' => 80, // changed
            ],
            [
                'tipe' => 'premium',
                'harga' => 500000,
                'stok' => 20, // new ticket
            ]
        ]
    ]);

    $response->assertRedirect(route('admin.events.index'));

    $this->assertDatabaseHas('events', [
        'id' => $event->id,
        'judul' => 'Event Baru',
        'lokasi_id' => $lokasiJakarta->id,
    ]);

    $event->refresh();
    // Ticket2 should be deleted since no sales, Ticket1 updated, Ticket3 created. Total tickets should be 2.
    expect($event->tikets)->toHaveCount(2);
    expect($event->tikets->where('id', $ticket1->id)->first()->stok)->toBe(80);
    expect($event->tikets->where('id', $ticket2->id))->toBeEmpty();
    expect($event->gambar)->toContain('events/');
    Storage::disk('public')->assertExists($event->gambar);
});

test('admin cannot update date_time of event with sales', function () {
    User::unguard();
    $admin = User::create([
        'name' => 'Admin',
        'email' => 'admin_sales@example.com',
        'password' => bcrypt('password'),
        'role' => 'admin',
    ]);
    User::reguard();

    $kategori = Kategori::create(['nama' => 'Konser']);

    Event::unguard();
    $event = Event::create([
        'user_id' => $admin->id,
        'kategori_id' => $kategori->id,
        'judul' => 'Event With Sales',
        'deskripsi' => 'Deskripsi',
        'lokasi' => 'Bandung',
        'gambar' => 'konser.jpg',
        'tanggal_waktu' => now()->addDays(2),
    ]);
    Event::reguard();

    $ticket = $event->tikets()->create(['tipe' => 'reguler', 'harga' => 100000, 'stok' => 50]);

    // Create an order on this event (hasSales will be true)
    \App\Models\Order::unguard();
    \App\Models\Order::create([
        'user_id' => $admin->id,
        'event_id' => $event->id,
        'order_date' => now(),
        'total_harga' => 100000,
    ]);
    \App\Models\Order::reguard();

    // Try to update event by changing tanggal_waktu: should fail
    $response = $this->actingAs($admin)->put(route('admin.events.update', $event), [
        'kategori_id' => $kategori->id,
        'judul' => 'Event With Sales Changed',
        'deskripsi' => 'Deskripsi',
        'lokasi_id' => $event->lokasi_id,
        'tanggal_waktu' => now()->addDays(3)->toDateTimeString(), // changed
        'tikets' => [
            [
                'id' => $ticket->id,
                'tipe' => 'reguler',
                'harga' => 100000,
                'stok' => 50,
            ]
        ]
    ]);

    $response->assertSessionHasErrors('tanggal_waktu');
});

test('show event page loads related events', function () {
    User::unguard();
    $user = User::create([
        'name' => 'User',
        'email' => 'user_show@example.com',
        'password' => bcrypt('password'),
    ]);
    User::reguard();

    $kategori = Kategori::create(['nama' => 'Konser']);

    Event::unguard();
    $event = Event::create([
        'user_id' => $user->id,
        'kategori_id' => $kategori->id,
        'judul' => 'Event Utama',
        'deskripsi' => 'Deskripsi',
        'lokasi' => 'Bandung',
        'gambar' => 'konser.jpg',
        'tanggal_waktu' => now()->addDays(2),
    ]);

    // Related event (same category, in the future)
    $related = Event::create([
        'user_id' => $user->id,
        'kategori_id' => $kategori->id,
        'judul' => 'Event Terkait',
        'deskripsi' => 'Deskripsi',
        'lokasi' => 'Jakarta',
        'gambar' => 'konser.jpg',
        'tanggal_waktu' => now()->addDays(3),
    ]);

    // Non-related event (different category)
    $kategori2 = Kategori::create(['nama' => 'Seminar']);
    $nonRelated = Event::create([
        'user_id' => $user->id,
        'kategori_id' => $kategori2->id,
        'judul' => 'Event Lain',
        'deskripsi' => 'Deskripsi',
        'lokasi' => 'Surabaya',
        'gambar' => 'konser.jpg',
        'tanggal_waktu' => now()->addDays(4),
    ]);
    Event::reguard();

    $response = $this->get(route('events.show', $event));

    $response->assertStatus(200);
    $response->assertViewIs('events.show');
    $response->assertViewHas('event');
    $response->assertViewHas('relatedEvents');

    $relatedEvents = $response->viewData('relatedEvents');
    expect($relatedEvents)->toHaveCount(1);
    expect($relatedEvents->first()->id)->toBe($related->id);
});

test('admin can delete event without sales', function () {
    Storage::fake('public');

    User::unguard();
    $admin = User::create([
        'name' => 'Admin',
        'email' => 'admin_del@example.com',
        'password' => bcrypt('password'),
        'role' => 'admin',
    ]);
    User::reguard();

    $kategori = Kategori::create(['nama' => 'Konser']);

    Event::unguard();
    $event = Event::create([
        'user_id' => $admin->id,
        'kategori_id' => $kategori->id,
        'judul' => 'Event Hapus',
        'deskripsi' => 'Deskripsi',
        'lokasi' => 'Bandung',
        'gambar' => 'events/fake_image.jpg', // dummy stored image
        'tanggal_waktu' => now()->addDays(2),
    ]);
    Event::reguard();

    // Fake that the image exists in storage
    Storage::disk('public')->put('events/fake_image.jpg', 'fake content');

    $response = $this->actingAs($admin)->delete(route('admin.events.destroy', $event));

    $response->assertRedirect(route('admin.events.index'));
    $response->assertSessionHas('success');

    $this->assertDatabaseMissing('events', ['id' => $event->id]);
    Storage::disk('public')->assertMissing('events/fake_image.jpg');
});

test('admin cannot delete event with sales', function () {
    User::unguard();
    $admin = User::create([
        'name' => 'Admin',
        'email' => 'admin_del_sales@example.com',
        'password' => bcrypt('password'),
        'role' => 'admin',
    ]);
    User::reguard();

    $kategori = Kategori::create(['nama' => 'Konser']);

    Event::unguard();
    $event = Event::create([
        'user_id' => $admin->id,
        'kategori_id' => $kategori->id,
        'judul' => 'Event Hapus Gagal',
        'deskripsi' => 'Deskripsi',
        'lokasi' => 'Bandung',
        'gambar' => 'konser.jpg',
        'tanggal_waktu' => now()->addDays(2),
    ]);
    Event::reguard();

    // Create an order on this event
    \App\Models\Order::unguard();
    \App\Models\Order::create([
        'user_id' => $admin->id,
        'event_id' => $event->id,
        'order_date' => now(),
        'total_harga' => 100000,
    ]);
    \App\Models\Order::reguard();

    $response = $this->actingAs($admin)->delete(route('admin.events.destroy', $event));

    $response->assertRedirect(route('admin.events.index'));
    $response->assertSessionHas('error');

    $this->assertDatabaseHas('events', ['id' => $event->id]);
});

test('admin can access index event page', function () {
    User::unguard();
    $admin = User::create([
        'name' => 'Admin',
        'email' => 'admin_index@example.com',
        'password' => bcrypt('password'),
        'role' => 'admin',
    ]);
    User::reguard();

    $response = $this->actingAs($admin)->get(route('admin.events.index'));

    $response->assertStatus(200);
    $response->assertViewIs('pages.admin.events.index');
    $response->assertViewHas('events');
    $response->assertViewHas('categories');
});

test('admin can export events to csv', function () {
    User::unguard();
    $admin = User::create([
        'name' => 'Admin',
        'email' => 'admin_export@example.com',
        'password' => bcrypt('password'),
        'role' => 'admin',
    ]);
    User::reguard();

    $kategori = Kategori::create(['nama' => 'Konser']);

    Event::unguard();
    $event = Event::create([
        'user_id' => $admin->id,
        'kategori_id' => $kategori->id,
        'judul' => 'Event Export Test',
        'deskripsi' => 'Deskripsi',
        'lokasi' => 'Bandung',
        'gambar' => 'konser.jpg',
        'tanggal_waktu' => now()->addDays(2),
    ]);
    Event::reguard();

    $response = $this->actingAs($admin)->get(route('admin.events.export'));

    $response->assertStatus(200);
    $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
    
    $disposition = $response->headers->get('Content-Disposition');
    expect($disposition)->toContain('attachment; filename="events_export_');
    expect($disposition)->toContain('.csv"');
    
    $content = $response->streamedContent();
    expect($content)->toContain('Event Export Test');
});

test('admin can bulk delete events without sales', function () {
    User::unguard();
    $admin = User::create([
        'name' => 'Admin',
        'email' => 'admin_bulk_del@example.com',
        'password' => bcrypt('password'),
        'role' => 'admin',
    ]);
    User::reguard();

    $kategori = Kategori::create(['nama' => 'Konser']);

    Event::unguard();
    $event1 = Event::create([
        'user_id' => $admin->id,
        'kategori_id' => $kategori->id,
        'judul' => 'Event Bulk 1',
        'deskripsi' => 'Deskripsi',
        'lokasi' => 'Bandung',
        'gambar' => 'konser.jpg',
        'tanggal_waktu' => now()->addDays(2),
    ]);
    $event2 = Event::create([
        'user_id' => $admin->id,
        'kategori_id' => $kategori->id,
        'judul' => 'Event Bulk 2',
        'deskripsi' => 'Deskripsi',
        'lokasi' => 'Jakarta',
        'gambar' => 'konser.jpg',
        'tanggal_waktu' => now()->addDays(2),
    ]);
    Event::reguard();

    $response = $this->actingAs($admin)->post(route('admin.events.bulk-destroy'), [
        'event_ids' => [$event1->id, $event2->id]
    ]);

    $response->assertRedirect(route('admin.events.index'));
    $response->assertSessionHas('success');

    $this->assertDatabaseMissing('events', ['id' => $event1->id]);
    $this->assertDatabaseMissing('events', ['id' => $event2->id]);
});

test('admin cannot bulk delete events with sales', function () {
    User::unguard();
    $admin = User::create([
        'name' => 'Admin',
        'email' => 'admin_bulk_sales@example.com',
        'password' => bcrypt('password'),
        'role' => 'admin',
    ]);
    User::reguard();

    $kategori = Kategori::create(['nama' => 'Konser']);

    Event::unguard();
    $event1 = Event::create([
        'user_id' => $admin->id,
        'kategori_id' => $kategori->id,
        'judul' => 'Event Bulk Sales 1',
        'deskripsi' => 'Deskripsi',
        'lokasi' => 'Bandung',
        'gambar' => 'konser.jpg',
        'tanggal_waktu' => now()->addDays(2),
    ]);
    $event2 = Event::create([
        'user_id' => $admin->id,
        'kategori_id' => $kategori->id,
        'judul' => 'Event Bulk Sales 2',
        'deskripsi' => 'Deskripsi',
        'lokasi' => 'Jakarta',
        'gambar' => 'konser.jpg',
        'tanggal_waktu' => now()->addDays(2),
    ]);
    Event::reguard();

    // Create an order on event1
    \App\Models\Order::unguard();
    \App\Models\Order::create([
        'user_id' => $admin->id,
        'event_id' => $event1->id,
        'order_date' => now(),
        'total_harga' => 100000,
    ]);
    \App\Models\Order::reguard();

    $response = $this->actingAs($admin)->post(route('admin.events.bulk-destroy'), [
        'event_ids' => [$event1->id, $event2->id]
    ]);

    $response->assertRedirect(route('admin.events.index'));
    $response->assertSessionHas('error'); // skipped event1 message
    $response->assertSessionHas('success'); // deleted event2 message

    $this->assertDatabaseHas('events', ['id' => $event1->id]);
    $this->assertDatabaseMissing('events', ['id' => $event2->id]);
});

test('admin can clone event with tickets', function () {
    User::unguard();
    $admin = User::create([
        'name' => 'Admin',
        'email' => 'admin_clone@example.com',
        'password' => bcrypt('password'),
        'role' => 'admin',
    ]);
    User::reguard();

    $kategori = Kategori::create(['nama' => 'Konser']);

    Event::unguard();
    $event = Event::create([
        'user_id' => $admin->id,
        'kategori_id' => $kategori->id,
        'judul' => 'Event Asli Clone',
        'deskripsi' => 'Deskripsi',
        'lokasi' => 'Bandung',
        'gambar' => 'konser.jpg',
        'tanggal_waktu' => now()->addDays(2),
    ]);
    Event::reguard();

    $event->tikets()->create(['tipe' => 'reguler', 'harga' => 100000, 'stok' => 50]);

    $response = $this->actingAs($admin)->post(route('admin.events.clone', $event));

    $response->assertRedirect(route('admin.events.index'));

    $clonedEvent = Event::where('judul', '[Clone] Event Asli Clone')->first();
    expect($clonedEvent)->not->toBeNull();
    expect($clonedEvent->tikets)->toHaveCount(1);
    expect($clonedEvent->tikets->first()->tipe)->toBe('reguler');
});

test('event status history is recorded on creation and update', function () {
    User::unguard();
    $admin = User::create([
        'name' => 'Admin',
        'email' => 'admin_history@example.com',
        'password' => bcrypt('password'),
        'role' => 'admin',
    ]);
    User::reguard();

    $kategori = Kategori::create(['nama' => 'Konser']);

    Event::unguard();
    // 1. Check history on creation
    $event = Event::create([
        'user_id' => $admin->id,
        'kategori_id' => $kategori->id,
        'judul' => 'Event History Test',
        'deskripsi' => 'Deskripsi',
        'lokasi' => 'Bandung',
        'gambar' => 'konser.jpg',
        'tanggal_waktu' => now()->addDays(2), // status: Upcoming
    ]);
    Event::reguard();

    expect($event->statusHistories)->toHaveCount(1);
    expect($event->statusHistories->first()->old_status)->toBeNull();
    expect($event->statusHistories->first()->new_status)->toBe('Upcoming');

    // 2. Check history on update of tanggal_waktu to past (Completed)
    $event->update([
        'tanggal_waktu' => now()->subHours(5)
    ]);

    $event->refresh();
    expect($event->statusHistories)->toHaveCount(2);
    expect($event->statusHistories->last()->old_status)->toBe('Upcoming');
    expect($event->statusHistories->last()->new_status)->toBe('Completed');
});

test('admin can access locations index page', function () {
    User::unguard();
    $admin = User::create([
        'name' => 'Admin User',
        'email' => 'admin_loc@example.com',
        'password' => bcrypt('password'),
        'role' => 'admin',
    ]);
    User::reguard();

    $response = $this->actingAs($admin)->get(route('locations.index'));
    $response->assertStatus(200);
});

test('admin can store a new location', function () {
    User::unguard();
    $admin = User::create([
        'name' => 'Admin User',
        'email' => 'admin_loc_store@example.com',
        'password' => bcrypt('password'),
        'role' => 'admin',
    ]);
    User::reguard();

    $response = $this->actingAs($admin)->post(route('locations.store'), [
        'nama_lokasi' => 'Stadion Baru',
        'is_active' => true,
    ]);

    $response->assertRedirect(route('locations.index'));
    $this->assertDatabaseHas('management_lokasis', [
        'nama_lokasi' => 'Stadion Baru',
        'is_active' => 1,
    ]);
});

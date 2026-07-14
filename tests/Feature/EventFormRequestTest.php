<?php

use App\Http\Requests\EventFormRequest;
use App\Models\Kategori;
use Illuminate\Support\Facades\Validator;

test('validation passes with valid data', function () {
    $kategori = Kategori::create([
        'nama' => 'Konser',
    ]);

    $rules = (new EventFormRequest())->rules();

    $data = [
        'judul' => 'Konser Musik Keren',
        'deskripsi' => 'Deskripsi konser musik keren.',
        'lokasi' => 'Stadion Utama',
        'kategori_id' => $kategori->id,
        'tanggal_waktu' => now()->addDays(2)->toDateTimeString(),
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
    ];

    $validator = Validator::make($data, $rules);

    expect($validator->passes())->toBeTrue();
});

test('validation fails with invalid data', function () {
    $rules = (new EventFormRequest())->rules();

    $data = [
        'judul' => '',
        'deskripsi' => '',
        'lokasi' => '',
        'kategori_id' => 9999, // nonexistent
        'tanggal_waktu' => now()->subDays(1)->toDateTimeString(), // in the past
        'tikets' => []
    ];

    $validator = Validator::make($data, $rules);

    expect($validator->passes())->toBeFalse();
    expect($validator->errors()->keys())->toContain(
        'judul', 'deskripsi', 'lokasi', 'kategori_id', 'tanggal_waktu', 'tikets'
    );
});

test('custom messages are in Indonesian', function () {
    $request = new EventFormRequest();
    $rules = $request->rules();
    $messages = $request->messages();

    $data = [
        'judul' => '',
    ];

    $validator = Validator::make($data, $rules, $messages);

    expect($validator->passes())->toBeFalse();
    expect($validator->errors()->first('judul'))->toBe('Judul event wajib diisi.');
});

test('authorization passes for admin', function () {
    \App\Models\User::unguard();
    $admin = \App\Models\User::create([
        'name' => 'Admin User',
        'email' => 'admin_test@example.com',
        'password' => bcrypt('password'),
        'role' => 'admin',
    ]);
    \App\Models\User::reguard();

    $request = new EventFormRequest();
    $request->setUserResolver(fn () => $admin);

    expect($request->authorize())->toBeTrue();
});

test('authorization fails for regular user or guest', function () {
    \App\Models\User::unguard();
    $user = \App\Models\User::create([
        'name' => 'Regular User',
        'email' => 'user_test@example.com',
        'password' => bcrypt('password'),
        'role' => 'user',
    ]);
    \App\Models\User::reguard();

    $request = new EventFormRequest();
    $request->setUserResolver(fn () => $user);

    expect($request->authorize())->toBeFalse();

    $guestRequest = new EventFormRequest();
    $guestRequest->setUserResolver(fn () => null);

    expect($guestRequest->authorize())->toBeFalse();
});


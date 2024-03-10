<?php

use Illuminate\Support\Facades\Event;
use Statamic\Facades;

it('gets users', function () {
    $user = tap(Facades\User::make()->email('test@test2.com'))->save();

    $this->actingAs(makeUser());

    $response = $this->get(route('private.users.index'));

    $response->assertOk();

    $json = $response->json();

    $this->assertCount(2, $json['data']);

    $this->assertSame('test@test2.com|test@test.com', collect($json['data'])->pluck('email')->join('|'));

    $response = $this->get(route('private.users.index', ['limit' => 1]));
    $json = $response->json();

    $this->assertCount(1, $json['data']);
});

it('gets individual users', function () {
    $user = tap(Facades\User::make()->email('test@test2.com'))->save();

    $this->actingAs(makeUser());

    $response = $this->get(route('private.users.show', ['id' => $user->id()]));

    $response->assertOk();

    $json = $response->json();

    $this->assertSame($user->id(), array_get($json, 'data.id'));

    $this->get(route('private.users.show', ['id' => 'none']))
        ->assertNotFound();
});

it('deletes a user', function () {
    $user = tap(Facades\User::make()->email('test@test2.com'))->save();

    $this->actingAs(makeUser());

    $this->assertCount(2, Facades\User::all());

    $response = $this->delete(route('private.users.destroy', ['id' => $user->id()]));

    $response->assertStatus(204);

    $this->assertCount(1, Facades\User::all());
});

it('creates a user', function () {
    Event::fake();

    $this->actingAs(makeUser());

    $this->assertCount(1, Facades\User::all());

    $response = $this->post(route('private.users.store'), [
        'email' => 'test@test3.com',
        'super' => false,
    ]);

    $response->assertOk();

    $json = $response->json();

    $this->assertSame('test@test3.com', array_get($json, 'data.email'));
});

it('returns validation errors when creating a user', function () {
    Event::fake();

    $this->actingAs(makeUser());

    $this->assertCount(1, Facades\User::all());

    $response = $this->post(route('private.users.store'), [
        'super' => false,
    ]);

    $response->assertStatus(422);
    $response->assertSee('The Email Address field is required');
});

it('updates a user', function () {
    Event::fake();

    $this->actingAs(makeUser());

    $user = tap(Facades\User::make()->email('test@test2.com')->set('name', 'Test'))->save();

    $this->assertCount(2, Facades\User::all());

    $this->assertSame($user->name(), 'Test');

    $response = $this->patch(route('private.users.update', ['id' => $user->id()]), [
        'name' => 'Tester',
    ]);

    $response->assertOk();

    $response->json();

    $this->assertSame('Tester', Facades\User::find($user->id())->name());
});

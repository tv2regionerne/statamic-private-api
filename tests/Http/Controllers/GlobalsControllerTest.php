<?php

use Illuminate\Support\Facades\Event;
use Statamic\Facades;

it('gets globals', function () {
    $global1 = tap(Facades\GlobalSet::make('test'))->save();
    $global2 = tap(Facades\GlobalSet::make('blog'))->save();
    $global3 = tap(Facades\GlobalSet::make('pages'))->save();

    $this->actingAs(makeUser());

    $response = $this->get(route('private.globals.index'));

    $response->assertOk();

    $json = $response->json();

    $this->assertCount(3, $json['data']);

    $this->assertSame('test|blog|pages', collect($json['data'])->pluck('handle')->join('|'));

    $response = $this->get(route('private.globals.index', ['limit' => 2]));
    $json = $response->json();

    $this->assertCount(2, $json['data']);
});

it('respects global restrictions', function () {
    app('config')->set('private-api.resources.globals', ['none' => true]);

    $form = tap(Facades\GlobalSet::make('test'))->save();

    $this->actingAs(makeUser());

    $this->get(route('private.globals.show', ['globalset' => 'test']))
        ->assertNotFound();

    app('config')->set('private-api.resources.globals', ['test' => true]);

    $this->get(route('private.globals.show', ['globalset' => 'test']))
        ->assertOk();
});

it('updates a global', function () {
    $global = tap(Facades\GlobalSet::make('test')->title('Test'))->save();

    $this->actingAs(makeUser());

    $this->assertSame('Test', $global->title());

    $response = $this->patch(route('private.globals.update', ['globalset' => $global->handle()]), [
        'title' => 'new title',
    ]);

    $response->assertOk();

    $json = $response->json();

    $this->assertSame('new title', array_get($json, 'data.title'));
    $this->assertSame('new title', Facades\GlobalSet::find('test')->title());
});

it('deletes a global', function () {
    $global = tap(Facades\GlobalSet::make('test'))->save();

    $this->actingAs(makeUser());

    $this->assertCount(1, Facades\GlobalSet::all());

    $response = $this->delete(route('private.globals.destroy', ['globalset' => $global->handle()]));

    $response->assertStatus(204);

    $this->assertCount(0, Facades\GlobalSet::all());
});

it('creates a global', function () {
    Event::fake();

    $this->actingAs(makeUser());

    $this->assertCount(0, Facades\GlobalSet::all());

    $response = $this->post(route('private.globals.store'), [
        'handle' => 'test',
        'title' => 'test',
    ]);

    $response->assertOk();

    $json = $response->json();

    $this->assertSame('test', array_get($json, 'data.title'));
    $this->assertSame('test', Facades\GlobalSet::all()->first()->title());
});

it('returns validation errors when creating a global', function () {
    Facades\GlobalSet::all()->each->delete();

    Event::fake();

    $this->actingAs(makeUser());

    $this->assertCount(0, Facades\GlobalSet::all());

    $response = $this->post(route('private.globals.store'), [
        'something' => 'test',
    ]);

    $response->assertStatus(422);
    $response->assertSee('The title field is required');
});

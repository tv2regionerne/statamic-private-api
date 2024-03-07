<?php

use Illuminate\Support\Facades\Event;
use Statamic\Facades;

it('gets containers', function () {
    $container1 = tap(Facades\AssetContainer::make('test'))->save();
    $container2 = tap(Facades\AssetContainer::make('blog'))->save();
    $container3 = tap(Facades\AssetContainer::make('pages'))->save();

    $this->actingAs(makeUser());

    $response = $this->get(route('private.asset-containers.index'));

    $response->assertOk();

    $json = $response->json();

    $this->assertCount(3, $json['data']);

    $this->assertSame('test|blog|pages', collect($json['data'])->pluck('handle')->join('|'));

    $response = $this->get(route('private.asset-containers.index', ['limit' => 2]));
    $json = $response->json();

    $this->assertCount(2, $json['data']);
});

it('respects container restrictions', function () {
    app('config')->set('private-api.resources.assets', ['none' => true]);

    $container = tap(Facades\AssetContainer::make('test'))->save();

    $this->actingAs(makeUser());

    $this->get(route('private.asset-containers.show', ['asset_container' => 'test']))
        ->assertNotFound();

    app('config')->set('private-api.resources.assets', ['test' => true]);

    $this->get(route('private.asset-containers.show', ['asset_container' => 'test']))
        ->assertOk();
});

it('gets updates a container', function () {
    $container = tap(Facades\AssetContainer::make('test')->title('Test')->disk('test'))->save();

    $this->actingAs(makeUser());

    $this->assertSame('Test', $container->title());

    $response = $this->patch(route('private.asset-containers.update', ['asset_container' => $container->handle()]), [
        'title' => 'new title',
    ]);

    $response->assertOk();

    $json = $response->json();

    $this->assertSame('new title', array_get($json, 'data.title'));
    $this->assertSame('new title', $container->title());
});

it('gets deletes a container', function () {
    $container = tap(Facades\AssetContainer::make('test'))->save();

    $this->actingAs(makeUser());

    $this->assertCount(1, Facades\AssetContainer::all());

    $response = $this->delete(route('private.asset-containers.destroy', ['asset_container' => $container->handle()]));

    $response->assertOk();

    $this->assertCount(0, Facades\AssetContainer::all());
});

it('receives validation errors when incorrectly creating a container', function () {
    Event::fake();

    $this->actingAs(makeUser());

    $this->assertCount(0, Facades\AssetContainer::all());

    $response = $this->post(route('private.asset-containers.store'), [
        'handle' => 'test',
        'title' => 'test',
    ]);

    $response->assertStatus(422);
    $response->assertSee('The Disk field is required');
});

it('creates a container', function () {
    Event::fake();

    $this->actingAs(makeUser());

    $this->assertCount(0, Facades\AssetContainer::all());

    $response = $this->post(route('private.asset-containers.store'), [
        'handle' => 'test',
        'title' => 'test',
        'disk' => 'test',
    ]);

    $response->assertOk();

    $json = $response->json();

    $this->assertSame('test', array_get($json, 'data.title'));
    $this->assertSame('test', Facades\AssetContainer::all()->first()->title());
});

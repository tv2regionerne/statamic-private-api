<?php

use Illuminate\Support\Facades\Event;
use Statamic\Facades;

it('gets navs', function () {
    $nav1 = tap(Facades\Nav::make('test'))->save();
    $nav2 = tap(Facades\Nav::make('blog'))->save();
    $nav3 = tap(Facades\Nav::make('pages'))->save();

    $this->actingAs(makeUser());

    $response = $this->get(route('private.navs.index'));

    $response->assertOk();

    $json = $response->json();

    $this->assertCount(3, $json['data']);

    $this->assertSame('test|blog|pages', collect($json['data'])->pluck('handle')->join('|'));

    $response = $this->get(route('private.navs.index', ['limit' => 2]));
    $json = $response->json();

    $this->assertCount(2, $json['data']);
});

it('respects nav restrictions', function () {
    app('config')->set('private-api.resources.navs', ['none' => true]);

    $nav = tap(Facades\Nav::make('test'))->save();

    $this->actingAs(makeUser());

    $this->get(route('private.navs.show', ['nav' => 'test']))
        ->assertNotFound();

    app('config')->set('private-api.resources.navs', ['test' => true]);

    $this->get(route('private.navs.show', ['nav' => 'test']))
        ->assertOk();
});

it('gets updates a nav', function () {
    $nav = tap(Facades\Nav::make('test')->title('Test'))->save();

    $this->actingAs(makeUser());

    $this->assertSame('Test', $nav->title());

    $response = $this->patch(route('private.navs.update', ['nav' => $nav->handle()]), [
        'title' => 'new title',
    ]);

    $response->assertOk();

    $json = $response->json();

    $this->assertSame('new title', array_get($json, 'data.title'));
    $this->assertSame('new title', $nav->title());
});

it('gets deletes a nav', function () {
    $nav = tap(Facades\Nav::make('test'))->save();

    $this->actingAs(makeUser());

    $this->assertCount(1, Facades\Nav::all());

    $response = $this->delete(route('private.navs.destroy', ['nav' => $nav->handle()]));

    $response->assertOk();

    $this->assertCount(0, Facades\Nav::all());
});

it('creates a nav', function () {
    Event::fake();

    $this->actingAs(makeUser());

    $this->assertCount(0, Facades\Nav::all());

    $response = $this->post(route('private.navs.store'), [
        'handle' => 'test',
        'title' => 'test',
    ]);

    $response->assertOk();

    $json = $response->json();

    $this->assertSame('test', array_get($json, 'data.title'));
    $this->assertSame('test', Facades\Nav::all()->first()->title());
});

it('returns validation errors when creating a nav', function () {
    Event::fake();

    $this->actingAs(makeUser());

    $this->assertCount(0, Facades\Nav::all());

    $response = $this->post(route('private.navs.store'), [
        'something' => 'test',
    ]);

    $response->assertStatus(422);
    $response->assertSee('The title field is required');
});

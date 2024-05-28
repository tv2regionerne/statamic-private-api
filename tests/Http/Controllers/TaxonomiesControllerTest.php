<?php

use Illuminate\Support\Facades\Event;
use Statamic\Facades;

it('gets taxonomies', function () {
    $taxonomy1 = tap(Facades\Taxonomy::make('test'))->save();
    $taxonomy2 = tap(Facades\Taxonomy::make('blog'))->save();
    $taxonomy3 = tap(Facades\Taxonomy::make('pages'))->save();

    $this->actingAs(makeUser());

    $response = $this->get(route('private.taxonomies.index'));

    $response->assertOk();

    $json = $response->json();

    $this->assertCount(3, $json['data']);

    $this->assertSame('test|blog|pages', collect($json['data'])->pluck('handle')->join('|'));

    $response = $this->get(route('private.taxonomies.index', ['limit' => 2]));
    $json = $response->json();

    $this->assertCount(2, $json['data']);
});

it('respects taxonomy restrictions', function () {
    app('config')->set('private-api.resources.taxonomies', ['none' => true]);

    $taxonomy = tap(Facades\Taxonomy::make('test'))->save();

    $this->actingAs(makeUser());

    $this->get(route('private.taxonomies.show', ['taxonomy' => 'test']))
        ->assertNotFound();

    app('config')->set('private-api.resources.taxonomies', ['test' => true]);

    $this->get(route('private.taxonomies.show', ['taxonomy' => 'test']))
        ->assertOk();
});

it('gets updates a taxonomy', function () {
    $taxonomy = tap(Facades\Taxonomy::make('test')->title('Test'))->save();

    $this->actingAs(makeUser());

    $this->assertSame('Test', $taxonomy->title());

    $response = $this->patch(route('private.taxonomies.update', ['taxonomy' => $taxonomy->handle()]), [
        'title' => 'new title',
    ]);

    $response->assertOk();

    $json = $response->json();

    $this->assertSame('new title', \Statamic\Support\Arr::get($json, 'data.title'));
    $this->assertSame('new title', $taxonomy->title());
});

it('gets deletes a taxonomy', function () {
    $taxonomy = tap(Facades\Taxonomy::make('test'))->save();

    $this->actingAs(makeUser());

    $this->assertCount(1, Facades\Taxonomy::all());

    $response = $this->delete(route('private.taxonomies.destroy', ['taxonomy' => $taxonomy->handle()]));

    $response->assertOk();

    $this->assertCount(0, Facades\Taxonomy::all());
});

it('creates a taxonomy', function () {
    Event::fake();

    $this->actingAs(makeUser());

    $this->assertCount(0, Facades\Taxonomy::all());

    $response = $this->post(route('private.taxonomies.store'), [
        'handle' => 'test',
        'title' => 'test',
    ]);

    $response->assertOk();

    $json = $response->json();

    $this->assertSame('test', \Statamic\Support\Arr::get($json, 'data.title'));
    $this->assertSame('test', Facades\Taxonomy::all()->first()->title());
});

it('returns validation errors when creating a taxonomy', function () {
    Event::fake();

    $this->actingAs(makeUser());

    $this->assertCount(0, Facades\Taxonomy::all());

    $response = $this->post(route('private.taxonomies.store'), [
        'something' => 'test',
    ]);

    $response->assertStatus(422);
    $response->assertSee('The title field is required');
});

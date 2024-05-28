<?php

use Illuminate\Support\Facades\Event;
use Statamic\Facades;

it('gets terms', function () {
    $taxonomy = tap(Facades\Taxonomy::make('test'))->save();

    $term1 = tap(Facades\Term::make()->taxonomy($taxonomy)->inDefaultLocale()->slug('test1')->data([]))->save();
    $term2 = tap(Facades\Term::make()->taxonomy($taxonomy)->inDefaultLocale()->slug('test2')->data([]))->save();
    $term3 = tap(Facades\Term::make()->taxonomy($taxonomy)->inDefaultLocale()->slug('test3')->data([]))->save();

    $this->actingAs(makeUser());

    $response = $this->get(route('private.taxonomies.terms.index', ['taxonomy' => $taxonomy->handle()]));

    $response->assertOk();

    $json = $response->json();

    $this->assertCount(3, $json['data']);

    $this->assertSame('test1|test2|test3', collect($json['data'])->pluck('slug')->join('|'));

    $response = $this->get(route('private.taxonomies.terms.index', ['taxonomy' => $taxonomy->handle(), 'limit' => 2]));
    $json = $response->json();

    $this->assertCount(2, $json['data']);
});

it('doesnt get terms from a missing taxonony', function () {
    $this->actingAs(makeUser());

    $this->get(route('private.taxonomies.terms.index', ['taxonomy' => 'not-found']))
        ->assertNotFound();
});

it('respects taxonomy restrictions', function () {
    app('config')->set('private-api.resources.taxonomies', ['none' => true]);

    $collection = tap(Facades\Taxonomy::make('test'))->save();

    $this->actingAs(makeUser());

    $this->get(route('private.taxonomies.terms.index', ['taxonomy' => 'test']))
        ->assertNotFound();

    app('config')->set('private-api.resources.taxonomies', ['test' => true]);

    $this->get(route('private.taxonomies.terms.index', ['taxonomy' => 'test']))
        ->assertOk();
});

it('gets individual terms', function () {
    $taxonomy = tap(Facades\Taxonomy::make('test'))->save();

    $term1 = tap(Facades\Term::make()->taxonomy($taxonomy)->inDefaultLocale()->slug('test1')->data([]))->save();

    $this->actingAs(makeUser());

    $response = $this->get(route('private.taxonomies.terms.show', ['taxonomy' => $taxonomy->handle(), 'term' => $term1->slug()]));

    $response->assertOk();

    $json = $response->json();

    $this->assertSame($term1->id(), \Statamic\Support\Arr::get($json, 'data.id'));

    $this->get(route('private.taxonomies.terms.show', ['taxonomy' => $taxonomy->handle(), 'term' => 'none']))
        ->assertNotFound();
});

it('gets updates an term', function () {
    $taxonomy = tap(Facades\Taxonomy::make('test'))->save();

    $term1 = tap(Facades\Term::make()->taxonomy($taxonomy)->inDefaultLocale()->slug('test1')->data([]))->save();

    $this->actingAs(makeUser());

    $this->assertNull($term1->get('title'));

    $response = $this->patch(route('private.taxonomies.terms.update', ['taxonomy' => $taxonomy->handle(), 'term' => $term1->slug()]), [
        'title' => 'test',
    ]);

    $response->assertOk();

    $json = $response->json();

    $this->assertSame('test', \Statamic\Support\Arr::get($json, 'data.title'));
    $this->assertSame('test', $term1->fresh()->get('title'));
});

it('gets deletes an entry', function () {
    $taxonomy = tap(Facades\Taxonomy::make('test'))->save();

    $term1 = tap(Facades\Term::make()->taxonomy($taxonomy)->inDefaultLocale()->slug('test1')->data([]))->save();

    $this->actingAs(makeUser());

    $this->assertCount(1, Facades\Term::all());

    $response = $this->delete(route('private.taxonomies.terms.destroy', ['taxonomy' => $taxonomy->handle(), 'term' => $term1->slug()]));

    $response->assertStatus(204);

    $this->assertCount(0, Facades\Term::all());
});

it('creates a term', function () {
    Event::fake();

    $taxonomy = tap(Facades\Taxonomy::make('test'))->save();

    $this->actingAs(makeUser());

    $this->assertCount(0, Facades\Term::all());

    $response = $this->post(route('private.taxonomies.terms.store', ['taxonomy' => $taxonomy->handle()]), [
        'title' => 'test',
        'slug' => 'test',
    ]);

    $response->assertOk();

    $json = $response->json();

    $this->assertSame('test', \Statamic\Support\Arr::get($json, 'data.title'));
    $this->assertSame('test', Facades\Term::all()->first()->get('title'));
});

it('returns validation errors when creating a term', function () {
    Event::fake();

    $taxonomy = tap(Facades\Taxonomy::make('test'))->save();

    $this->actingAs(makeUser());

    $this->assertCount(0, Facades\Term::all());

    $response = $this->post(route('private.taxonomies.terms.store', ['taxonomy' => $taxonomy->handle()]), [
        'nothing' => 'test',
    ]);

    $response->assertStatus(422);
    $response->assertSee('The Title field is required');
});

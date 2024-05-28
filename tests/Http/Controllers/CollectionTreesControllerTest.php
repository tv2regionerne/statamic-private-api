<?php

use Statamic\Facades;

it('doesnt get a tree from a missing collection', function () {
    $this->actingAs(makeUser());

    $this->get(route('private.collections.trees.show', ['collection' => 'not-found']))
        ->assertNotFound();
});

it('respects collection restrictions', function () {
    app('config')->set('private-api.resources.collections', ['none' => true]);

    $collection = tap(Facades\Collection::make('test'))->save();

    $this->actingAs(makeUser());

    $this->get(route('private.collections.trees.show', ['collection' => 'test']))
        ->assertNotFound();

    app('config')->set('private-api.resources.collections', ['test' => true]);

    $this->get(route('private.collections.trees.show', ['collection' => 'test']))
        ->assertOk();
});

it('gets a tree', function () {
    $collection = tap(Facades\Collection::make('test'))->save();

    $entry1 = tap(Facades\Entry::make()->id('test1')->collection($collection))->save();

    $this->actingAs(makeUser());

    $response = $this->get(route('private.collections.trees.show', ['collection' => $collection->handle()]));

    $response->assertOk();
    $this->assertSame($response->getContent(), '{"pages":[]}');
});

it('updates a tree', function () {
    Facades\Site::setConfig(['sites' => [
        'en' => ['url' => 'http://domain.com/', 'locale' => 'en'],
    ]]);

    $collection = tap(Facades\Collection::make('test')->structureContents(['root' => true, 'max_depth' => 3]))->save();

    $entry1 = tap(Facades\Entry::make()->id('test1')->collection($collection))->save();
    $entry2 = tap(Facades\Entry::make()->id('test2')->collection($collection))->save();

    $this->actingAs(makeUser());

    $this->assertNull($entry1->get('title'));

    $response = $this->patch(route('private.collections.trees.update', ['collection' => $collection->handle()]), [
        'pages' => [
            ['id' => 'test2', 'children' => []],
            ['id' => 'test1', 'children' => []],
        ],
        'site' => 'en',
    ]);

    $response->assertOk();

    $json = $response->json();

    $this->assertCount(2, \Statamic\Support\Arr::get($json, 'pages'));
    $this->assertSame('test2', \Statamic\Support\Arr::get($json, 'pages.0.id'));
});

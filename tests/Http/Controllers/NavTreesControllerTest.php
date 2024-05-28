<?php

use Statamic\Facades;

it('doesnt get a tree from a missing nav', function () {
    $this->actingAs(makeUser());

    $this->get(route('private.navs.trees.show', ['nav' => 'not-found']))
        ->assertNotFound();
});

it('respects nav restrictions', function () {
    app('config')->set('private-api.resources.navs', ['none' => true]);

    $nav = tap(Facades\Nav::make('test'))->save();
    $tree = tap($nav->makeTree('en'))->save();

    $this->actingAs(makeUser());

    $this->get(route('private.navs.trees.show', ['nav' => 'test']))
        ->assertNotFound();

    app('config')->set('private-api.resources.navs', ['test' => true]);

    $this->get(route('private.navs.trees.show', ['nav' => 'test']))
        ->assertOk();
});

it('gets a tree', function () {
    $nav = tap(Facades\Nav::make('test'))->save();
    $tree = tap($nav->makeTree('en'))->save();

    $this->actingAs(makeUser());

    $response = $this->get(route('private.navs.trees.show', ['nav' => $nav->handle()]));

    $response->assertOk();
    $this->assertSame($response->getContent(), '{"pages":[]}');
});

it('updates a tree', function () {
    $nav = tap(Facades\Nav::make('test'))->save();
    $tree = tap($nav->makeTree('en'))->save();

    $this->actingAs(makeUser());

    $response = $this->patch(route('private.navs.trees.update', ['nav' => $nav->handle()]), [
        'pages' => [
            ['id' => 'test2', 'url' => '/', 'children' => []],
            ['id' => 'test1', 'url' => '/test', 'children' => []],
        ],
        'site' => 'en',
    ]);

    $response->assertOk();

    $json = $response->json();

    $this->assertCount(2, \Statamic\Support\Arr::get($json, 'pages'));
    $this->assertSame('test2', \Statamic\Support\Arr::get($json, 'pages.0.id'));
});

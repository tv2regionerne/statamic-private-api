<?php

use Illuminate\Support\Facades\Event;
use Statamic\Facades;

it('gets collections', function () {
    $collection1 = tap(Facades\Collection::make('test'))->save();
    $collection2 = tap(Facades\Collection::make('blog'))->save();
    $collectio3 = tap(Facades\Collection::make('pages'))->save();
    
    $this->actingAs(makeUser());

    $response = $this->get(route('private.collections.index'));
    
    $response->assertOk();
    
    $json = $response->json();
    
    $this->assertCount(3, $json['data']);
    
    $this->assertSame('test|blog|pages', collect($json['data'])->pluck('handle')->join('|'));
    
    $response = $this->get(route('private.collections.index', ['limit' => 2]));
    $json = $response->json();
    
    $this->assertCount(2, $json['data']);
});

it('respects collection restrictions', function () {
    app('config')->set('private-api.resources.collections', ['none' => true]);
    
    $collection = tap(Facades\Collection::make('test'))->save();

    $this->actingAs(makeUser());

    $this->get(route('private.collections.show', ['collection' => 'test']))
        ->assertNotFound();   
        
    app('config')->set('private-api.resources.collections', ['test' => true]);
    
    $this->get(route('private.collections.show', ['collection' => 'test']))
        ->assertOk();   
});

it('gets updates a collection', function () {
    $collection = tap(Facades\Collection::make('test')->title('Test'))->save();
        
    $this->actingAs(makeUser());
    
    $this->assertSame('Test', $collection->title());

    $response = $this->patch(route('private.collections.update', ['collection' => $collection->handle()]), [
        'title' => 'new title',
    ]);
        
    $response->assertOk();
    
    $json = $response->json();
    
    $this->assertSame('new title', array_get($json, 'data.title'));
    $this->assertSame('new title', $collection->title());
});

it('gets deletes a collection', function () {
    $collection = tap(Facades\Collection::make('test'))->save();
        
    $this->actingAs(makeUser());
    
    $this->assertCount(1, Facades\Collection::all());

    $response = $this->delete(route('private.collections.destroy', ['collection' => $collection->handle()]));
        
    $response->assertOk();
    
    $this->assertCount(0, Facades\Collection::all());
});

it('creates a collection', function () {
    Event::fake();
        
    $this->actingAs(makeUser());
    
    $this->assertCount(0, Facades\Collection::all());
    
    $response = $this->post(route('private.collections.store'), [
        'handle' => 'test',
        'title' => 'test',
    ]);
        
    $response->assertOk();
    
    $json = $response->json();
    
    $this->assertSame('test', array_get($json, 'data.title'));
    $this->assertSame('test', Facades\Collection::all()->first()->title());
});
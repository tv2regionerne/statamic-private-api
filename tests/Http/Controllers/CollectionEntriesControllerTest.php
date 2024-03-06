<?php

use Illuminate\Support\Facades\Event;
use Statamic\Facades;

it('gets entries', function () {
    $collection = tap(Facades\Collection::make('test'))->save();
    
    $entry1 = tap(Facades\Entry::make()->id('test1')->collection($collection))->save();
    $entry2 = tap(Facades\Entry::make()->id('test2')->collection($collection))->save();
    $entry3 = tap(Facades\Entry::make()->id('test3')->collection($collection))->save();
    
    $this->actingAs(makeUser());

    $response = $this->get(route('private.collections.entries.index', ['collection' => $collection->handle()]));
    
    $response->assertOk();
    
    $json = $response->json();
    
    $this->assertCount(3, $json['data']);
    
    $this->assertSame('test1|test2|test3', collect($json['data'])->pluck('id')->join('|'));
    
    $response = $this->get(route('private.collections.entries.index', ['collection' => $collection->handle(), 'limit' => 2]));
    $json = $response->json();
    
    $this->assertCount(2, $json['data']);
});

it('doesnt get entries from a missing collection', function () {
    $this->actingAs(makeUser());

    $this->get(route('private.collections.entries.index', ['collection' => 'not-found']))
        ->assertNotFound();   
});

it('respects collection restrictions', function () {
    app('config')->set('private-api.resources.collections', ['none' => true]);
    
    $collection = tap(Facades\Collection::make('test'))->save();

    $this->actingAs(makeUser());

    $this->get(route('private.collections.entries.index', ['collection' => 'test']))
        ->assertNotFound();   
        
    app('config')->set('private-api.resources.collections', ['test' => true]);
    
    $this->get(route('private.collections.entries.index', ['collection' => 'test']))
        ->assertOk();   
});

it('gets individual entries', function () {
    $collection = tap(Facades\Collection::make('test'))->save();
    
    $entry1 = tap(Facades\Entry::make()->id('test1')->collection($collection))->save();
    
    $this->actingAs(makeUser());

    $response = $this->get(route('private.collections.entries.show', ['collection' => $collection->handle(), 'entry' => $entry1->id()]));
    
    $response->assertOk();
    
    $json = $response->json();
    
    $this->assertSame($entry1->id(), array_get($json, 'data.id'));
    
    $this->get(route('private.collections.entries.show', ['collection' => $collection->handle(), 'entry' => 'none']))
        ->assertNotFound();
});

it('gets updates an entry', function () {
    $collection = tap(Facades\Collection::make('test'))->save();
    
    $entry1 = tap(Facades\Entry::make()->id('test1')->collection($collection))->save();
    
    $this->actingAs(makeUser());
    
    $this->assertNull($entry1->get('title'));

    $response = $this->patch(route('private.collections.entries.update', ['collection' => $collection->handle(), 'entry' => $entry1->id()]), [
        'title' => 'test',
    ]);
        
    $response->assertOk();
    
    $json = $response->json();
    
    $this->assertSame('test', array_get($json, 'data.title'));
    $this->assertSame('test', $entry1->fresh()->get('title'));
});

it('gets deletes an entry', function () {
    $collection = tap(Facades\Collection::make('test'))->save();
    
    $entry1 = tap(Facades\Entry::make()->id('test1')->collection($collection))->save();
    
    $this->actingAs(makeUser());
    
    $this->assertCount(1, Facades\Entry::all());

    $response = $this->delete(route('private.collections.entries.destroy', ['collection' => $collection->handle(), 'entry' => $entry1->id()]));
        
    $response->assertStatus(204);
    
    $this->assertCount(0, Facades\Entry::all());
});

it('creates an entry', function () {
    Event::fake();

    $collection = tap(Facades\Collection::make('test'))->save();
        
    $this->actingAs(makeUser());
    
    $this->assertCount(0, Facades\Entry::all());
    
    $response = $this->post(route('private.collections.entries.store', ['collection' => $collection->handle()]), [
        'title' => 'test',
    ]);
        
    $response->assertOk();
    
    $json = $response->json();
    
    $this->assertSame('test', array_get($json, 'data.title'));
    $this->assertSame('test', Facades\Entry::all()->first()->get('title'));
});

it('returns validation errors when creating an entry', function () {
    Event::fake();

    $collection = tap(Facades\Collection::make('test'))->save();
        
    $this->actingAs(makeUser());
    
    $this->assertCount(0, Facades\Entry::all());
    
    $response = $this->post(route('private.collections.entries.store', ['collection' => $collection->handle()]), [
        'nothing' => 'test',
    ]);
        
    $response->assertStatus(422);
    $response->assertSee('The Title field is required');
});
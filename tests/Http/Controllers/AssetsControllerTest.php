<?php

use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;
use Statamic\Facades;

it('gets assets', function () {
    Storage::fake('test');

    $container = tap(Facades\AssetContainer::make('test')->disk('test'))->save();
    
    $asset1 = tap(Facades\Asset::make()->path('test1')->container($container))->save();
    $asset2 = tap(Facades\Asset::make()->path('test2')->container($container))->save();
    $asset3 = tap(Facades\Asset::make()->path('test3')->container($container))->save();
    
    $this->actingAs(makeUser());

    $response = $this->get(route('private.asset-containers.assets.index', ['asset_container' => $container->handle()]));
    
    $response->assertOk();
    
    $json = $response->json();
    
    $this->assertCount(3, $json['data']);
    
    $this->assertSame('test::test1|test::test2|test::test3', collect($json['data'])->pluck('id')->join('|'));
    
    $response = $this->get(route('private.asset-containers.assets.index', ['asset_container' => $container->handle(), 'limit' => 2]));
    $json = $response->json();
    
    $this->assertCount(2, $json['data']);
});

it('doesnt get entries from a missing container', function () {
    $this->actingAs(makeUser());

    $this->get(route('private.asset-containers.assets.index', ['asset_container' => 'not-found']))
        ->assertNotFound();   
});

it('respects container restrictions', function () {
    Storage::fake('test');

    app('config')->set('private-api.resources.assets', ['none' => true]);
    
    $container = tap(Facades\AssetContainer::make('test')->disk('test'))->save();

    $this->actingAs(makeUser());

    $this->get(route('private.asset-containers.assets.index', ['asset_container' => 'test']))
        ->assertNotFound();   
        
    app('config')->set('private-api.resources.assets', ['test' => true]);
    
    $this->get(route('private.asset-containers.assets.index', ['asset_container' => 'test']))
        ->assertOk();   
});

it('gets individual assets', function () {
    Storage::fake('test');
    Storage::disk('test')->put('test1.txt', 'contents');

    $container = tap(Facades\AssetContainer::make('test')->disk('test'))->save();
    
    $asset1 = tap(Facades\Asset::make()->path('test1.txt')->container($container))->save();
    
    $this->actingAs(makeUser());

    $response = $this->get(route('private.asset-containers.assets.show', ['asset_container' => $container->handle(), 'id' => $asset1->id()]));
    
    $response->assertOk();
    
    $json = $response->json();
    
    $this->assertSame($asset1->id(), array_get($json, 'data.id'));
    
    $this->get(route('private.asset-containers.assets.show', ['asset_container' => $container->handle(), 'id' => 'none']))
        ->assertNotFound();
});

it('updates an asset', function () {
    Storage::fake('test');
    Storage::disk('test')->put('test1.txt', 'contents');

    $container = tap(Facades\AssetContainer::make('test')->disk('test'))->save();
    
    $asset1 = tap(Facades\Asset::make()->path('test1')->container($container))->save();
    
    $this->actingAs(makeUser());
    
    $this->assertNull($asset1->get('title'));

    $response = $this->patch(route('private.asset-containers.assets.update', ['asset_container' => $container->handle(), 'id' => $asset1->id()]), [
        'title' => 'test',
    ]);
        
    $response->assertOk();
    
    $json = $response->json();
    
    $this->assertSame('test', array_get($json, 'data.title'));
    $this->assertSame('test', $asset1->fresh()->get('title'));
});

it('deletes an asset', function () {
    Storage::fake('test');
    Storage::disk('test')->put('test1.txt', 'contents');

    $container = tap(Facades\AssetContainer::make('test')->disk('test'))->save();
    
    $asset1 = tap(Facades\Asset::make()->path('test1.txt')->container($container))->save();
    
    $this->actingAs(makeUser());
    
    $this->assertCount(1, Facades\Asset::all());

    $response = $this->delete(route('private.asset-containers.assets.destroy', ['asset_container' => $container->handle(), 'id' => $asset1->id()]));
        
    $response->assertStatus(204);
    
    $this->assertCount(0, Facades\Asset::all());
    Storage::disk('test')->assertMissing('test1.txt');
});

it('creates an asset', function () {
    Event::fake();
        
    Storage::fake('test');

    $container = tap(Facades\AssetContainer::make('test')->disk('test'))->save();
        
    $this->actingAs(makeUser());
    
    $this->assertCount(0, Facades\Asset::all());
            
    $response = $this->post(route('private.asset-containers.assets.store', ['asset_container' => $container->handle()]), [
        'title' => 'test',
        'path' => 'test1.txt',
        'folder' => '/',
    ]);
        
    $response->dd()->assertOk();
    
    $json = $response->json();
    
    $this->assertSame('test', array_get($json, 'data.title'));
    $this->assertSame('test', Facades\Asset::all()->first()->get('title'));
});

it('returns validation errors when creating an asset', function () {
    Event::fake();
        
    Storage::fake('test');

    $container = tap(Facades\AssetContainer::make('test')->disk('test'))->save();
        
    $this->actingAs(makeUser());
    
    $this->assertCount(0, Facades\Asset::all());
    
    $response = $this->post(route('private.asset-containers.assets.store', ['asset_container' => $container->handle()]), [
        'nothing' => 'test',
    ]);
        
    $response->assertStatus(422);
    $response->assertSee('The folder field is required');
});
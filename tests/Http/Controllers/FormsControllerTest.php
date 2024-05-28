<?php

use Illuminate\Support\Facades\Event;
use Statamic\Facades;

it('gets forms', function () {
    $form1 = tap(Facades\Form::make('test'))->save();
    $form2 = tap(Facades\Form::make('blog'))->save();
    $form3 = tap(Facades\Form::make('pages'))->save();

    $this->actingAs(makeUser());

    $response = $this->get(route('private.forms.index'));

    $response->assertOk();

    $json = $response->json();

    $this->assertCount(3, $json['data']);

    $this->assertSame('blog|pages|test', collect($json['data'])->pluck('handle')->join('|'));

    $response = $this->get(route('private.forms.index', ['limit' => 2]));
    $json = $response->json();

    $this->assertCount(2, $json['data']);
});

it('respects form restrictions', function () {
    app('config')->set('private-api.resources.forms', ['none' => true]);

    $form = tap(Facades\Form::make('test'))->save();

    $this->actingAs(makeUser());

    $this->get(route('private.forms.show', ['form' => 'test']))
        ->assertNotFound();

    app('config')->set('private-api.resources.forms', ['test' => true]);

    $this->get(route('private.forms.show', ['form' => 'test']))
        ->assertOk();
});

it('gets updates a form', function () {
    $form = tap(Facades\Form::make('test')->title('Test'))->save();

    $this->actingAs(makeUser());

    $this->assertSame('Test', $form->title());

    $response = $this->patch(route('private.forms.update', ['form' => $form->handle()]), [
        'title' => 'new title',
    ]);

    $response->assertOk();

    $json = $response->json();

    $this->assertSame('new title', \Statamic\Support\Arr::get($json, 'data.title'));
    $this->assertSame('new title', Facades\Form::find('test')->title());
});

it('gets deletes a form', function () {
    Facades\Form::all()->each->delete();

    $form = tap(Facades\Form::make('test'))->save();

    $this->actingAs(makeUser());

    $this->assertCount(1, Facades\Form::all());

    $response = $this->delete(route('private.forms.destroy', ['form' => $form->handle()]));

    $response->assertOk();

    $this->assertCount(0, Facades\Form::all());
});

it('creates a form', function () {
    Event::fake();

    $this->actingAs(makeUser());

    $this->assertCount(0, Facades\Form::all());

    $response = $this->post(route('private.forms.store'), [
        'handle' => 'test',
        'title' => 'test',
    ]);

    $response->assertOk();

    $json = $response->json();

    $this->assertSame('test', \Statamic\Support\Arr::get($json, 'data.title'));
    $this->assertSame('test', Facades\Form::all()->first()->title());
});

it('returns validation errors when creating a form', function () {
    Facades\Form::all()->each->delete();

    Event::fake();

    $this->actingAs(makeUser());

    $this->assertCount(0, Facades\Form::all());

    $response = $this->post(route('private.forms.store'), [
        'something' => 'test',
    ]);

    $response->assertStatus(422);
    $response->assertSee('The title field is required');
});

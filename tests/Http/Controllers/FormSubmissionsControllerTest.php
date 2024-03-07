<?php

use Illuminate\Support\Facades\Event;
use Statamic\Facades;

it('gets submissions', function () {
    $form = tap(Facades\Form::make('test'))->save();
    $form->submissions()->each->delete();
    
    $submission1 = tap($form->makeSubmission()->data(['foo' => 'bar']))->save();
    $submission2 = tap($form->makeSubmission()->data(['foo' => 'foo']))->save();
    $submission3 = tap($form->makeSubmission()->data(['foo' => 'far']))->save();
    
    $this->actingAs(makeUser());

    $response = $this->get(route('private.forms.submissions.index', ['form' => $form->handle()]));
    
    $response->assertOk();
    
    $json = $response->json();
    
    $this->assertCount(3, $json['data']);
        
    $response = $this->get(route('private.forms.submissions.index', ['form' => $form->handle(), 'limit' => 2]));
    $json = $response->json();
    
    $this->assertCount(2, $json['data']);
});

it('doesnt get submission from a missing form', function () {
    $this->actingAs(makeUser());

    $this->get(route('private.forms.submissions.index', ['form' => 'not-found']))
        ->assertNotFound();   
});

it('respects form restrictions', function () {
    app('config')->set('private-api.resources.forms', ['none' => true]);
    
    $form = tap(Facades\Form::make('test'))->save();

    $this->actingAs(makeUser());

    $this->get(route('private.forms.submissions.index', ['form' => 'test']))
        ->assertNotFound();   
        
    app('config')->set('private-api.resources.forms', ['test' => true]);
    
    $this->get(route('private.forms.submissions.index', ['form' => 'test']))
        ->assertOk();   
});

it('gets individual submissions', function () {
    $form = tap(Facades\Form::make('test'))->save();
    
    $submission1 = tap($form->makeSubmission()->data(['foo' => 'bar']))->save();
    
    $this->actingAs(makeUser());

    $response = $this->get(route('private.forms.submissions.show', ['form' => $form->handle(), 'id' => $submission1->id()]));
    
    $response->assertOk();
    
    $json = $response->json();
    
    $this->assertSame($submission1->id(), array_get($json, 'data.id'));
    
    $this->get(route('private.forms.submissions.show', ['form' => $form->handle(), 'id' => 'none']))
        ->assertNotFound();
});

it('gets deletes a submission', function () {
    $form = tap(Facades\Form::make('test'))->save();
    $form->submissions()->each->delete();
    
    $submission1 = tap($form->makeSubmission()->data(['foo' => 'bar']))->save();
    
    $this->actingAs(makeUser());
    
    $this->assertCount(1, $form->submissions());

    $response = $this->delete(route('private.forms.submissions.destroy', ['form' => $form->handle(), 'id' => $submission1->id()]));
        
    $response->assertStatus(204);
    
    $this->assertCount(0, $form->submissions());
});
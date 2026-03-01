<?php

use Statamic\Facades;

it('gets variables', function () {
    Facades\Site::setSites([
        'en' => ['url' => 'http://domain.com/', 'locale' => 'en'],
    ]);

    $global1 = tap(Facades\GlobalSet::make('test'))->save();
    $vars = $global1->makeLocalization('en');
    $vars->save();

    $this->actingAs(makeUser());

    $response = $this->getJson(route('private.globals.variables.show', ['globalset' => $global1->handle(), 'site' => 'en']));
    $response->assertOk();

    $json = $response->json();

    $this->assertSame('test', $json['handle']);
});

it('updates a variable', function () {
    Facades\Site::setSites([
        'en' => ['url' => 'http://domain.com/', 'locale' => 'en'],
    ]);

    $global1 = tap(Facades\GlobalSet::make('test'))->save();
    $vars = $global1->makeLocalization('en');
    $vars->data(['test' => 'yes']);
    $vars->save();

    $response = $this->patch(route('private.globals.variables.update', ['globalset' => $global1->handle(), 'site' => 'en']), [
        'test' => 'no',
    ]);

    $response->assertOk();

    $json = $response->json();

    $this->assertSame('no', \Statamic\Support\Arr::get($json, 'data.test'));
});

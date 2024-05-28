<?php

namespace Tv2regionerne\StatamicPrivateApi\Tests;

use Statamic\Facades\Path;
use Statamic\Facades\Stache;
use Statamic\Support\Str;

trait PreventSavingStacheItemsToDisk
{
    protected $fakeStacheDirectory = __DIR__.'/__fixtures__/dev-null';

    protected function preventSavingStacheItemsToDisk()
    {
        $this->fakeStacheDirectory = Path::tidy($this->fakeStacheDirectory);

        Stache::stores()->each(function ($store) {
            $relative = Str::after(Str::after($store->directory(), 'content'), '/');
            $store->directory($this->fakeStacheDirectory.'/'.$relative);
        });
    }

    protected function deleteFakeStacheDirectory()
    {
        app('files')->deleteDirectory($this->fakeStacheDirectory);

        mkdir($this->fakeStacheDirectory);
        touch($this->fakeStacheDirectory.'/.gitkeep');
    }
}

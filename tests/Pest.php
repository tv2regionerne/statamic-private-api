<?php

uses(\Tv2regionerne\StatamicPrivateApi\Tests\TestCase::class)->in('*');

function makeUser()
{
    $user = \Statamic\Facades\User::make()
        ->email('test@test.com');

    $user->makeSuper();

    $user->save();

    return $user;
}

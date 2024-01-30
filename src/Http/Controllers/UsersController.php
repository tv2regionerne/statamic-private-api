<?php

namespace Tv2regionerne\StatamicPrivateApi\Http\Controllers;

use Illuminate\Http\Request;
use Statamic\Facades;
use Statamic\Http\Controllers\API\ApiController;
use Statamic\Http\Controllers\CP\Users\UsersController as CpController;
use Statamic\Http\Resources\API\UserResource;
use Tv2regionerne\StatamicPrivateApi\Traits\VerifiesPrivateAPI;

class UsersController extends ApiController
{
    use VerifiesPrivateAPI;

    public function index()
    {
        abort_if(! $this->resourcesAllowed('users', ''), 404);

        return app(UserResource::class)::collection(
            $this->filterSortAndPaginate(Facades\User::query())
        );
    }

    public function show($id)
    {
        abort_if(! $this->resourcesAllowed('users', ''), 404);

        if (! $user = Facades\User::find($id)) {
            abort(404);
        }

        return app(UserResource::class)::make($user);
    }

    public function store(Request $request)
    {
        abort_if(! $this->resourcesAllowed('users', ''), 404);

        return (new CpController($request))->store($request);
    }

    public function update(Request $request, $id)
    {
        abort_if(! $this->resourcesAllowed('users', ''), 404);

        if (! $user = Facades\User::find($id)) {
            abort(404);
        }

        // cp controller expects the full payload, so merge with existing values
        $mergedData = $this->mergeBlueprintAndRequestData($user->blueprint(), $user->data(), $request);

        if (! $mergedData->get('email')) {
            $mergedData = $mergedData->merge(['email' => $user->email()]);
        }

        if (! $mergedData->get('name')) {
            $mergedData = $mergedData->merge(['name' => $user->name()]);
        }

        if (! $mergedData->get('roles')) {
            $mergedData = $mergedData->merge(['roles' => $user->roles()->map->handle()->all()]);
        }

        if (! $mergedData->get('groups')) {
            $mergedData = $mergedData->merge(['groups' => $user->groups()->map->handle()->all()]);
        }

        $request->merge($mergedData->all());

        return (new CpController($request))->update($request, $id);
    }

    public function destroy($id)
    {
        abort_if(! $this->resourcesAllowed('users', ''), 404);

        return (new CpController($request))->destroy($id);
    }
}

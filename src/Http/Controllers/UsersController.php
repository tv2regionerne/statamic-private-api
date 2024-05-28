<?php

namespace Tv2regionerne\StatamicPrivateApi\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
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
        try {
            if (! $request->input('invitation')) {
                $request = $request->merge(['invitation' => ['send' => false]]);
            }

            (new CpController($request))->store($request);

            $user = Facades\User::findByEmail($request->input('email'));

            return app(UserResource::class)::make($user);
        } catch (ValidationException $e) {
            return $this->returnValidationErrors($e);
        }
    }

    public function update(Request $request, $id)
    {
        abort_if(! $this->resourcesAllowed('users', ''), 404);

        if (! $user = Facades\User::find($id)) {
            abort(404);
        }

        try {
            $data = $this->show($id)->toArray($request);

            $mergedData = collect($data)->merge($request->all());

            $request->merge($mergedData->all());

            (new CpController($request))->update($request, $id);

            return app(UserResource::class)::make($user);
        } catch (ValidationException $e) {
            return $this->returnValidationErrors($e);
        }
    }

    public function destroy(Request $request, $id)
    {
        abort_if(! $this->resourcesAllowed('users', ''), 404);

        if (! $user = Facades\User::find($id)) {
            abort(404);
        }

        $this->authorize('delete', $user);

        Facades\User::delete($user);

        return response('', 204);
    }
}

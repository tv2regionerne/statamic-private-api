<?php

namespace Tv2regionerne\StatamicPrivateApi\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Statamic\Contracts\Auth\User;
use Statamic\Facades;
use Statamic\Http\Resources\API\UserResource;
use Statamic\Rules\UniqueUserValue;
use Tv2regionerne\StatamicPrivateApi\Traits\VerifiesPrivateAPI;

class UsersController extends ApiController
{
    use VerifiesPrivateAPI;

    public function index()
    {
        abort_if(! $this->resourcesAllowed('users', ''), 404);

        $this->authorize('view', [User::class]);

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

        $this->authorize('view', $user);

        return app(UserResource::class)::make($user);
    }

    public function store(Request $request)
    {
        abort_if(! $this->resourcesAllowed('users', ''), 404);

        $this->authorize('create', [User::class]);

        try {
            $validator = Validator::make($request->all(), [
                'email' => ['required', 'email', new UniqueUserValue],
            ]);

            $validator->setAttributeNames([
                'email' => 'Email Address',
            ]);

            $validator->validate();

            $user = Facades\User::make()
                ->email($request->string('email')->toString());

            foreach ($request->except(['email', 'super', 'groups', 'roles', 'invitation']) as $key => $value) {
                $user->set($key, $value);
            }

            if ($request->boolean('super') && Facades\User::current()?->isSuper()) {
                $user->makeSuper();
            }

            $user->save();

            $user = Facades\User::findByEmail($request->string('email')->toString());

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

        $this->authorize('edit', $user);

        try {
            $payload = array_merge($request->all(), [
                'email' => $request->input('email', $user->email()),
            ]);

            $validator = Validator::make($payload, [
                'email' => ['required', 'email', new UniqueUserValue(except: $user->id())],
            ]);

            $validator->setAttributeNames([
                'email' => 'Email Address',
            ]);

            $validator->validate();

            foreach ($request->except(['email', 'super', 'groups', 'roles', 'invitation']) as $key => $value) {
                $user->set($key, $value);
            }

            $user->email($payload['email']);

            if ($request->has('super') && Facades\User::current()?->isSuper() && Facades\User::current()?->id() !== $user->id()) {
                $user->super = $request->boolean('super');
            }

            $user->save();

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

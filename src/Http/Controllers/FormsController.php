<?php

namespace Tv2regionerne\StatamicPrivateApi\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Statamic\Facades;
use Statamic\Http\Controllers\API\ApiController;
use Statamic\Http\Controllers\CP\Forms\FormsController as CpController;
use Statamic\Http\Resources\API\FormResource;
use Statamic\Query\ItemQueryBuilder;
use Tv2regionerne\StatamicPrivateApi\Traits\VerifiesPrivateAPI;

class FormsController extends ApiController
{
    use VerifiesPrivateAPI;

    public function index()
    {
        abort_if(! $this->resourcesAllowed('forms', ''), 404);

        $query = (new ItemQueryBuilder)->withItems(Facades\Form::all());

        return app(FormResource::class)::collection(
            $this->filterSortAndPaginate($query)
        );
    }

    public function show($form)
    {
        $form = $this->formFromHandle($form);

        return app(FormResource::class)::make($form);
    }

    public function store(Request $request)
    {
        try {
            (new CpController($request))->store($request);

            $form = $this->formFromHandle($request->input('handle'));

            return app(FormResource::class)::make($form);
        } catch (ValidationException $e) {
            return $this->returnValidationErrors($e);
        }
    }

    public function update(Request $request, $form)
    {
        $form = $this->formFromHandle($form);

        try {
            (new CpController($request))->update($form, $request);

            $form = $this->formFromHandle($form);

            return app(FormResource::class)::make($form);
        } catch (ValidationException $e) {
            return $this->returnValidationErrors($e);
        }
    }

    public function destroy(Request $request, $form)
    {
        $form = $this->formFromHandle($form);

        return (new CpController($request))->destroy($form);
    }

    private function formFromHandle($form)
    {
        $form = is_string($form) ? Facades\Form::find($form) : $form;

        if (! $form) {
            abort(404);
        }

        abort_if(! $this->resourcesAllowed('forms', $form->handle()), 404);

        return $form;
    }
}

<?php

namespace Tv2regionerne\StatamicPrivateApi\Http\Controllers;

use Illuminate\Http\Request;
use Statamic\Facades;
use Statamic\Http\Controllers\API\ApiController;
use Statamic\Http\Controllers\CP\Forms\FormsController as CpController;
use Statamic\Query\ItemQueryBuilder;
use Statamic\Http\Resources\API\FormResource;
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
        abort_if(! $this->resourcesAllowed('forms', ''), 404);

        return (new CpController($request))->store($request);
    }

    public function update(Request $request, $form)
    {
        $form = $this->formFromHandle($form);

        return (new CpController($request))->update($form, $request);
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

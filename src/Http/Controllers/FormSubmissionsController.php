<?php

namespace Tv2regionerne\StatamicPrivateApi\Http\Controllers;

use Illuminate\Http\Request;
use Statamic\Facades;
use Statamic\Http\Controllers\API\ApiController;
use Statamic\Http\Controllers\Cp\Forms\FormSubmissionsController as CpController;
use Statamic\Query\ItemQueryBuilder;
use Tv2regionerne\StatamicPrivateApi\Http\Resources\FormSubmissionResource;
use Tv2regionerne\StatamicPrivateApi\Traits\VerifiesPrivateAPI;

class FormSubmissionsController extends ApiController
{
    use VerifiesPrivateAPI;

    public function index($form)
    {
        $form = $this->formFromHandle($form);

        $query = (new ItemQueryBuilder)->withItems($form->submissions());
        return FormSubmissionResource::collection(
            $this->filterSortAndPaginate($query)
        );
    }

    public function show($form, $id)
    {
        $form = $this->formFromHandle($form);

        $submission = $form->submission($id);

        if (! $submission) {
            abort(404);
        }

        return app(FormSubmissionResource::class)::make($submission);
    }

    public function destroy(Request $request, $form, $id)
    {
        $form = $this->formFromHandle($form);

        return (new CpController($request))->destroy($form, $id);
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

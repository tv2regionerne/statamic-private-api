<?php

use Illuminate\Support\Facades\Route;
use Tv2regionerne\StatamicPrivateApi\Http\Controllers;

Route::prefix(config('private-api.route'))
    ->middleware(config('private-api.middleware'))
    ->group(function () {

        Route::name('private.')
            ->middleware('auth:api')
            ->group(function () {

                Route::get('/ping', function (Illuminate\Http\Request $request) {
                    return $request->user();
                });

                // assets
                Route::prefix('/asset-containers')
                    ->group(function () {
                        Route::get('/', [Controllers\AssetContainersController::class, 'index']);
                        Route::post('/', [Controllers\AssetContainersController::class, 'store']);

                        Route::prefix('/{asset_container}')
                            ->group(function () {
                                Route::get('/', [Controllers\AssetContainersController::class, 'show']);
                                Route::patch('/', [Controllers\AssetContainersController::class, 'update']);
                                Route::delete('/', [Controllers\AssetContainersController::class, 'destroy']);

                                Route::prefix('/assets')
                                    ->group(function () {
                                        Route::get('/', [Controllers\AssetsController::class, 'index']);
                                        Route::get('{id}', [Controllers\AssetsController::class, 'show']);
                                        Route::delete('{id}', [Controllers\AssetsController::class, 'destroy']);
                                        Route::post('/', [Controllers\AssetsController::class, 'store']);
                                    });
                            });
                    });

                // collections
                Route::prefix('/collections')
                    ->group(function () {
                        Route::get('/', [Controllers\CollectionsController::class, 'index']);
                        Route::post('/', [Controllers\CollectionsController::class, 'store']);

                        // individual collection
                        Route::prefix('/{collection}')
                            ->group(function () {
                                Route::get('/', [Controllers\CollectionsController::class, 'show']);
                                Route::patch('/', [Controllers\CollectionsController::class, 'update']);
                                Route::delete('/', [Controllers\CollectionsController::class, 'destroy']);

                                // collection entries
                                Route::prefix('/entries')
                                    ->group(function () {
                                        Route::get('/', [Controllers\CollectionEntriesController::class, 'index']);
                                        Route::get('{entry}', [Controllers\CollectionEntriesController::class, 'show']);
                                        Route::post('/', [Controllers\CollectionEntriesController::class, 'store']);
                                        Route::patch('{entry}', [Controllers\CollectionEntriesController::class, 'update']);
                                        Route::delete('{entry}', [Controllers\CollectionEntriesController::class, 'destroy']);
                                    });

                                // collection tree
                                Route::prefix('/tree')
                                    ->group(function () {
                                        Route::get('/', [Controllers\CollectionTreesController::class, 'show']);
                                        Route::patch('/', [Controllers\CollectionTreesController::class, 'update']);
                                    });
                            });
                    });

                // forms
                Route::prefix('/forms')
                    ->group(function () {
                        Route::get('/', [Controllers\FormsController::class, 'index']);
                        Route::post('/', [Controllers\FormsController::class, 'store']);

                        Route::prefix('/{form}')
                            ->group(function () {
                                Route::get('/', [Controllers\FormsController::class, 'show']);
                                Route::patch('/', [Controllers\FormsController::class, 'update']);
                                Route::delete('/', [Controllers\FormsController::class, 'destroy']);

                                Route::prefix('/submissions')
                                    ->group(function () {
                                        Route::get('/', [Controllers\FormSubmissionsController::class, 'index']);
                                        Route::get('{id}', [Controllers\FormSubmissionsController::class, 'show']);
                                        Route::delete('{id}', [Controllers\FormSubmissionsController::class, 'destroy']);
                                    });
                            });
                    });

                // globals
                Route::prefix('/globals')
                    ->group(function () {
                        Route::get('/', [Controllers\GlobalsController::class, 'index']);
                        Route::post('/', [Controllers\GlobalsController::class, 'store']);

                        // individual set
                        Route::prefix('/{global}')
                            ->group(function () {
                                Route::get('/', [Controllers\GlobalsController::class, 'show']);
                                Route::patch('/', [Controllers\GlobalsController::class, 'update']);
                                Route::delete('/', [Controllers\GlobalsController::class, 'destroy']);

                                Route::prefix('/variables')
                                    ->group(function () {
                                        Route::get('/', [Controllers\GlobalVariablesController::class, 'show']);
                                        Route::patch('/', [Controllers\GlobalVariablesController::class, 'update']);
                                    });
                            });
                    });

                // navs
                Route::prefix('/navs')
                    ->group(function () {
                        Route::get('/', [Controllers\NavsController::class, 'index']);
                        Route::post('/', [Controllers\NavsController::class, 'store']);

                        // individual navs
                        Route::prefix('/{nav}')
                            ->group(function () {
                                Route::get('/', [Controllers\NavsController::class, 'show']);
                                Route::patch('/', [Controllers\NavsController::class, 'update']);
                                Route::delete('/', [Controllers\NavsController::class, 'destroy']);

                                // nav tree
                                Route::prefix('/tree')
                                    ->group(function () {
                                        Route::get('/', [Controllers\NavTreesController::class, 'show']);
                                        Route::patch('/', [Controllers\NavTreesController::class, 'update']);
                                    });
                            });
                    });

                // taxonomy terms
                Route::prefix('/taxonomies')
                    ->group(function () {
                        Route::get('/', [Controllers\TaxonomiesController::class, 'index']);
                        Route::post('/', [Controllers\TaxonomiesController::class, 'store']);

                        Route::prefix('/{taxonomy}')
                            ->group(function () {
                                Route::get('/', [Controllers\TaxonomiesController::class, 'show']);
                                Route::patch('/', [Controllers\TaxonomiesController::class, 'update']);
                                Route::delete('/', [Controllers\TaxonomiesController::class, 'destroy']);

                                Route::prefix('/terms')
                                    ->group(function () {
                                        Route::get('/', [Controllers\TaxonomyTermsController::class, 'index']);
                                        Route::get('{term}', [Controllers\TaxonomyTermsController::class, 'show']);
                                        Route::post('/', [Controllers\TaxonomyTermsController::class, 'store']);
                                        Route::patch('{term}', [Controllers\TaxonomyTermsController::class, 'update']);
                                        Route::delete('{term}', [Controllers\TaxonomyTermsController::class, 'destroy']);
                                    });
                            });
                    });

                // users
                Route::prefix('/users')
                    ->group(function () {
                        Route::get('/', [Controllers\UsersController::class, 'index']);
                        Route::get('{id}', [Controllers\UsersController::class, 'show']);
                        Route::post('/', [Controllers\UsersController::class, 'store']);
                        Route::patch('{id}', [Controllers\UsersController::class, 'update']);
                        Route::delete('{id}', [Controllers\UsersController::class, 'destroy']);
                    });

                //
                //            Route::name('assets.index')->get('assets/{asset_container}', [AssetsController::class, 'index']);
                //            Route::name('assets.show')->get('assets/{asset_container}/{asset}', [AssetsController::class, 'show'])->where('asset', '.*');

            });
    });

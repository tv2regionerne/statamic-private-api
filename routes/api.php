<?php

use Illuminate\Support\Facades\Route;
use Tv2regionerne\StatamicPrivateApi\Facades\PrivateApi;
use Tv2regionerne\StatamicPrivateApi\Http\Controllers;

Route::prefix(config('private-api.route'))
    ->middleware(config('private-api.middleware'))
    ->group(function () {

        Route::name('private.')
            ->group(function () {

                Route::get('/ping', function (Illuminate\Http\Request $request) {
                    return $request->user();
                });

                // assets
                Route::prefix('/asset-containers')
                    ->name('asset-containers.')
                    ->group(function () {
                        Route::get('/', [Controllers\AssetContainersController::class, 'index'])->name('index');
                        Route::post('/', [Controllers\AssetContainersController::class, 'store'])->name('store');

                        Route::prefix('/{asset_container}')
                            ->group(function () {
                                Route::get('/', [Controllers\AssetContainersController::class, 'show'])->name('show');
                                Route::patch('/', [Controllers\AssetContainersController::class, 'update'])->name('update');
                                Route::delete('/', [Controllers\AssetContainersController::class, 'destroy'])->name('destroy');

                                Route::prefix('/assets')
                                    ->name('assets.')
                                    ->group(function () {
                                        Route::get('/', [Controllers\AssetsController::class, 'index'])->name('index');
                                        Route::get('{id}', [Controllers\AssetsController::class, 'show'])->name('show');
                                        Route::patch('{id}', [Controllers\AssetsController::class, 'update']);
                                        Route::delete('{id}', [Controllers\AssetsController::class, 'destroy'])->name('destroy');
                                        Route::post('/', [Controllers\AssetsController::class, 'store'])->name('store');
                                    });
                            });
                    });

                // collections
                Route::prefix('/collections')
                    ->name('collections.')
                    ->group(function () {
                        Route::get('/', [Controllers\CollectionsController::class, 'index'])->name('index');
                        Route::post('/', [Controllers\CollectionsController::class, 'store'])->name('store');

                        // individual collection
                        Route::prefix('/{collection}')
                            ->group(function () {
                                Route::get('/', [Controllers\CollectionsController::class, 'show'])->name('show');
                                Route::patch('/', [Controllers\CollectionsController::class, 'update'])->name('update');
                                Route::delete('/', [Controllers\CollectionsController::class, 'destroy'])->name('destroy');

                                // collection entries
                                Route::prefix('/entries')
                                    ->name('entries.')
                                    ->group(function () {
                                        Route::get('/', [Controllers\CollectionEntriesController::class, 'index'])->name('index');
                                        Route::get('{entry}', [Controllers\CollectionEntriesController::class, 'show'])->name('show');
                                        Route::post('/', [Controllers\CollectionEntriesController::class, 'store'])->name('store');
                                        Route::patch('{entry}', [Controllers\CollectionEntriesController::class, 'update'])->name('update');
                                        Route::delete('{entry}', [Controllers\CollectionEntriesController::class, 'destroy'])->name('destroy');
                                    });

                                // collection tree
                                Route::prefix('/tree')
                                    ->name('trees.')
                                    ->group(function () {
                                        Route::get('/', [Controllers\CollectionTreesController::class, 'show'])->name('show');
                                        Route::patch('/', [Controllers\CollectionTreesController::class, 'update'])->name('update');
                                    });
                            });
                    });

                // forms
                Route::prefix('/forms')
                    ->name('forms.')
                    ->group(function () {
                        Route::get('/', [Controllers\FormsController::class, 'index'])->name('index');
                        Route::post('/', [Controllers\FormsController::class, 'store'])->name('store');

                        Route::prefix('/{form}')
                            ->group(function () {
                                Route::get('/', [Controllers\FormsController::class, 'show'])->name('show');
                                Route::patch('/', [Controllers\FormsController::class, 'update'])->name('update');
                                Route::delete('/', [Controllers\FormsController::class, 'destroy'])->name('destroy');

                                Route::prefix('/submissions')
                                    ->name('submissions.')
                                    ->group(function () {
                                        Route::get('/', [Controllers\FormSubmissionsController::class, 'index'])->name('index');
                                        Route::get('{id}', [Controllers\FormSubmissionsController::class, 'show'])->name('show');
                                        Route::delete('{id}', [Controllers\FormSubmissionsController::class, 'destroy'])->name('destroy');
                                    });
                            });
                    });

                // globals
                Route::prefix('/globals')
                    ->name('globals.')
                    ->group(function () {
                        Route::get('/', [Controllers\GlobalsController::class, 'index'])->name('index');
                        Route::post('/', [Controllers\GlobalsController::class, 'store'])->name('store');

                        // individual set
                        Route::prefix('/{globalset}')
                            ->group(function () {
                                Route::get('/', [Controllers\GlobalsController::class, 'show'])->name('show');
                                Route::patch('/', [Controllers\GlobalsController::class, 'update'])->name('update');
                                Route::delete('/', [Controllers\GlobalsController::class, 'destroy'])->name('destroy');

                                Route::prefix('/variables/{site}')
                                    ->name('variables.')
                                    ->group(function () {
                                        Route::get('/', [Controllers\GlobalVariablesController::class, 'show'])->name('show');
                                        Route::patch('/', [Controllers\GlobalVariablesController::class, 'update'])->name('update');
                                    });
                            });
                    });

                // navs
                Route::prefix('/navs')
                    ->name('navs.')
                    ->group(function () {
                        Route::get('/', [Controllers\NavsController::class, 'index'])->name('index');
                        Route::post('/', [Controllers\NavsController::class, 'store'])->name('store');

                        // individual navs
                        Route::prefix('/{nav}')
                            ->group(function () {
                                Route::get('/', [Controllers\NavsController::class, 'show'])->name('show');
                                Route::patch('/', [Controllers\NavsController::class, 'update'])->name('update');
                                Route::delete('/', [Controllers\NavsController::class, 'destroy'])->name('destroy');

                                // nav tree
                                Route::prefix('/tree')
                                    ->name('trees.')
                                    ->group(function () {
                                        Route::get('/', [Controllers\NavTreesController::class, 'show'])->name('show');
                                        Route::patch('/', [Controllers\NavTreesController::class, 'update'])->name('update');
                                    });
                            });
                    });

                // taxonomy terms
                Route::prefix('/taxonomies')
                    ->name('taxonomies.')
                    ->group(function () {
                        Route::get('/', [Controllers\TaxonomiesController::class, 'index'])->name('index');
                        Route::post('/', [Controllers\TaxonomiesController::class, 'store'])->name('store');

                        Route::prefix('/{taxonomy}')
                            ->group(function () {
                                Route::get('/', [Controllers\TaxonomiesController::class, 'show'])->name('show');
                                Route::patch('/', [Controllers\TaxonomiesController::class, 'update'])->name('update');
                                Route::delete('/', [Controllers\TaxonomiesController::class, 'destroy'])->name('destroy');

                                Route::prefix('/terms')
                                    ->name('terms.')
                                    ->group(function () {
                                        Route::get('/', [Controllers\TaxonomyTermsController::class, 'index'])->name('index');
                                        Route::get('{term}', [Controllers\TaxonomyTermsController::class, 'show'])->name('show');
                                        Route::post('/', [Controllers\TaxonomyTermsController::class, 'store'])->name('store');
                                        Route::patch('{term}', [Controllers\TaxonomyTermsController::class, 'update'])->name('update');
                                        Route::delete('{term}', [Controllers\TaxonomyTermsController::class, 'destroy'])->name('destroy');
                                    });
                            });
                    });

                // users
                Route::prefix('/users')
                    ->name('users.')
                    ->group(function () {
                        Route::get('/', [Controllers\UsersController::class, 'index'])->name('index');
                        Route::get('{id}', [Controllers\UsersController::class, 'show'])->name('show');
                        Route::post('/', [Controllers\UsersController::class, 'store'])->name('store');
                        Route::patch('{id}', [Controllers\UsersController::class, 'update'])->name('update');
                        Route::delete('{id}', [Controllers\UsersController::class, 'destroy'])->name('destroy');
                    });

                PrivateApi::additionalRoutes();
            });
    });

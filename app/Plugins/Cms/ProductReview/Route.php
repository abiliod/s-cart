<?php
/**
 * Route front
 */
if(sc_config('ProductReview')) {
Route::group(
    [
        'prefix'    => 'plugin/product_review',
        'namespace' => 'App\Plugins\Cms\ProductReview\Controllers',
    ],
    function () {
        Route::post('post-review', 'FrontController@postReview')
        ->name('product_review.postReview');
        Route::post('remove-review', 'FrontController@removeReview')
        ->name('product_review.removeReview');
    }
);
}
/**
 * Route admin
 */
Route::group(
    [
        'prefix' => SC_ADMIN_PREFIX.'/product_review',
        'middleware' => SC_ADMIN_MIDDLEWARE,
        'namespace' => 'App\Plugins\Cms\ProductReview\Admin',
    ], 
    function () {
        Route::get('/', 'AdminController@index')
        ->name('admin_product_review.index');
        Route::post('/update_status', 'AdminController@updateStatus')
        ->name('admin_product_review.update_status');
        Route::post('/delete', 'AdminController@deleteList')
        ->name('admin_product_review.delete');
    }
);

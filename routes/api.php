<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::group(['prefix' => 'v1'], function() {
    Route::group(['middleware' => []],function(){
        Route::post('/authentication', 'AuthController@postAuthentication');
        Route::get('/prod-inout', 'VsysController@getProductInputOutput');
        Route::get('/cdm', 'VsysController@getUserCardMoney');
        Route::get('/reg-visitor', 'VsysController@getRegisterVisitor');
    });

// Co header la "Bearer + token" thi duoc vao
    Route::group(['middleware' => 'jwt.auth'],function(){
        Route::get('/authorization', 'AuthController@getAuthorization');

        Route::get('/nhapdulieu', function(Request $request) {
            error_log('========================');
            error_log($_GET['param']);
            error_log('========================');
            return response()->json(json_decode($_GET['param']), 200);
        });

        Route::group(['prefix' => 'locations'], function () {
            Route::get('/', 'LocationController@getReadAll');
        });

        Route::group(['middleware' => 'iocenter', 'prefix' => 'io-centers'], function () {
            Route::get('/', 'IOCenterController@getReadAll');
            Route::get('/search', 'IOCenterController@getSearchOne');
            Route::get('/page/{page}/{pageSize}', 'IOCenterController@getReadAllWithPage');
            Route::get('/{id}', 'IOCenterController@getReadOne');
            Route::post('/', 'IOCenterController@postCreateOne');
            Route::put('/', 'IOCenterController@putUpdateOne');
            Route::patch('/', 'IOCenterController@patchDeactivateOne');
            Route::delete('/{id}', 'IOCenterController@deleteDeleteOne');
        });

        Route::group(['middleware' => 'device', 'prefix' => 'devices'], function () {
            Route::get('/', 'DeviceController@getReadAll');
            Route::get('/search', 'DeviceController@getSearchOne');
            Route::get('/{id}', 'DeviceController@getReadOne');
            Route::post('/', 'DeviceController@postCreateOne');
            Route::put('/', 'DeviceController@putUpdateOne');
            Route::patch('/', 'DeviceController@patchDeactivateOne');
            Route::delete('/{id}', 'DeviceController@deleteDeleteOne');
        });

        Route::group(['middleware' => 'supplier', 'prefix' => 'suppliers'], function () {
            Route::get('/', 'SupplierController@getReadAll');
            Route::get('/search', 'SupplierController@getSearchOne');
            Route::get('/{id}', 'SupplierController@getReadOne');
            Route::post('/', 'SupplierController@postCreateOne');
            Route::put('/', 'SupplierController@putUpdateOne');
            Route::patch('/', 'SupplierController@patchDeactivateOne');
            Route::delete('/{id}', 'SupplierController@deleteDeleteOne');
        });

        Route::group(['middleware' => 'distributor', 'prefix' => 'distributors'], function () {
            Route::get('/', 'DistributorController@getReadAll');
            Route::get('/search', 'DistributorController@getSearchOne');
            Route::get('/{id}', 'DistributorController@getReadOne');
            Route::post('/', 'DistributorController@postCreateOne');
            Route::put('/', 'DistributorController@putUpdateOne');
            Route::patch('/', 'DistributorController@patchDeactivateOne');
            Route::delete('/{id}', 'DistributorController@deleteDeleteOne');
        });

        Route::group(['middleware' => 'user', 'prefix' => 'users'], function () {
            Route::get('/', 'UserController@getReadAll');
            Route::get('/search', 'UserController@getSearchOne');
            Route::get('/{id}', 'UserController@getReadOne');
            Route::post('/', 'UserController@postCreateOne');
            Route::post('/change-password', 'UserController@postChangePassword');
            Route::put('/', 'UserController@putUpdateOne');
            Route::patch('/', 'UserController@patchDeactivateOne');
            Route::delete('/{id}', 'UserController@deleteDeleteOne');
        });

        Route::group(['middleware' => 'product', 'prefix' => 'products'], function () {
            Route::get('/', 'ProductController@getReadAll');
            Route::get('/search', 'ProductController@getSearchOne');
            Route::get('/{id}', 'ProductController@getReadOne');
            Route::post('/', 'ProductController@postCreateOne');
            Route::put('/', 'ProductController@putUpdateOne');
            Route::patch('/', 'ProductController@patchDeactivateOne');
            Route::delete('/{id}', 'ProductController@deleteDeleteOne');
        });

        Route::group(['middleware' => 'producer', 'prefix' => 'producers'], function () {
            Route::get('/', 'ProducerController@getReadAll');
            Route::get('/search', 'ProducerController@getSearchOne');
            Route::get('/{id}', 'ProducerController@getReadOne');
            Route::post('/', 'ProducerController@postCreateOne');
            Route::put('/', 'ProducerController@putUpdateOne');
            Route::patch('/', 'ProducerController@patchDeactivateOne');
            Route::delete('/{id}', 'ProducerController@deleteDeleteOne');
        });

        Route::group(['middleware' => 'unit', 'prefix' => 'units'], function () {
            Route::get('/', 'UnitController@getReadAll');
            Route::get('/search', 'UnitController@getSearchOne');
            Route::get('/{id}', 'UnitController@getReadOne');
            Route::post('/', 'UnitController@postCreateOne');
            Route::put('/', 'UnitController@putUpdateOne');
            Route::patch('/', 'UnitController@patchDeactivateOne');
            Route::delete('/{id}', 'UnitController@deleteDeleteOne');
        });

        Route::group(['middleware' => 'position', 'prefix' => 'positions'], function () {
            Route::get('/', 'PositionController@getReadAll');
            Route::get('/search', 'PositionController@getSearchOne');
            Route::get('/{id}', 'PositionController@getReadOne');
            Route::post('/', 'PositionController@postCreateOne');
            Route::put('/', 'PositionController@putUpdateOne');
            Route::patch('/', 'PositionController@patchDeactivateOne');
            Route::delete('/{id}', 'PositionController@deleteDeleteOne');
        });

        Route::group(['middleware' => 'button-product', 'prefix' => 'button-products'], function () {
            Route::get('/', 'ButtonProductController@getReadAll');
            Route::get('/search', 'ButtonProductController@getSearchOne');
            Route::get('/{id}', 'ButtonProductController@getReadOne');
            Route::post('/', 'ButtonProductController@postCreateOrUpdateMulti');
            Route::put('/', 'ButtonProductController@putUpdateOne');
            Route::patch('/', 'ButtonProductController@patchDeactivateOne');
            Route::delete('/{id}', 'ButtonProductController@deleteDeleteOne');
        });

        Route::group(['middleware' => 'user-card', 'prefix' => 'user-cards'], function () {
            Route::get('/', 'UserCardController@getReadAll');
            Route::get('/search', 'UserCardController@getSearchOne');
            Route::get('/{id}', 'UserCardController@getReadOne');
            Route::post('/', 'UserCardController@postCreateOrUpdateOne');
            Route::patch('/', 'UserCardController@patchDeactivateOne');
            Route::delete('/{id}', 'UserCardController@deleteDeleteOne');
        });

        Route::group(['middleware' => 'report-supplier', 'prefix' => 'report-suppliers'], function () {
            Route::get('/', 'ReportCustomerController@getReadAll');
            Route::group(['prefix' => 'report-inputs'], function () {
                Route::get('/search', 'ReportCustomerController@getReportInputBySearch');
            });
            Route::group(['prefix' => 'report-stocks'], function () {
                Route::get('/search', 'ReportCustomerController@getReportStockBySearch');
            });
            Route::group(['prefix' => 'report-sales'], function () {
                Route::get('/search', 'ReportCustomerController@getReportSaleBySearch');
            });
        });

        Route::group(['middleware' => 'report-distributor', 'prefix' => 'report-distributors'], function () {
            Route::get('/', 'ReportCustomerController@getReadAll');
            Route::group(['prefix' => 'report-inputs'], function () {
                Route::get('/search', 'ReportCustomerController@getReportInputBySearch');
            });
            Route::group(['prefix' => 'report-stocks'], function () {
                Route::get('/search', 'ReportCustomerController@getReportStockBySearch');
            });
            Route::group(['prefix' => 'report-sales'], function () {
                Route::get('/search', 'ReportCustomerController@getReportSaleBySearch');
            });
        });

        Route::group(['middleware' => 'report-staff-input', 'prefix' => 'report-staff-inputs'], function () {
            Route::get('/', 'ReportCustomerController@getReadAll');
            Route::group(['prefix' => 'report-inputs'], function () {
                Route::get('/search', 'ReportCustomerController@getReportInputBySearch');
            });
            Route::group(['prefix' => 'report-stocks'], function () {
                Route::get('/search', 'ReportCustomerController@getReportStockBySearch');
            });
        });

        Route::group(['middleware' => 'report-vsys', 'prefix' => 'report-vsyss'], function () {
            Route::get('/', 'ReportVsysController@getReadAll');
            Route::get('/balance-detail/{id}', 'ReportVsysController@getReportBalanceDetail');
            Route::group(['prefix' => 'report-balances'], function () {
                Route::get('/search', 'ReportVsysController@getReportBalanceBySearch');
            });
            Route::group(['prefix' => 'report-dpss'], function () {
                Route::get('/search', 'ReportVsysController@getReportDpsBySearch');
            });
        });

        Route::group(['middleware' => 'report-logging', 'prefix' => 'report-loggings'], function () {
            Route::get('/', 'ReportLoggingController@getReadAll');
            Route::get('/search', 'ReportLoggingController@getReportLoggingBySearch');
        });
    });
});



<?php


Route::group([
    'namespace'  => 'App\Api\Oauth\Controllers',
    'middleware' => ['web', 'wechat.oauth'],
], function ($router) {
    $router->get('/redirect_to_worker_v1', 'AccessTokenController@generateWorkerAccessToken');
    $router->get('/redirect_to_service_provider_v1', 'AccessTokenController@generateServiceProviderAccessToken');
    $router->get('/redirect_to_big_land_v1', 'AccessTokenController@generateBiglandlordorAccessToken');
});

Route::group([
    'namespace'  => 'App\Api\Oauth\Controllers',
    'middleware' => ['web', 'wechat.oauth:index']
], function ($router) {
    $router->get('/redirect_to_customer_v1', 'AccessTokenController@generateCustomerAccessToken');
    $router->get('/redirect_to_customer_personal_v1', 'AccessTokenController@generateCustomerPersonalAccessToken');
});
<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/
$app->group(['middleware' => 'api.log'], function () use ($app) {

    $app->post('/info', 'InfoController@add');

    $app->get('/active/{code}', 'UserController@active');
    $app->post('/user/create', 'UserController@create');
    $app->post('/user/resetpassword', 'UserController@resetPassword');
    $app->post('/user/changepassword', 'UserController@changePassword');
    $app->post('/user/getresetpasswordtoken', 'UserController@getResetPasswordToken');
    $app->post('/user/login', 'UserController@login');

    $app->post('/user/file', 'UserController@uploadFile');
    $app->get('/user/file/{name}', 'UserController@file');
    
    $app->post('/software/feedback', 'UserController@feedback');
    $app->post('/software/info', 'UserController@softwareInfo');
});


$app->get('/user/info', 'UserController@info');
$app->post('/user/info', 'UserController@showInfo');

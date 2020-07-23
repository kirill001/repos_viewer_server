<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

Route::post('/repos/add',                   'RepoController@addRepo');
Route::get('/repos/get',                    'RepoController@allRepos');
Route::post('/repos/{repo}/remove',         'RepoController@removeRepo');
Route::get('/repos/{repo}/commits',         'RepoController@getCommits');
Route::post('/repos/{repo}/commits/remove', 'RepoController@removeCommits');

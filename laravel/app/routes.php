<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the Closure to execute when that URI is requested.
|
*/
Route::filter('loggedin', function(){
	if (!Session::has('user')){
		return Shared\Errors::handleError("notloggedin");
	}
	if (Session::get('ip') != $_SERVER['REMOTE_ADDR']){
		Session::flush();
		return Shared\Errors::handleError("notloggedin");
	}
});
Route::filter('playconfirmed', function(){
	if (!Session::has('playconfirm')){
		return Redirect::action('GameController@showGameListing');
		//return Shared\Errors::handleError("mustconfirm");
	}
});
Route::filter('createconfirmed', function(){
	if (!Session::has('createconfirm')){
		return Redirect::action('GameController@createPuzzle');
		//return Shared\Errors::handleError("mustconfirm");
	}
});

Route::get('/', function()
{
	return View::make('index');
});

Route::get('about', function()
{	
    return View::make('about');
});

Route::get('contact', function()
{
    return View::make('contact');
});

Route::get('contact', function()
{
    return View::make('contact');
});

//game tutorial routes
Route::get('tutorials', 'GameController@showTutorialListing');
Route::get('try/{id}', 'GameController@playTutorial');

//game (solver) routes
Route::get('play', 'GameController@showGameListing');
Route::get('play/{id}', array('before' => 'loggedin', 'uses' => 'GameController@confirmEntry'));
Route::get('confirm', array('before' => 'loggedin', 'uses' => 'GameController@playResponse'));
Route::get('game/{id}', array('before' => 'loggedin|playconfirmed', 'uses' => 'GameController@playGame'));

//game (creator) routes
Route::get('create', array('before' => 'loggedin', 'uses' => 'GameController@createPuzzle'));
Route::get('make', array('before' => 'loggedin', 'uses' => 'GameController@makePuzzle'));
Route::post('make/summary', array('before' => 'loggedin', 'uses' => 'GameController@confirmCreate'));
Route::get('make/confirm', array('before' => 'loggedin', 'uses' => 'GameController@createResponse'));
Route::get('make/save', array('before' => 'loggedin|createconfirmed', 'uses' => 'GameController@savePuzzle'));
//login routes
Route::any('login', 'LoginController@doLogin');
Route::get('logout', 'LoginController@doLogout');

//account routes
Route::get('profile', 'UserController@publicListing');
Route::get('profile/{id}', 'UserController@publicProfile');

Route::get('account', array('before' => 'loggedin', 'uses' => 'UserController@accountIndex'));
Route::group(array('prefix' => '/account'), function()
		{
			//for account sections
			Route::post('/nickname', array('before' => 'loggedin', 'uses' => 'UserController@changeNickname'));
			Route::get('/wallet', array('before' => 'loggedin', 'uses' => 'UserController@showWallet'));
			Route::get('/wallet/getnewaddress', array('before' => 'loggedin', 'uses' => 'UserController@getNewAddress'));
			Route::post('/wallet/withdraw', array('before' => 'loggedin', 'uses' => 'UserController@withdraw'));
			Route::get('/close/{id}', array('before' => 'loggedin', 'uses' => 'GameController@closeGame'));
		});

//API
Route::group(array('prefix' => 'api'), function()
{
	//the routes for all API calls
	Route::group(array('prefix' => '/v1'), function()
	{
		//for version 1
		Route::group(array('prefix' => '/solver', 'before' => 'loggedin'), function()
		{
			//for solvers only
			Route::get('/getMap/{id}', 'APIController@getMap_Solver');
			Route::get('/move/{id}/{tileID}/{sessionID}', 'APIController@getMove_Solver');
		});
		
		Route::group(array('prefix' => '/creator', 'before' => 'loggedin'), function()
		{
			//for creators only
			Route::get('/getMap/{width}/{height}', 'APIController@getMap_Creator');
			Route::post('/evalMap', 'APIController@evalMap_Creator');
			Route::get('/getFees', 'APIController@getFees_Creator');
			Route::post('/saveMap', 'APIController@savePuzzle_Creator');
		});
		
		Route::group(array('prefix' => '/all'), function()
		{
			//for everyone
			Route::get('/getTiles', 'APIController@getTiles_All');
			Route::get('/getTileInfo/{id}', 'APIController@getTileInfo_All');
		});
		
		Route::group(array('prefix' => '/tutorial'), function()
		{
			//for everyone
			Route::get('/getMap/{id}', 'APIController@getMap_Tutorial');
		});
	
	});

});

//test coin routes
Route::get('coin', 'APIController@getCoinInfo');
<?php

use Illuminate\Support\Facades\Route;
use Symfony\Component\HttpFoundation\Response;

Route::get('/', function () {
// return response('Forbidden', Response::HTTP_FORBIDDEN);
// Return welcome page
  return view('welcome');
});

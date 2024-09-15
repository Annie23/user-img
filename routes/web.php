<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;

// Route for the user list page (renders the Blade template)
Route::get('/', function () {
    return view('index'); // Loads the index.blade.php file in resources/views
});


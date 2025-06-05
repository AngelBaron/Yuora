<?php

use App\Http\Controllers\Api\ArtistController;
use App\Http\Controllers\Api\UserController;
use App\Models\Artist;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');



Route::get('/users', function () {
    return response()->json(User::all());
});

Route::post('/login', function (Request $request) {
    $request->validate([
        'email' => 'required|email',
        'password' => 'required',
    ]);

    $user = User::where('email', $request->email)->first();

    if (!$user || ! Hash::check($request->password, $user->password)) {
        return response()->json(['message' => 'Credenciales incorrectas'], 401);
    }

      $token = $user->createToken('api-token')->plainTextToken;

    return response()->json([
        'token' => $token,
        'user' => $user,
    ]);
});





Route::middleware(['auth:sanctum'])->group(function () {
    //CRUD DE USUARIOS Y PERFILES
    //Obtener el perfil del usuario autenticado
    Route::get('/me', [UserController::class,'me']);
    //Actualizar el perfil
    Route::patch('/me',[UserController::class,'updateProfile']);

    //Delete profile and cover photos
    Route::delete('/me/profile-photo',[UserController::class,'deleteProfilePhoto']);
    Route::delete('/me/cover-photo',[UserController::class,'deleteCoverPhoto']);

    //Create Artist for user
    Route::post('/create-artist',[ArtistController::class,'create']);
    Route::get('/me-artist',[ArtistController::class,'meArtist']);
    Route::patch('/me-artist',[ArtistController::class,'updateArtist']);

    //delete profile and cover photo for artist
    Route::delete('/me-artist/profile',[ArtistController::class,'deleteProfilePhoto']);
    Route::delete('/me-artist/cover',[ArtistController::class,'deleteCoverPhoto']);

    //crud for songs
    Route::post('/create-song',[ArtistController::class,'createSong']);

    //See Artist with id DO WHEN DOING LISTENER
    // Route::get('/artist/{id}',[ArtistController::class,'showArtist']);
});
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    public function me(Request $request)
    {
        return response()->json($request->user());
    }

    public function updateProfile(Request $request)
    {
        $user = $request->user();
        $data = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|max:255|unique:users,email,' . $user->id,
            'password' => 'sometimes|string|min:8',
            'password_confirmation' => 'sometimes|string|min:8|same:password',
            'perfil_photo' => 'sometimes|image|max:2048',
            'cover_photo' => 'sometimes|image|max:5120',
            'description' => 'sometimes|string|max:500',
            'display_preference' => 'sometimes|in:default,yes,no'
        ]);

        if ($request->hasFile('perfil_photo')) {
            if ($user->perfil_photo && Storage::disk('public')->exists($user->perfil_photo)) {
                Storage::disk('public')->delete($user->perfil_photo);
            }
            $file = $request->file('perfil_photo');
            $filename = md5(now() . $file->getClientOriginalName()) . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('perfil_photos', $filename, 'public');
            $data['perfil_photo'] = $path;
        }

        if ($request->hasFile('cover_photo')) {
            if ($user->cover_photo && Storage::disk('public')->exists($user->cover_photo)) {
                Storage::disk('public')->delete($user->cover_photo);
            }
            $file = $request->file('cover_photo');
            $filename = md5(now() . $file->getClientOriginalName()) . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('cover_photos', $filename, 'public');
            $data['cover_photo'] = $path;
        }

        if (!empty($data['password'])) {
            $data['password'] = bcrypt($data['password']);
        }

        $user->update($data);

        return response()->json($user);
    }

    public function deleteProfilePhoto(Request $request)
    {
        $user = $request->user();

        if ($user->perfil_photo && Storage::disk('public')->exists($user->perfil_photo)) {
            Storage::disk('public')->delete($user->perfil_photo);
            $user->perfil_photo = null;

            $user->save();

            return response()->json(["message" => 'Perfil photo has been eliminate correctly.']);
        }
        return response()->json(['message' => 'Without photo to eliminate'], '404');
    }

    public function deleteCoverPhoto(Request $request)
    {
        $user = $request->user();

        if ($user->cover_photo && Storage::disk('public')->exists($user->cover_photo)) {
            Storage::disk('public')->delete($user->cover_photo);
            $user->cover_photo = null;

            $user->save();

            return response()->json(["message" => 'Perfil cover has been eliminate correctly.']);
        }
        return response()->json(['message' => 'Without photo to eliminate'], '404');
    }

   
}

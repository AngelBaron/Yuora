<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Artist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ArtistController extends Controller
{
    public function create(Request $request)
    {
        $user= $request->user();

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'perfil_photo' => 'nullable|image|max:2048',
            'cover_photo' => 'nullable|image|max:5120',
            'description' => 'nullable|string|max:500',
        ]);

        
        if ($request->hasFile('perfil_photo')) {
            $file = $request->file('perfil_photo');
            $filename = md5(now() . $file->getClientOriginalName()) . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('perfil_photos', $filename, 'public');
            $data['perfil_photo'] = $path;
        }
        if ($request->hasFile('cover_photo')) {
            $file = $request->file('cover_photo');
            $filename = md5(now() . $file->getClientOriginalName()) . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('cover_photos', $filename, 'public');
            $data['cover_photo'] = $path;
        }
        try {
            $artist = Artist::create([
            'user_id' => $user->id,
            'name' => $data['name'],
            'perfil_photo' => $data['perfil_photo'] ?? null,
            'cover_photo' => $data['cover_photo'] ?? null,
            'description' => $data['description'] ?? null,
        ]);
        } catch (\Throwable $th) {
            //throw $th;
            return response()->json(['message' => 'Error creating artist: ' . $th->getMessage()], 500);
        }
        

        if (!$artist) {
            return response()->json(['message' => 'Error creating artist'], 500);
        }
        return response()->json($artist, 201);
    }

    public function meArtist(Request $request)
    {
        $user = $request->user();
        $artist = Artist::where('user_id', $user->id)->first();

        if (!$artist) {
            return response()->json(['message' => 'Artist not found'], 404);
        }

        return response()->json($artist);
    }

    public function updateArtist(Request $request)
    {
        $user = $request->user();

        $artist = Artist::where('user_id',$user->id)->first();

        $data = $request->validate([
            'name'=>'sometimes|string|max:255',
            'perfil_photo'=>'sometimes|image|max:2048',
            'cover_photo'=>'sometimes|image|max:5120',
            'description'=>'sometimes|string|max:512'
        ]);

        if($request->hasFile('perfil_photo')){
            if($artist->perfil_photo && Storage::disk('public')->exists($artist->perfil_photo)){
                Storage::disk('public')->delete($artist->perfil_photo);
            }

            $file = $request->file('perfil_photo');
            $filename = md5(now() . $file->getClientOriginalName()). '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('perfil_photos',$filename,'public');
            $data['perfil_photo']=$path;
        }

        if($request->hasFile('cover_photo'))
        {
            if($artist->cover_photo && Storage::disk('public')->exists($artist->cover_photo)){
                Storage::disk('public')->delete($artist->cover_photo);
            }
            $file= $request->file('cover_photo');
            $filename = md5(now() . $file->getClientOriginalName()) . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('cover_photos',$filename,'public');
            $data['cover_photo'] = $path;
        }

        if(!$artist){
            return response()->json(['message'=>'Artist not found'],404);
        }

        $artist->update($data);

        return response()->json($artist, 206);
    }

    public function deleteProfilePhoto(Request $request)
    {
        $user = $request->user();
        $artist = Artist::where('user_id',$user->id)->first();
        if($artist->perfil_photo && Storage::disk('public')->exists($artist->perfil_photo)){
            Storage::disk('public')->delete($artist->perfil_photo);
            $artist->perfil_photo = null;
            $artist->save();
            return response()->json(["message" => 'Perfil photo has been eliminate correctly.']);
        }
        return response()->json(["message"=>'Without photo to eliminate'],404);
    }

    public function deleteCoverPhoto(Request $request)
    {
        $user = $request->user();
        $artist = Artist::where('user_id',$user->id)->first();
        if($artist->cover_photo && Storage::disk('public')->exists($artist->cover_photo)){
            Storage::disk('public')->delete($artist->cover_photo);
            $artist->cover_photo = null;
            $artist->save();
            return response()->json(["message" => 'Cover photo has been eliminate correctly.']);
        }
        return response()->json(["message"=>'Without photo to eliminate'],404);
    }
}

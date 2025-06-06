<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Artist;
use App\Models\Song;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ArtistController extends Controller
{
    public function create(Request $request)
    {
        $user = $request->user();

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

        $artist = Artist::where('user_id', $user->id)->first();
        if (!$artist) {
            return response()->json(['message' => 'Artist not found'], 404);
        }

        $data = $request->validate([
            'name' => 'sometimes|string|max:255',
            'perfil_photo' => 'sometimes|image|max:2048',
            'cover_photo' => 'sometimes|image|max:5120',
            'description' => 'sometimes|string|max:512'
        ]);

        if ($request->hasFile('perfil_photo')) {
            if ($artist->perfil_photo && Storage::disk('public')->exists($artist->perfil_photo)) {
                Storage::disk('public')->delete($artist->perfil_photo);
            }

            $file = $request->file('perfil_photo');
            $filename = md5(now() . $file->getClientOriginalName()) . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('perfil_photos', $filename, 'public');
            $data['perfil_photo'] = $path;
        }

        if ($request->hasFile('cover_photo')) {
            if ($artist->cover_photo && Storage::disk('public')->exists($artist->cover_photo)) {
                Storage::disk('public')->delete($artist->cover_photo);
            }
            $file = $request->file('cover_photo');
            $filename = md5(now() . $file->getClientOriginalName()) . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('cover_photos', $filename, 'public');
            $data['cover_photo'] = $path;
        }

        

        $artist->update($data);

        return response()->json($artist, 206);
    }

    public function deleteProfilePhoto(Request $request)
    {
        $user = $request->user();
        $artist = Artist::where('user_id', $user->id)->first();
        if ($artist->perfil_photo && Storage::disk('public')->exists($artist->perfil_photo)) {
            Storage::disk('public')->delete($artist->perfil_photo);
            $artist->perfil_photo = null;
            $artist->save();
            return response()->json(["message" => 'Perfil photo has been eliminate correctly.']);
        }
        return response()->json(["message" => 'Without photo to eliminate'], 404);
    }

    public function deleteCoverPhoto(Request $request)
    {
        $user = $request->user();
        $artist = Artist::where('user_id', $user->id)->first();
        if ($artist->cover_photo && Storage::disk('public')->exists($artist->cover_photo)) {
            Storage::disk('public')->delete($artist->cover_photo);
            $artist->cover_photo = null;
            $artist->save();
            return response()->json(["message" => 'Cover photo has been eliminate correctly.']);
        }
        return response()->json(["message" => 'Without photo to eliminate'], 404);
    }

    public function getSong(Request $request,$id)
    {
        $user = $request->user();
        $artist = Artist::where('user_id',$user->id)->first();
        if(!$artist)
        {
            return response()->json([
                'message'=>'Artist not found'
            ],404);
        }
        $song = Song::where('id',$id)->where('artist_id',$artist->id)->first();

        if(!$song)
        {
            return response()->json([
                'message'=>'Song not found'
            ],404);
        }

        return response()->json($song,200);
    }

    public function getSongs(Request $request)
    {
        $user = $request->user();
        $artist = Artist::where('user_id', $user->id)->first();

        if(!$artist)
        {
            return response()->json([
                'message'=>'Artist not found'
            ],404);
        }

        $songs = Song::where('artist_id',$artist->id)->get();

        return response()->json($songs,200);

    }

    public function createSong(Request $request)
    {
        $user = $request->user();
        $artist = Artist::where('user_id', $user->id)->first();
        if (!$artist) {
            return response()->json(['message' => 'Artist not found'], 404);
        }
        $data = $request->validate([
            'title' => 'required|string|max:127',
            'photo_song' => 'sometimes|image|max:2048',
            'audio_song' => 'required|mimes:mp3,wav,ogg,aac,flac|max:10240'
        ]);
        //encrypt name photo and audio
        if ($request->hasFile('photo_song')){
            $data['photo_song'] = $this->storeFile($request, 'photo_song', 'photo_songs');
        }
        
        $data['audio_song'] = $this->storeFile($request, 'audio_song', 'audio_songs');

        try {
            $song = Song::create([
                'artist_id' => $artist->id,
                'title' => $data['title'],
                'photo_song' => $data['photo_song']?? null,
                'audio_song' => $data['audio_song']
            ]);
        } catch (\Throwable $th) {
            return response()->json(['message' => 'Error creating song'], 500);
        }
        return response()->json($song, 200);
    }

    public function deleteSong(Request $request, $id)
    {
        $user = $request->user();
        $artist = Artist::where('user_id', $user->id)->first();
        if (!$artist) {
            return response()->json(['message' => 'Artist not exists'], 404);
        }
        $song = Song::where('id', $id)->where('artist_id', $artist->id)->first();
        if (!$song) {
            return response()->json(['message' => 'Song not found'], 404);
        }
        if ($song->photo_song && Storage::disk('public')->exists($song->photo_song)) {
            Storage::disk('public')->delete($song->photo_song);
        }
        if (Storage::disk('public')->exists($song->audio_song)) {
            Storage::disk('public')->delete($song->audio_song);
        }
        $song->delete();
        return response()->json(['message' => 'Song deleted successfully']);
    }

    public function deletePhotoSong(Request $request, $id)
    {
        $user = $request->user();
        $artist = Artist::where('user_id',$user->id)->first();

        if(!$artist)
        {
            return response()->json(['message'=>'Artist not found'],404);
        }

        $song = Song::where('id',$id)->where('artist_id',$artist->id)->first();

        if(!$song)
        {
            return response()->json(['message'=>'Song not found'],404);
        }

        if($song->photo_song && Storage::disk('public')->exists($song->photo_song))
        {
            Storage::disk('public')->delete($song->photo_song);
            $song->photo_song = null;

            $song->save();

            return response()->json(['message'=>'Photo has been eliminated correcly']);
        }
        
        return response()->json(['message'=>'Without photo to eliminate'],404);
    }

    public function updateSong(Request $request, $id)
    {
        $user = $request->user();
        $artist = Artist::where('user_id',$user->id)->first();
        if(!$artist)
        {
            return response()->json(['message'=>"Artist doesn't exist"],404);
        }
        $data = $request->validate(
            [
                'title' => 'sometimes|string|max:127',
                'photo_song' => 'sometimes|image|max:2048'
            ]
        );
        $song = Song::where('id',$id)->where('artist_id',$artist->id)->first();

        if(!$song)
        {
            return response()->json([
                'message'=>'Song not found'
            ],404);
        }

        if($request->hasFile('photo_song')){
            if($song->photo_song && Storage::disk('public')->exists($song->photo_song)){
                Storage::disk('public')->delete($song->photo_song);
            }
            $data['photo_song'] = $this->storeFile($request, 'photo_song', 'photo_songs');
        }
        try {
            $song->update($data);
        } catch (\Throwable $th) {
            return response()->json([
                'message'=>'Operation failed'
            ],400);
        }
        
        return response()->json([
            $song
        ],200);
    }

    private function storeFile(Request $request, string $key, string $folder): ?string
    {
        if (!$request->hasFile($key)) return null;

        $file = $request->file($key);
        $filename = md5(now() . $file->getClientOriginalName()) . '.' . $file->getClientOriginalExtension();
        return $file->storeAs($folder, $filename, 'public');
    }
}

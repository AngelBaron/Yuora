<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Album;
use App\Models\Artist;
use App\Models\Song;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
    public function getSong(Request $request, $id)
    {
        $user = $request->user();
        $artist = Artist::where('user_id', $user->id)->first();
        if (!$artist) {
            return response()->json([
                'message' => 'Artist not found'
            ], 404);
        }
        $song = Song::where('id', $id)->where('artist_id', $artist->id)->first();

        if (!$song) {
            return response()->json([
                'message' => 'Song not found'
            ], 404);
        }

        return response()->json($song, 200);
    }
    public function getSongs(Request $request)
    {
        $user = $request->user();
        $artist = Artist::where('user_id', $user->id)->first();

        if (!$artist) {
            return response()->json([
                'message' => 'Artist not found'
            ], 404);
        }

        $songs = Song::where('artist_id', $artist->id)->get();

        return response()->json($songs, 200);
    }
    //create Single
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
        if ($request->hasFile('photo_song')) {
            $data['photo_song'] = $this->storeFile($request, 'photo_song', 'photo_songs');
        }

        $data['audio_song'] = $this->storeFile($request, 'audio_song', 'audio_songs');
        try {
            $song = Song::create([
                'artist_id' => $artist->id,
                'title' => $data['title'],
                'photo_song' => $data['photo_song'] ?? null,
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
        $artist = Artist::where('user_id', $user->id)->first();

        if (!$artist) {
            return response()->json(['message' => 'Artist not found'], 404);
        }

        $song = Song::where('id', $id)->where('artist_id', $artist->id)->first();

        if (!$song) {
            return response()->json(['message' => 'Song not found'], 404);
        }

        if ($song->photo_song && Storage::disk('public')->exists($song->photo_song)) {
            Storage::disk('public')->delete($song->photo_song);
            $song->photo_song = null;

            $song->save();

            return response()->json(['message' => 'Photo has been eliminated correcly']);
        }

        return response()->json(['message' => 'Without photo to eliminate'], 404);
    }
    public function updateSong(Request $request, $id)
    {
        $user = $request->user();
        $artist = Artist::where('user_id', $user->id)->first();
        if (!$artist) {
            return response()->json(['message' => "Artist doesn't exist"], 404);
        }
        $data = $request->validate(
            [
                'title' => 'sometimes|string|max:127',
                'photo_song' => 'sometimes|image|max:2048'
            ]
        );
        $song = Song::where('id', $id)->where('artist_id', $artist->id)->first();

        if (!$song) {
            return response()->json([
                'message' => 'Song not found'
            ], 404);
        }

        if ($request->hasFile('photo_song')) {
            if ($song->photo_song && Storage::disk('public')->exists($song->photo_song)) {
                Storage::disk('public')->delete($song->photo_song);
            }
            $data['photo_song'] = $this->storeFile($request, 'photo_song', 'photo_songs');
        }
        try {
            $song->update($data);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Operation failed'
            ], 400);
        }

        return response()->json([
            $song
        ], 200);
    }
    //create album with songs or none
    public function getAlbum(Request $request, $id)
    {
        $user = $request->user();
        $artist = Artist::where('user_id', $user->id)->first();
        if (!$artist) {
            return response()->json(['message' => "Artist doesn't exist"], 404);
        }
        $album = Album::with('songs')->where('id', $id)->where('artist_id', $artist->id)->first();
        if (!$album) {
            return response()->json(['message' => "Album doesn't exist"], 404);
        }

        return response()->json($album, 200);
    }
    public function getAlbums(Request $request)
    {
        $user = $request->user();
        $artist = Artist::where('user_id', $user->id)->first();
        if (!$artist) {
            return response()->json(['message' => "Artist doesn't exist"], 404);
        }
        $albums = Album::with('songs')->where('artist_id', $artist->id)->get();
        if (!$albums) {
            return response()->json(['message' => "Albums doesn't exist"], 404);
        }

        return response()->json($albums, 200);
    }
    public function createAlbum(Request $request)
    {
        $user = $request->user();
        $artist = Artist::where('user_id', $user->id)->first();
        if (!$artist) {
            return response()->json(['message' => "Artist doesn't exist"], 404);
        }
        $data = $request->validate([
            'photo_album' => 'sometimes|image|max:2048',
            'name_album' => 'required|string|max:127',
            'title_song' => 'sometimes|array',
            'title_song.*' => 'string|max:127',
            'audio_song' => 'sometimes|array',
            'audio_song.*' => 'mimes:mp3,wav,ogg,aac,flac|max:10240'
        ]);
        if (
            isset($data['title_song'])
            && (!isset($data['audio_song']) || count($data['title_song']) !== count($data['audio_song']))
        ) {
            return response()->json(['message' => 'Title and audio song arrays must match in length'], 422);
        }
        DB::beginTransaction();

        try {
            if ($request->hasFile('photo_album')) {

                $data['photo_album'] = $this->storeFile($request, 'photo_album', 'photo_albums');
            }
            //create album
            $album = Album::create([
                'artist_id' => $user->id,
                'photo_album' => $data['photo_album'] ?? null,
                'name_album' => $data['name_album']
            ]);
            //create songs if the album has songs
            if (isset($data['title_song']) && isset($data['audio_song'])) {
                foreach ($data['title_song'] as $index => $title) {
                    $audio = $data['audio_song'][$index] ?? null;
                    if (!$audio) {
                        return response()->json(['message' => 'Audio song is required for each title'], 422);
                    }
                    $audioFile = $request->file('audio_song')[$index];
                    $audioPath = $audioFile->storeAs(
                        'audio_songs',
                        md5(now() . $audioFile->getClientOriginalName()) . '.' . $audioFile->getClientOriginalExtension(),
                        'public'
                    );
                    if (!$audioPath) {
                        return response()->json(['message' => 'Failed to store audio file'], 500);
                    }
                    Song::create([
                        'artist_id' => $artist->id,
                        'album_id' => $album->id,
                        'title' => $title,
                        'photo_song' => null, // No photo for album songs
                        'audio_song' => $audioPath
                    ]);
                }
            }
            // Confirmar transacción
            DB::commit();
            return response()->json(Album::with('songs')->find($album->id), 201);
        } catch (\Throwable $th) {
            // Revertir si hay error
            DB::rollBack();
            // Eliminar foto subida si ya se había guardado
            if (!empty($data['photo_album']) && Storage::disk('public')->exists($data['photo_album'])) {
                Storage::disk('public')->delete($data['photo_album']);
            }
            return response()->json([
                'message' => 'Error creating album or songs',
                'error' => $th->getMessage()
            ], 500);
        }
    }
    public function updateAlbum(Request $request, $id)
    {
        $data = $request->validate([
            'name_album' => 'sometimes|string|max:127',
            'photo_album' => 'sometimes|image|max:2048',
            'title_song' => 'sometimes|array',
            'title_song.*' => 'string|max:127',
            'audio_song' => 'sometimes|array',
            'audio_song.*' => 'mimes:mp3,wav,ogg,aac,flac|max:10240'
        ]);
        $user = $request->user();
        $artist = Artist::where('user_id', $user->id)->first();
        if (!$artist) {
            return response()->json(['message' => "Artist doesn't exist"], 404);
        }
        $album = Album::where('id', $id)->where('artist_id', $artist->id)->first();

        if (!$album) {
            return response()->json(['message' => "Album doesn't exist"], 404);
        }
        DB::beginTransaction();

        try {
            if (isset($data['name_album'])) {
                $album->update([
                    'name_album' => $data['name_album']
                ]);
            }

            if ($request->hasFile('photo_album')) {
                if ($album->photo_album && Storage::disk('public')->exists($album->photo_album)) {
                    Storage::disk('public')->delete($album->photo_album);
                }

                $newPhoto = $this->storeFile($request, 'photo_album', 'photo_albums');
                $album->update([
                    'photo_album' => $newPhoto
                ]);
            }
            if (isset($data['title_song']) && isset($data['audio_song'])) {
                foreach ($data['title_song'] as $index => $title) {
                    $audioFile = $request->file('audio_song')[$index] ?? null;

                    if (!$audioFile) {
                        throw new \Exception("Audio missing for song at index {$index}");
                    }

                    $audioPath = $audioFile->storeAs(
                        'audio_songs',
                        md5(now() . $audioFile->getClientOriginalName()) . '.' . $audioFile->getClientOriginalExtension(),
                        'public'
                    );

                    Song::create([
                        'artist_id' => $artist->id,
                        'album_id' => $album->id,
                        'title' => $title,
                        'photo_song' => null,
                        'audio_song' => $audioPath
                    ]);
                }
            }
            DB::commit();
            return response()->json(Album::with('songs')->find($album->id), 200);
        } catch (\Throwable $th) {
            DB::rollBack();


            if (isset($newPhoto) && Storage::disk('public')->exists($newPhoto)) {
                Storage::disk('public')->delete($newPhoto);
            }

            return response()->json([
                'message' => 'Error updating album',
                'error' => $th->getMessage()
            ], 500);
        }
    }
    public function deleteAlbum(Request $request, $id)
    {
        $user = $request->user();
        $artist = Artist::where('user_id', $user->id)->first();

        $all = $request->query('all');

        if (!in_array($all, ['yes', 'no'])) {
            return response()->json(['message' => 'Invalid query parameter'], 400);
        }
        if (!$artist) {
            return response()->json(['message' => "Artist doesn't exist"], 404);
        }
        $album = Album::where('id', $id)->where('artist_id', $artist->id)->first();

        if (!$album) {
            return response()->json(['message' => "Album doesn't exist"], 404);
        }

        DB::beginTransaction();
        try {
            if ($all === 'yes') {

                $songs = Song::where('album_id', $album->id)->get();
                foreach ($songs as $song) {
                    if ($song->photo_song && Storage::disk('public')->exists($song->photo_song)) {
                        Storage::disk('public')->delete($song->photo_song);
                    }
                    if ($song->audio_song && Storage::disk('public')->exists($song->audio_song)) {
                        Storage::disk('public')->delete($song->audio_song);
                    }
                    $song->delete();
                }
                if ($album->photo_album && Storage::disk('public')->exists($album->photo_album)) {
                    Storage::disk('public')->delete($album->photo_album);
                }
                $album->delete();
                DB::commit();
                return response()->json(['message' => 'Album and all songs deleted successfully'], 200);
            } else {

                if ($album->photo_album && Storage::disk('public')->exists($album->photo_album)) {
                    Storage::disk('public')->delete($album->photo_album);
                }
                $songs = Song::where('album_id', $album->id)->get();
                foreach ($songs as $song) {
                    $song->album_id = null;
                    $song->save();
                }
                $album->delete();
                DB::commit();
                return response()->json(['message' => 'Album deleted successfully'], 200);
            }
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json(['message' => 'Error deleting album: ' . $th->getMessage()], 500);
        }
    }
    public function deletePhotoAlbum(Request $request,$id)
    {
        $user = $request->user();
        $artist = Artist::where('user_id',$user->id)->first();
        if (!$artist) {
            return response()->json(['message' => "Artist doesn't exist"], 404);
        }
        $album = Album::where('id', $id)->where('artist_id', $artist->id)->first();
        if (!$album) {
            return response()->json(['message' => "Album doesn't exist"], 404);
        }
        if($album->photo_album&&Storage::disk('public')->exists($album->photo_album)){
            Storage::disk('public')->delete($album->photo_album);
        }
        $album->photo_album=null;
        $album->save();
        return response()->json(['message' => 'Photo album has been eliminated correctly'], 200);
    }
    public function createPost(Request $request)
    {
    }
    public function updatePost(Request $request, $id)
    {
    }
    public function deletePost(Request $request,$id)
    {
    }
    public function getPost(Request $request, $id)
    {
    }
    public function getAllPosts(Request $request)
    {
    }
    private function storeFile(Request $request, string $key, string $folder): ?string
    {
        if (!$request->hasFile($key)) return null;

        $file = $request->file($key);
        $filename = md5(now() . $file->getClientOriginalName()) . '.' . $file->getClientOriginalExtension();
        return $file->storeAs($folder, $filename, 'public');
    }
}

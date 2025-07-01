<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Playlist;
use App\Models\Song;
use App\Models\song_reaction;
use Illuminate\Http\Request;

use function Pest\Laravel\delete;

class ListenerController extends Controller
{
    public function getSong($id){
        $song = Song::find($id);
        if(!$song)
        {
            return response()->json(['message'=>'the song does not exists'],404);
        }
        return response()->json($song,200);
    }
    public function getSongs(){
        $songs = Song::all();

        return response()->json($songs,200);
    }
    public function PutSongReaction(Request $request, $id)
    {
        $user = $request->user();

        $type = $request->query('type');

        if(!in_array($type,['like','unlike'])){
            return response()->json(['message'=>'Invalid reaction'],401);
        }

        $song = Song::where('id',$id)->first();

        if(!$song)
        {
            return response()->json(['message'=>'Invalid song'],401);
        }

        $songReaction = song_reaction::where('user_id',$user->id)->where('song_id',$song->id)->first();

        if ($songReaction) {
            try {
                $songReaction->update([
                    'type'=>$type
                ]);

                return response()->json($songReaction,201);
            } catch (\Throwable $th) {
                //throw $th;
                return response()->json(['message'=>'Error updating reaction'],500);
            }
        } else {
            try {
                $songReaction = song_reaction::create([
                    'user_id'=>$user->id,
                    'song_id'=>$song->id,
                    'type'=>$type
                ]);
            } catch (\Throwable $th) {
                //throw $th;
                return response()->json(['message'=>'Error creating reaction'],500);
            }
        }
        
    }
    public function DeleteSongReaction(Request $request, $id)
    {
        $user = $request->user();
        $song = Song::find($id);
        if(!$song){
            return response()->json(['message'=>'Song does not exist'],404);
        }

        $song_reaction = song_reaction::where('user_id',$user->id)->where('song_id',$song->id)->first();

        if(!$song_reaction)
        {
            return response()->json(['message'=>'Song reaction does not exist'],404);
        }

        $song_reaction->delete();

        return response()->json(['message'=>'Song reaction deleted successfully'],200);
    }
    public function follow(Request $request, $id)
    {
    }
    public function getFollows(Request $request)
    {
    }
    public function unfollow(Request $request, $id)
    {
    }
    public function createPlaylist(Request $request)
    {
        $user = $request->user();
        $data = $request->validate([
            'name'=>'required|string|max:127',
            'photo'=>'nullable|image'
        ]);
        
        $filePath = $this->storeFile($request,'photo','playlists');


        $playlist = Playlist::create([
            'user_id'=>$user->id,
            'name'=>$data['name'],
            'photo'=>$filePath??null
        ]);

        if(!$playlist){
            return response()->json(['message'=>'Error creating playlist'],500);
        }

        return response()->json($playlist,201);

    }
    public function deletePlaylist(Request $request,$id)
    {
        $user = $request->user();
        $playlist = Playlist::where('user_id',$user->id)->where('id',$id)->first();
        if(!$playlist)
        {
            return response()->json(['message'=>'Playlist does not exist'],404);
        }
        
        try {
            $playlist->delete();
            return response()->json(['message'=>'Playlist deleted successfully'],200);
        } catch (\Throwable $th) {
            //throw $th;
            return response()->json(['message'=>'Error deleting playlist'],500);
        }
    }
    public function updatePlaylist(Request $request, $id)
    {
        $user = $request->user();
        $playlist = Playlist::where('user_id',$user->id)->where('id',$id)->first();
        if(!$playlist){
            return response()->json(['message'=>'Playlist does not exist'],404);
        }
        $data = $request->validate([
            'name'=>'sometimes|string|max:127',
            'photo'=>'sometimes|image'
        ]);

        if(isset($data['photo']))
        {
            $data['photo']=$this->storeFile($request,'photo','playlists');
        }

        $playlist->update($data);

        return response()->json([$playlist],201);
        
    }
    public function getAllPlaylist(Request $request)
    {
        $user = $request->user();
        $playlists = Playlist::where('user_id',$user->id)->get();

        return response()->json($playlists,200);
    }
    public function getPlaylist(Request $request, $id)
    {
        $playlist  = Playlist::where('id',$id)->first();

        if(!$playlist)
        {
            return response()->json(['message'=>'Playlist does not exist'],404);
        }
        return response()->json($playlist,200);
    }
    public function comment(Request $request,$id)
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

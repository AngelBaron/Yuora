<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Playlist;
use App\Models\Song;
use Illuminate\Http\Request;

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
        
    }
    public function DeleteSongReaction(Request $request, $id)
    {
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
    public function deletePlaylist(Request $request)
    {
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

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Validator;
use Cookie;
use App\Models\PersonalAccessToken;
use App\Models\Share;
use App\Models\Asset;

class ShareController extends Controller
{
    public function store(Request $req)
    {
        [$id] = explode('|', Cookie::get('token') , 2);

        $user_id = PersonalAccessToken::where('id',$id)->select('tokenable_id')->first();

        $asset = Asset::where('user_id', $user_id->tokenable_id)->select('address')->distinct()->count();

        $total_shares = ($asset * 100)/5000000;

        $total_shares = number_format($total_shares, 5);

        $share = new Share();

        $share->user_id = $user_id->tokenable_id;
        $share->total_share = $total_shares;

        if($req->reward)
        {
            $share->reward = $req->reward;
        }

        $share->save();

        if(! empty($share->id))
        {
            return response()->json([
                "message"=>"Data Inserted Successfully",
                "status"=>true,
            ]);
        }
        else 
        {
            return response()->json([
                "message"=>"Something went wrong",
                "status"=>false,
            ]);
        }
    }

    public function fetch()
    {
        [$id] = explode('|', Cookie::get('token') , 2);

        $user_id = PersonalAccessToken::where('id',$id)->select('tokenable_id')->first();

        $share = Share::where('user_id',$user_id->tokenable_id)->get();

        if(! empty($share))
        {
            return response()->json([
                "data"=>$share,
                "status"=>true,
            ]);
        }
        else 
        {
            return response()->json([
                "message"=>"Something went wrong",
                "status"=>false,
            ]);
        }
    }
}

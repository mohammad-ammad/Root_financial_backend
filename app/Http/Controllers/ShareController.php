<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Validator;
use Cookie;
use App\Models\PersonalAccessToken;
use App\Models\Share;
use App\Models\Asset;
use DB;
class ShareController extends Controller
{
    public function store(Request $req)
    {
        $validation = Validator::make($req->all(),[ 
            '_token' => 'required',
        ]);

        if ($validation->fails()) 
        {
            $response['message'] = $validation->messages()->first();
            return response()->json($response);
        }

        [$id] = explode('|', $req->_token , 2);

        $user_id = PersonalAccessToken::where('id',$id)->select('tokenable_id')->first();

        if(! empty($user_id))
        {
            $asset = Asset::where('user_id', $user_id->tokenable_id)->count();

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
        else 
        {
            return response()->json([
                "data"=>"Invalid Token",
                "status"=>false,
            ]);
        }

       
    }

    public function fetch(Request $req)
    {
        $validation = Validator::make($req->all(),[ 
            '_token' => 'required',
        ]);

        if ($validation->fails()) 
        {
            $response['message'] = $validation->messages()->first();
            return response()->json($response);
        }

        [$id] = explode('|', $req->_token , 2);

        $user_id = PersonalAccessToken::where('id',$id)->select('tokenable_id')->first();

        if(! empty($user_id))
        {
            $share = Share::where('user_id',$user_id->tokenable_id)->orderBy('id','desc')->limit(1)->get();

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
        else 
        {
            return response()->json([
                "message"=>"Invalid Token",
                "status"=>false,
            ]);
        }

        
    }
    
    public function fetch_all(Request $req)
    {
        $validation = Validator::make($req->all(),[ 
            '_token' => 'required',
        ]);

        if ($validation->fails()) 
        {
            $response['message'] = $validation->messages()->first();
            return response()->json($response);
        }
        
        [$id] = explode('|', $req->_token , 2);

        $user_id = PersonalAccessToken::where('id',$id)->select('tokenable_id')->first();
        
        
        if(! empty($user_id))
        {
            $share = Share::distinct('user_id')->pluck('user_id');
            
            // return $share;
            
            $response = array();
            $response['data'] = array();
            
            foreach($share as $val)
            {
                $resp['shares'] = Share::where('user_id',$val)->orderBy('id','desc')->limit(1)->get();
                $resp['address'] = Asset::where('user_id',$val)->distinct('address')->select('address')->get();

                array_push($response['data'],$resp);
            }
            
            $response['status'] = true;

            if(! empty($share))
            {
                return $response;
            }
            else 
            {
                return response()->json([
                    "message"=>"Something went wrong",
                    "status"=>false,
                ]);
            }
        }
        else 
        {
            return response()->json([
                "message"=>"Invalid Token",
                "status"=>false,
            ]);
        }
        
    }
}

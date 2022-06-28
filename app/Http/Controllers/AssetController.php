<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Validator;
use App\Models\Asset;
use App\Models\PersonalAccessToken;
use Cookie;

class AssetController extends Controller
{
    public function store(Request $req)
    {
        $validation = Validator::make($req->all(),[ 
            'token' => 'required|unique:assets,token',
            '_token' => 'required',
        ]);

        if ($validation->fails()) 
        {
            $response['message'] = $validation->messages()->first();
            return response()->json($response);
        }

        else 
        {
            [$id] = explode('|', $req->_token , 2);

            $user_id = PersonalAccessToken::where('id',$id)->select('tokenable_id')->first();

            if(! empty($user_id))
            {
                $asset = new Asset();

                $asset->user_id = $user_id->tokenable_id;
                $asset->token = $req->token;

                $asset->save();

                if(! empty($asset->id))
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
    }
}

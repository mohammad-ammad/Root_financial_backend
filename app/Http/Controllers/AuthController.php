<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Validator;
use Hash;
use Cookie;
use App\Models\PersonalAccessToken;
use App\Models\Asset;

class AuthController extends Controller
{
    public function pre_store(Request $req)
    {
        $validation = Validator::make($req->all(),[ 
            'token' => 'required|unique:assets,token',
        ]);

        if ($validation->fails()) 
        {
            $response['message'] = $validation->messages()->first();
            return response()->json($response);
        }
        
        
            $check = User::where('address',$req->address)->first();
            if( !empty($check))
            {
                $userId = $check->id;
            }
            else 
            {
                $validation = Validator::make($req->all(),[ 
                    'address' => 'required|unique:users,address',
                    'token' => 'required|unique:assets,token',
                ]);
        
                if ($validation->fails()) 
                {
                    $response['message'] = $validation->messages()->first();
                    return response()->json($response);
                }

                $user = new User();
                $user->address = $req->address;
                $user->status = 0;
                $user->save();
                $userId = $user->id;
            }
            
            if(! empty($userId))
            {
                $asset = new Asset();

                $asset->user_id = $userId;
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
                    "message"=>"Something went wrong",
                    "status"=>false,
                ]);
            }

        
    }
    public function store(Request $req)
    {
        $validation = Validator::make($req->all(),[ 
            'address' => 'required',
            'password' => 'required',
        ]);

        if ($validation->fails()) 
        {
            $response['message'] = $validation->messages()->first();
            return response()->json($response);
        }

        else 
        {
            $user = User::where('address',$req->address)->first();
            
            if($user->status == 0)
            {
                $user->password = Hash::make($req->password);
                $user->status = 1;
                $user->update();
            }

            $token = $user->createToken('root_finance')->plainTextToken;


            return response()->json([
                "message"=>"User Created Successfully",
                "status"=>true,
                "token" => $token
            ]);
        }
    }

    public function login(Request $req)
    {
        $validation = Validator::make($req->all(),[ 
            'address' => 'required',
            'password' => 'required',
        ]);

        if ($validation->fails()) 
        {
            $response['message'] = $validation->messages()->first();
            return response()->json($response);
        }

        else 
        {
            $user = User::where('address',$req->address)->first();
            
            if(! empty($user))
            {
                if (Hash::check($req->password, $user->password))
                {
                    $token = $user->createToken('root_finance')->plainTextToken;

                    return response()->json([
                        "message"=>"Login Successfully",
                        "status"=>true,
                        "token" => $token
                    ]);
                }
                else 
                {
                    return response()->json([
                        "message"=>"Password not matched",
                        "status"=>false,
                    ]);
                }
            }
            else 
            {
                return response()->json([
                    "message"=>"User not found",
                    "status"=>false,
                ]);
            }
        }
    }

    public function profile(Request $req)
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
            $user = User::find($user_id);

            return response()->json([
                "data"=>$user,
                "status"=>true,
            ]);
        }

        else 
        {
            return response()->json([
                "data"=>"Invalid Token",
                "status"=>false,
            ]);
        }

        
    }

    public function destroy(Request $req)
    {
        return response()->json([
            "message"=>"Logout Successfully",
            "status"=>true
        ])->cookie('token', '', 120);
    }
}

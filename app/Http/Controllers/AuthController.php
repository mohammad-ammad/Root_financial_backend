<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Validator;
use Hash;
use Cookie;
use App\Models\PersonalAccessToken;

class AuthController extends Controller
{
    public function store(Request $req)
    {
        $validation = Validator::make($req->all(),[ 
            'email' => 'required|unique:users,email',
            'password' => 'required',
        ]);

        if ($validation->fails()) 
        {
            $response['message'] = $validation->messages()->first();
            return response()->json($response);
        }

        else 
        {
            $user = new User();
            $user->name = $req->email;
            $user->email = $req->email;
            $user->password = Hash::make($req->password);
            $user->save();

            $token = $user->createToken('root_finance')->plainTextToken;


            return response()->json([
                "message"=>"User Created Successfully",
                "status"=>true,
                "token" => $token
            ])->cookie('token', $token, 120);
        }
    }

    public function login(Request $req)
    {
        $validation = Validator::make($req->all(),[ 
            'email' => 'required',
            'password' => 'required',
        ]);

        if ($validation->fails()) 
        {
            $response['message'] = $validation->messages()->first();
            return response()->json($response);
        }

        else 
        {
            $user = User::where('email',$req->email)->first();
            
            if(! empty($user))
            {
                if (Hash::check($req->password, $user->password))
                {
                    $token = $user->createToken('root_finance')->plainTextToken;


                    return response()->json([
                        "message"=>"Login Successfully",
                        "status"=>true,
                        "token" => $token
                    ])->cookie('token', $token, 120);
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

    public function profile()
    {
        [$id] = explode('|', Cookie::get('token') , 2);

        $user_id = PersonalAccessToken::where('id',$id)->select('tokenable_id')->first();

        $user = User::find($user_id);

        return response()->json([
            "data"=>$user,
            "status"=>true,
        ]);
    }

    public function destroy(Request $req)
    {
        return response()->json([
            "message"=>"Logout Successfully",
            "status"=>true
        ])->cookie('token', '', 120);
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PersonalAccessToken;
use App\Models\Proposer;
use App\Models\Voting;
use App\Models\Asset;
use Validator;
use DateTime;
use Carbon\Carbon;
use DB;


class ProposerController extends Controller
{
    public function store(Request $req)
    {
        $validation = Validator::make($req->all(),[ 
            'address' => 'required',
            'proposal' => 'required',
            'p_id' => 'required',
        ]);

        if ($validation->fails()) 
        {
            $response['message'] = $validation->messages()->first();
            return response()->json($response);
        }

        // [$id] = explode('|', $req->_token , 2);

        // $user_id = PersonalAccessToken::where('id',$id)->select('tokenable_id')->first();

        $user = DB::table('users')->where('address',$req->address)->select('id')->first();

        $user = $user->id;

        // $date= Carbon::now()->addDays(12)->toDateString();
        $date= Carbon::now()->toDateString();

        if(! empty($user))
        {
            $proposer = new Proposer();
            $proposer->user_id = $user;
            $proposer->proposal = $req->proposal;
            $proposer->p_id = $req->p_id;
            $proposer->expires_at = $date;

            $proposer->save();
            
            if(! empty($proposer->id))
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
                "message"=>"Invalid User",
                "status"=>false,
            ]);
        }
    }

    public function vote(Request $req)
    {
        $validation = Validator::make($req->all(),[ 
            'address' => 'required',
        ]);

        if ($validation->fails()) 
        {
            $response['message'] = $validation->messages()->first();
            return response()->json($response);
        }

        // [$id] = explode('|', $req->_token , 2);

        // $user_id = PersonalAccessToken::where('id',$id)->select('tokenable_id')->first();

        $user = DB::table('users')->where('address',$req->address)->select('id')->first();

        $user = $user->id;

        if(! empty($user))
        {

            $date= Carbon::now()->toDateString();

            $proposer = Proposer::find($req->proposer_id);

            if($proposer->status == 1)
            {
                if($proposer->expires_at < $date)
                {
                    $proposer->status = 0;
                    $proposer->update();
                    
                    return response()->json([
                        "message"=>"Portal Closed",
                        "status"=>false,
                    ]);
                }
                else 
                {
                    $check = Voting::where('proposal_id',$req->proposer_id)->where('user_id',$user)->count();
                    
                    if($check == 0)
                    {
                        $asset = Asset::where('user_id',$user)->where('status',1)->count();

                        if($asset > 0)
                        {
                            $vote = new Voting();
                            $vote->proposal_id = $req->proposer_id;
                            $vote->user_id = $user;
                            $vote->status = $req->vote;
                            $vote->power = $asset;
                            $vote->save();

                            return response()->json([
                                "message"=>"Voted Successfully",
                                "status"=>true,
                            ]);
                        }
                        else 
                        {
                            return response()->json([
                                "message"=>"Please Buy Tokens",
                                "status"=>true,
                            ]);
                        }

                    }
                    else 
                    {
                        return response()->json([
                            "message"=>"Already Voted",
                            "status"=>false,
                        ]);
                    }
                }
            }
            else 
            {
                return response()->json([
                    "message"=>"Portal Closed",
                    "status"=>false,
                ]);
            }
        }
        else
        {
            return response()->json([
                "message"=>"Invalid User",
                "status"=>false,
            ]);
        }

    }

    public function fetch($token)
    {
        if ($token == null) 
        {
            return response()->json([
                "message"=>"Invalid User",
                "status"=>false,
            ]);
        }

        // [$id] = explode('|', $token , 2);

        // $user_id = PersonalAccessToken::where('id',$id)->select('tokenable_id')->first();

        $user = DB::table('users')->where('address',$token)->select('id')->first();

        $user = $user->id;
    
        if(! empty($user))
        {
            $data = DB::table('proposer')
            ->leftJoin('users','users.id','=','proposer.user_id')
            ->where('proposer.status','=',1)
            ->select('proposer.proposal','proposer.id','proposer.p_id','proposer.created_at','users.address')
            ->orderBy('proposer.id','desc')
            ->get();

            $response = array();
            $response['data'] = array();

            foreach($data as $val)
            {
                $resp['proposal'] = $val;
                $resp['votes_support'] = DB::table('voting')->where('proposal_id','=',$val->id)->where('status','=',0)->count();
                $resp['votes_oppose'] = DB::table('voting')->where('proposal_id','=',$val->id)->where('status','=',1)->count();
                $resp['votes_neutral'] = DB::table('voting')->where('proposal_id','=',$val->id)->where('status','=',2)->count();
                $resp['user_vote'] = DB::table('voting')->where('proposal_id','=',$val->id)->where('user_id','=',$user)->select('status')->get();
                array_push($response['data'],$resp);
            }


            $raw_data = $response['data'];

            $page = !isset($_GET['page']) ? 1 : $_GET['page'];
            $limit = 20; 
            $offset = ($page - 1) * $limit; 
            $total_items = count($raw_data);
            $total_pages = ceil($total_items / $limit);
            $final = array_splice($raw_data, $offset, $limit);

            return $final;
        }
        else 
        {
            return response()->json([
                "message"=>"Invalid User",
                "status"=>false,
            ]);
        }
    }

    public function power(Request $req)
    {
        $validation = Validator::make($req->all(),[ 
            'address' => 'required',
        ]);

        if ($validation->fails()) 
        {
            $response['message'] = $validation->messages()->first();
            return response()->json($response);
        }

        // [$id] = explode('|', $req->_token , 2);

        // $user_id = PersonalAccessToken::where('id',$id)->select('tokenable_id')->first();

        $user = DB::table('users')->where('address',$req->address)->select('id')->first();

        $user = $user->id;

        if(! empty($user))
        {
            $data = Voting::where('user_id',$user)
            ->select('power')->orderBy('id', 'desc')->limit(1)->get();

            return response()->json([
                "power"=>$data[0]->power,
                "status"=>true,
            ]);
        }
        else 
        {
            return response()->json([
                "message"=>"Invalid User",
                "status"=>false,
            ]);
        }
    }

    public function voting_result()
    {
        $total_users = DB::table('users')->where('status','=',1)->count();
        $check = DB::table('proposer')
        ->leftJoin('voting','voting.proposal_id','=','proposer.id')
        ->where('proposer.status','=',0)
        ->count();

        if($check == 0)
        {
            return response()->json([
                "message"=>"Result pending",
                "status"=>false,
            ]);
        }

        $percentage = ($check/$total_users)*100;
        
        if($percentage >= 60)
        {
            $data = DB::table('proposer')
                ->leftJoin('users','users.id','=','proposer.user_id')
                ->where('proposer.status','=',0)
                ->select('proposer.proposal','proposer.id','proposer.p_id','proposer.created_at','users.address')
                ->orderBy('proposer.id','desc')
                ->get();

                $response = array();
                $response['data'] = array();

                foreach($data as $val)
                {
                    $resp['proposal'] = $val;
                    $resp['votes_support'] = DB::table('voting')->where('proposal_id','=',$val->id)->where('status','=',0)->count();
                    $resp['votes_oppose'] = DB::table('voting')->where('proposal_id','=',$val->id)->where('status','=',1)->count();
                    $resp['votes_neutral'] = DB::table('voting')->where('proposal_id','=',$val->id)->where('status','=',2)->count();
                    
                    array_push($response['data'],$resp);
                }

                $raw_data = $response['data'];

                $page = !isset($_GET['page']) ? 1 : $_GET['page'];
                $limit = 20; 
                $offset = ($page - 1) * $limit; 
                $total_items = count($raw_data);
                $total_pages = ceil($total_items / $limit);
                $final = array_splice($raw_data, $offset, $limit);

                return $final;
        }
        else 
        {
            return response()->json([
                "message"=>"Vote count is less than 60%",
                "status"=>false,
            ]);
        }
        
    }
}

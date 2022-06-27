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
            '_token' => 'required',
            'proposal' => 'required',
            'p_id' => 'required',
        ]);

        if ($validation->fails()) 
        {
            $response['message'] = $validation->messages()->first();
            return response()->json($response);
        }

        [$id] = explode('|', $req->_token , 2);

        $user_id = PersonalAccessToken::where('id',$id)->select('tokenable_id')->first();

        $date= Carbon::now()->addDays(12)->toDateString();

        if(! empty($user_id))
        {
            $proposer = new Proposer();
            $proposer->user_id = $user_id->tokenable_id;
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
                    $check = Voting::where('proposal_id',$req->proposer_id)->where('user_id',$user_id->tokenable_id)->count();
                    
                    if($check == 0)
                    {
                        $asset = Asset::where('user_id',$user_id->tokenable_id)->where('status',1)->count();

                        if($asset > 0)
                        {
                            $vote = new Voting();
                            $vote->proposal_id = $req->proposer_id;
                            $vote->user_id = $user_id->tokenable_id;
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
        // return $token;
        [$id] = explode('|', $token , 2);

        $user_id = PersonalAccessToken::where('id',$id)->select('tokenable_id')->first();
    
        if(! empty($user_id))
        {
            $data = DB::table('proposer')
            ->leftJoin('assets', function ($leftJoin) {
                $leftJoin->on('proposer.user_id', '=', 'assets.user_id')
                        ->where('assets.created_at', '=', DB::raw("(select max(`created_at`) from assets)"));
            })
            ->where('proposer.status','=',1)
            ->select('proposer.proposal','proposer.id','proposer.p_id','proposer.created_at','assets.address')
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
                $resp['user_vote'] = DB::table('voting')->where('proposal_id','=',$val->id)->where('user_id','=',$user_id->tokenable_id)->select('status')->get();
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
            $data = Voting::where('user_id',$user_id->tokenable_id)
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
        $data = DB::table('proposer')
            ->leftJoin('assets', function ($leftJoin) {
                $leftJoin->on('proposer.user_id', '=', 'assets.user_id')
                        ->where('assets.created_at', '=', DB::raw("(select max(`created_at`) from assets)"));
            })
            ->where('proposer.status','=',0)
            ->select('proposer.proposal','proposer.id','proposer.p_id','proposer.created_at','assets.address')
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
}

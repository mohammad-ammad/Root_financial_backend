<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use DB;

class DemoCron extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'demo:cron';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $date= Carbon::now()->toDateString();

        // DB::table('proposer')->where('status','=',1)->where('expires_at','=',$date)
        // ->update(array('status'=> 0));
        // $arr = array(['proposal_id'=>1,'user_id'=>1,'status'=>2,'power'=>1]);
        // DB::table('voting')->insert($arr);
    }
}

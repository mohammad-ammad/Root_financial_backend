<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use DB;
use Carbon\Carbon;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */

    // protected $commands = [
    //     // Commands\DemoCron::class,
    //     'App\Console\Commands\DemoCron',
    // ];

    protected function schedule(Schedule $schedule)
    {
        $schedule->call(function () {
            $date= Carbon::now()->toDateString();
            DB::table('proposer')->where('status','=',1)->where('expires_at','=',$date)
            ->update(array('status'=> 0));
        })->everyThreeMinutes();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}

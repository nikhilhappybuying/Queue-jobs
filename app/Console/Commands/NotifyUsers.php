<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use App\Models\Message;
use App\Jobs\SendMailJob;
use App\Mail\NewArrivals;
use Illuminate\Console\Command;

class NotifyUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notify:users';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send an email to users';

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
        // return Command::SUCCESS;
        //One hour is added to compensate for PHP being one hour faster 
        $now = date("Y-m-d H:i", strtotime(Carbon::now()->addHour()));
        logger($now);

        $messages = Message::get();
        if($messages !== null){
            //Get all messages that their dispatch date is due
            $messages->where('date_string',  $now)->each(function($message) {
                if($message->delivered == 'NO')
                {
                    $users = User::all();
                    foreach($users as $user) {
                        dispatch(new SendMailJob(
                            $user->email, 
                            new NewArrivals($user, $message))
                        );
                    }
                    $message->delivered = 'YES';
                    $message->save();   
                }
            });
        }
    }
}

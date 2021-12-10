<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use App\Services\Mail\testmail;

class QueueJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    public $send;
    public $detail;
    /**
     * Create a new job instance.
     *
     * @return void
     */

    public function __construct($sendto, $details)

    {
        $this->send = $sendto;
        $this->detail = $details;
    }


    /**
    * Execute the job.
    *
    * @return void
    */
    public function handle()
    {
        Mail::to($this->send)->send(new testmail($this->detail));
    }
}

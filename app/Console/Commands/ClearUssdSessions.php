<?php

namespace App\Console\Commands;

use App\Models\UssdSession;
use Carbon\Carbon;
use Illuminate\Console\Command;

class ClearUssdSessions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:clear-ussd-sessions';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command to clear ussd sessions at regular intervals';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        UssdSession::query()
            ->where("created_at", "<", Carbon::now()->subMinutes(10))
            ->delete();
    }
}

<?php

namespace App\Console\Commands;

use App\Http\Controllers\LocationController;
use Illuminate\Console\Command;

class SetCitiesExactLocationCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'city:define';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Set full address for cities from database.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        if (LocationController::setCitiesExactLocation()) {
            $this->info("All cities records updated!");
        } else {
            $this->error("Cannot set all cities exact address.");
        }
    }
}

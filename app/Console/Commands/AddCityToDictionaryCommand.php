<?php

namespace App\Console\Commands;

use App\Http\Controllers\LocationController;
use Illuminate\Console\Command;

class AddCityToDictionaryCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'city:add {city}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Add city to dictionary';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        if ($this->argument("city") == null) {
            $this->error("You cannot add empty city name to database.");
            return Command::FAILURE;
        }

        if (LocationController::createCity($this->argument("city"))) {
            $this->info("City has been added to database!");
        } else {
            $this->error("City already exists in database.");
        }
    }
}

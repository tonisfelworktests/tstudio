<?php

namespace App\Console\Commands;

use App\Http\Controllers\LocationController;
use Illuminate\Console\Command;

class ShowDirectionsTableCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'city:directions {--inner=?} {--federal=?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $isInnerNotEmpty = $this->hasOption("inner") && $this->option("inner") != "";
        $isFederalNotEmpty = $this->hasOption("federal") && $this->option("federal") != "";

        if ($this->option("inner") == "?" && $this->option("federal") == "?") {
            $type = LocationController::DEFAULT_ALGORYTHM;
        } elseif ($this->hasOption("inner") && $this->hasOption("federal")) {
            if (!($isInnerNotEmpty && $isFederalNotEmpty)) {
                $this->error("You didn't set \"inner\" and \"federal\" arguments.");
                return Command::FAILURE;
            } elseif (!$isInnerNotEmpty) {
                $this->error("You didn't set \"inner\" argument.");
                return Command::FAILURE;
            } elseif (!$isFederalNotEmpty) {
                $this->error("You didn't set \"federal\" argument.");
                return Command::FAILURE;
            }
            $type = LocationController::BOTH_REGIONS;
        } elseif ($this->hasOption("inner")) {
            if (!$isInnerNotEmpty) {
                $this->error("You didn't set \"inner\" argument.");
                return Command::FAILURE;
            }
            $type = LocationController::INNER_REGION_ID;
        } elseif ($this->hasOption("federal")) {
            if (!$isFederalNotEmpty) {
                $this->error("You didn't set \"federal\" argument.");
                return Command::FAILURE;
            }
            $type = LocationController::FEDERAL_REGION_ID;
        }

        LocationController::showDirections($type, $this->option("inner"), $this->option("federal"));
    }
}

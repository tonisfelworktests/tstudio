<?php

namespace App\Console\Commands;

use App\Http\Controllers\LocationController;
use App\Models\City;
use App\Models\Direction;
use Illuminate\Console\Command;

class ShowDirectionsTableCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'city:directions {--inner=?} {--federal=?} {--update}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Show table of directions records.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        if ($this->hasOption("update") && $this->option("update")) {
            $cities = City::all();
            foreach ($cities as $city) {
                $d = new Direction();
                $d->cityId = $city->id;
                $d->regionId = $city->region()->id;
                $d->federalRegionId = $city->region()->federalRegion()->id;
                $d->save();
            }

            $this->info("Direction table has been filled of existing information.");
            return Command::SUCCESS;
        }

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

        $directions = LocationController::getDirections($type, $this->option("inner"), $this->option("federal"));
        $this->table(["ID", "City", "Region", "Federal region"], $directions);
    }
}

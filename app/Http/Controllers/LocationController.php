<?php

namespace App\Http\Controllers;

use App\Models\City;
use App\Models\Direction;
use App\Models\FederalRegion;
use App\Models\Region;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Psr7\Request as GuzzleRequest;
use GuzzleHttp\Client as GuzzleClient;

class LocationController extends Controller
{
    private const API_URL = "https://cleaner.dadata.ru/api/v1/clean/address";

    public const DEFAULT_ALGORYTHM = 0;
    public const INNER_REGION_ID = 1;
    public const FEDERAL_REGION_ID = 2;
    public const BOTH_REGIONS = 4;

    public static function createCity(string $name) : bool {
        $dbStatement = City::where("name", $name);

        if ($dbStatement->count() > 0) {
            return false;
        }

        $city = new City();
        $city->name = $name;
        $city->save();

        return true;
    }

    public static function setCitiesExactLocation() : bool {
        $apiToken = env('DADATA_API_KEY');
        $secretToken = env('DADATA_SECRET_KEY');
        $fullAddresses = [];

        $contentFromDB = City::select(["cities.name as city", "inner_regions.name as region, federal_regions.name as federal_region"])
            ->leftJoin("inner_regions", "inner_regions.id", "=", "cities.regionId")
            ->leftJoin("federal_regions", "federal_regions.id", "=", "inner_regions.federalRegionId")
            ->get();
        foreach ($contentFromDB as $item) {
            $fullAddresses[] = "\"{$item->federal_region} {$item->region} {$item->city}\", ";
        }

        try {
            $client = new GuzzleClient();
            $response = $client->request("POST", "http://cleaner.dadata.ru/api/v1/clean/address", [
                "headers" => [
                    "Content-Type" => "application/json",
                    "Authorization" => "Token $apiToken",
                    "X-Secret" => $secretToken
                ],
                "body" => json_encode($fullAddresses)
            ]);
            echo "Response is received!" . PHP_EOL;
        } catch (ClientException $e) {
            echo "Response did not receive!" . PHP_EOL;
            exit;
        }

        $responseContent = json_decode($response->getBody()->getContents());
        foreach ($responseContent as &$info) {
            if (!is_null($info->unparsed_parts)) {
                return false;
            }

            $federalRegion = $info->federal_district;
            $region = $info->region;
            $city = $info->city;

            $federalRegionOperation = FederalRegion::firstOrNew(["name" => $federalRegion]);
            if ($federalRegionOperation->exists) {
                $federalRegion = $federalRegionOperation->id;
            } else {
                $federalRegionOperation->save();
                $federalRegion = $federalRegionOperation->id;
            }

            Region::whereNull("federalRegionId")->delete();
            $regionOperation = Region::firstOrNew(["name" => $region, "federalRegionId" => $federalRegion]);
            if ($regionOperation->exists) {
                $region = $regionOperation->id;
            } else {
                $regionOperation->save();
                $region = $regionOperation->id;
            }

            City::whereNull("regionId")->delete();
            City::firstOrCreate(["name" => $city, "regionId" => $region]);
        }

        return true;
    }

    public static function showDirections(int $tableType, string $innerRegion = "", string $federalRegion = "") : void {
        $table = Direction::select(["cities.id as id", "cities.name as city", "inner_regions.name as region", "federal_regions.name as federal_region"])
            ->leftJoin("cities", "cities.id", "=", "directions.cityId")
            ->leftJoin("inner_regions", "inner_regions.id", "=", "directions.regionId")
            ->leftJoin("federal_regions", "federal_regions.id", "=", "directions.federalRegionId");
        switch($tableType) {
            case self::INNER_REGION_ID:
                $table = $table->where("inner_regions.name", $innerRegion);
                break;
            case self::FEDERAL_REGION_ID:
                $table = $table->where("federal_regions.name", $federalRegion);
                break;
            case self::BOTH_REGIONS:
                $table = $table->where("inner_regions.name", $innerRegion)
                    ->where("federal_regions.name", $federalRegion);
                break;
            default:
                break;
        }
        $table = $table->get();

        echo "Directions list:" . PHP_EOL;
        $headerMask = "|%-5.5s | %-15.10s | %-20.20s | %-21.20s |\n";
        $hrMask = "|%'-6.5s|%'-17.10s|%'-22.20s|%'-23.20s|\n";
        $mask = "|%5.5g | %20.14s | %20.20s | %15s |\n";
        printf($headerMask, "ID", "City", "Region", "Federal region");
        printf($hrMask, "-","-","-","-");
        //printf("'-");
        foreach ($table as $line) {
            printf($mask, $line->id, trim($line->city), trim($line->region), trim($line->federal_region));
        }
    }
}

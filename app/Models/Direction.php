<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Direction extends Model
{
    use HasFactory;

    protected $table = "directions";
    protected $fillable = ["cityId", "regionId", "federalRegionId"];

    public function city() {
        return City::find($this->cityId) ?? "<пусто>";
    }

    public function region() {
        return Region::find($this->regionId) ?? "<пусто>";
    }

    public function federalRegion() {
        return FederalRegion::find($this->federalRegionId) ?? "<пусто>";
    }
}

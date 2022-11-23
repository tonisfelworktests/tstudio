<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Region extends Model
{
    use HasFactory;

    protected $table = "inner_regions";
    protected $fillable = ["name", "federalRegionId"];

    public function federalRegion() {
        return FederalRegion::find($this->federalRegionId) ?? "<пусто>";
    }
}

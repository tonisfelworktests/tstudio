<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FederalRegion extends Model
{
    use HasFactory;

    protected $table = "federal_regions";
    protected $fillable = ["name"];
}

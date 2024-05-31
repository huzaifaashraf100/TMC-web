<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tender extends Model
{
    use HasFactory;
    protected $table = "tenders";
    protected $fillable = [
        'description',
        'department',
        'diary_no',
        'tender_date',
        'opening_date',
        'pdf_file'
    ];
}

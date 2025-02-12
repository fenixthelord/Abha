<?php

namespace Database\Seeders;

use App\Models\Position;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class FirstPositionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        
        Position::create([
            "name"=> ["en" => "first position" , "ar" => "الرئيس"],
            "parent_id" => null ,
        ]);
    }
}

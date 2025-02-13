<?php

namespace Database\Seeders;

use App\Models\Position;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FirstPositionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            "name" => ["en" => "first position", "ar" => "الرئيس"],
            "parent_id" => null,
        ];

        if (!Position::where("parent_id" , null)->first()){
            Position::create($data);
        }
    }
}

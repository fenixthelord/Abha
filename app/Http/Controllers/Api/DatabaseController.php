<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Traits\ResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DatabaseController extends Controller
{
    use ResponseTrait;

    public function getTables(Request $request)
    {
        try {
            $tables = DB::select('SHOW TABLES');
            $key = 'Tables_in_' . env('DB_DATABASE');
            $tableNames = array_map(fn($table) => $table->$key, $tables);

            if ($request->has('search')) {
                $search = strtolower($request->search);
                $tableNames = array_filter($tableNames, fn($table) => str_contains(strtolower($table), $search));
            }
            $data["names"] = array_values($tableNames);
            return $this->returnData($data);
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    public function getColumns(Request $request)
    {
        try {
            $table = $request->table;
            $columns = DB::select("SHOW COLUMNS FROM $table");
            $columnNames = array_map(fn($column) => $column->Field, $columns);

            if ($request->has('search')) {
                $search = strtolower($request->search);
                $columnNames = array_filter($columnNames, fn($table) => str_contains(strtolower($table), $search));
            }
            $data["names"] = array_values($columnNames);
            return $this->returnData($data);
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }
}

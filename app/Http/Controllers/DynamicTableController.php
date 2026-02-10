<?php

namespace App\Http\Controllers;

use App\Models\ModuleRegistry;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class DynamicTableController extends Controller
{
    private function validateTable($tableName)
    {
        $registry = ModuleRegistry::where('table_name', $tableName)
            ->where('status', true)
            ->first();
        
        if (!$registry) {
            throw new Exception("Table not registered or inactive");
        }
        
        return $registry;
    }

    private function getConnection($databaseName)
    {
        if ($databaseName && $databaseName !== config('database.connections.pgsql.database')) {
            $config = config('database.connections.pgsql');
            $config['database'] = $databaseName;
            config(['database.connections.dynamic' => $config]);
            DB::purge('dynamic');
            return DB::connection('dynamic');
        }
        return DB::connection();
    }

    // Get table structure with column details
    public function getTableStructure(Request $req)
    {
        $validated = Validator::make($req->all(), ['tableName' => 'required|string']);
        if ($validated->fails()) {
            return validationError($validated);
        }

        try {
            $this->validateTable($req->tableName);
            
            $columns = DB::select("
                SELECT column_name, data_type, is_nullable, column_default
                FROM information_schema.columns 
                WHERE table_name = ? 
                AND table_schema = 'public'
                ORDER BY ordinal_position
            ", [$req->tableName]);

            return responseMsgs(true, "Table Structure", $columns, "DYN001", "01", responseTime(), $req->getMethod(), $req->deviceId);
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "", "DYN001", "01", responseTime(), $req->getMethod(), $req->deviceId);
        }
    }

    // List all records from a table
    public function listTableData(Request $req)
    {
        $validated = Validator::make($req->all(), ['tableName' => 'required|string']);
        if ($validated->fails()) {
            return validationError($validated);
        }

        try {
            $registry = $this->validateTable($req->tableName);
            $connection = $this->getConnection($registry->database_name);
            
            $data = $connection->table($req->tableName)->get();
            
            return responseMsgs(true, "Table Data", remove_null($data), "DYN002", "01", responseTime(), $req->getMethod(), $req->deviceId);
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "", "DYN002", "01", responseTime(), $req->getMethod(), $req->deviceId);
        }
    }

    // Get single record by ID from a table
    public function getTableRecord(Request $req)
    {
        $validated = Validator::make($req->all(), [
            'tableName' => 'required|string',
            'id' => 'required|integer'
        ]);
        if ($validated->fails()) {
            return validationError($validated);
        }

        try {
            $registry = $this->validateTable($req->tableName);
            $connection = $this->getConnection($registry->database_name);
            
            $data = $connection->table($req->tableName)->where('id', $req->id)->first();
            $message = $data ? "Record Details" : "Record not found";
            $status = (bool)$data;
            
            return responseMsgs($status, $message, remove_null($data), "DYN003", "01", responseTime(), $req->getMethod(), $req->deviceId);
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "", "DYN003", "01", responseTime(), $req->getMethod(), $req->deviceId);
        }
    }

    // Create new record in a table
    public function createTableRecord(Request $req)
    {
        $validated = Validator::make($req->all(), [
            'tableName' => 'required|string',
            'data' => 'required|array'
        ]);
        if ($validated->fails()) {
            return validationError($validated);
        }

        try {
            $registry = $this->validateTable($req->tableName);
            $connection = $this->getConnection($registry->database_name);
            
            $id = $connection->table($req->tableName)->insertGetId($req->data);
            
            return responseMsgs(true, "Record created successfully", ['id' => $id], "DYN004", "01", responseTime(), $req->getMethod(), $req->deviceId);
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "", "DYN004", "01", responseTime(), $req->getMethod(), $req->deviceId);
        }
    }

    // Update existing record in a table
    public function updateTableRecord(Request $req)
    {
        $validated = Validator::make($req->all(), [
            'tableName' => 'required|string',
            'id' => 'required|integer',
            'data' => 'required|array'
        ]);
        if ($validated->fails()) {
            return validationError($validated);
        }

        try {
            $registry = $this->validateTable($req->tableName);
            $connection = $this->getConnection($registry->database_name);
            
            $updated = $connection->table($req->tableName)
                ->where('id', $req->id)
                ->update($req->data);
            
            $message = $updated ? "Record updated successfully" : "Record not found or no changes made";
            $status = (bool)$updated;
            
            return responseMsgs($status, $message, "", "DYN005", "01", responseTime(), $req->getMethod(), $req->deviceId);
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "", "DYN005", "01", responseTime(), $req->getMethod(), $req->deviceId);
        }
    }

    // Delete record from a table
    public function deleteTableRecord(Request $req)
    {
        $validated = Validator::make($req->all(), [
            'tableName' => 'required|string',
            'id' => 'required|integer'
        ]);
        if ($validated->fails()) {
            return validationError($validated);
        }

        try {
            $registry = $this->validateTable($req->tableName);
            $connection = $this->getConnection($registry->database_name);
            
            $deleted = $connection->table($req->tableName)->where('id', $req->id)->delete();
            $message = $deleted ? "Record deleted successfully" : "Record not found";
            $status = (bool)$deleted;
            
            return responseMsgs($status, $message, "", "DYN006", "01", responseTime(), $req->getMethod(), $req->deviceId);
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "", "DYN006", "01", responseTime(), $req->getMethod(), $req->deviceId);
        }
    }

    // Get module data completion percentage
    public function getModuleDataPercentage(Request $req)
    {
        $validated = Validator::make($req->all(), ['moduleId' => 'required|integer']);
        if ($validated->fails()) {
            return validationError($validated);
        }

        try {
            $tables = ModuleRegistry::where('module_id', $req->moduleId)
                ->where('status', true)
                ->get();

            $status = !$tables->isEmpty();
            $message = $status ? "Module Data Completion Percentage" : "No tables registered for this module";
            $data = "";

            if ($status) {
                $totalTables = $tables->count();
                $tablesWithData = 0;

                foreach ($tables as $table) {
                    $connection = $this->getConnection($table->database_name);
                    $count = $connection->table($table->table_name)->count();
                    if ($count > 0) {
                        $tablesWithData++;
                    }
                }

                $percentage = round(($tablesWithData / $totalTables) * 100, 2);

                $data = [
                    'total_tables' => $totalTables,
                    'tables_with_data' => $tablesWithData,
                    'tables_without_data' => $totalTables - $tablesWithData,
                    'completion_percentage' => $percentage
                ];
            }

            return responseMsgs($status, $message, $data, "DYN007", "01", responseTime(), $req->getMethod(), $req->deviceId);
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "", "DYN007", "01", responseTime(), $req->getMethod(), $req->deviceId);
        }
    }
}

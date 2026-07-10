<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SqlController extends Controller
{
    /**
     * Show the SQL terminal.
     */
    public function index()
    {
        return view('admin.sql');
    }

    /**
     * Execute the SQL query.
     */
    public function execute(Request $request)
    {
        $request->validate([
            'query' => 'required|string',
        ]);

        $query = trim($request->query('query', $request->input('query')));
        
        // Remove trailing semicolon if present
        if (str_ends_with($query, ';')) {
            $query = substr($query, 0, -1);
        }

        try {
            $isSelect = Str::startsWith(Str::lower($query), 'select') || Str::startsWith(Str::lower($query), 'show') || Str::startsWith(Str::lower($query), 'describe') || Str::startsWith(Str::lower($query), 'explain');

            if ($isSelect) {
                $results = DB::select($query);
                
                if (empty($results)) {
                    return response()->json([
                        'status' => 'success',
                        'message' => 'Query executed successfully, but returned no results.',
                        'type' => 'empty'
                    ]);
                }

                // Convert results to array of arrays for easier table rendering
                $data = array_map(function($item) {
                    return (array) $item;
                }, $results);

                $columns = array_keys($data[0]);

                return response()->json([
                    'status' => 'success',
                    'columns' => $columns,
                    'rows' => $data,
                    'count' => count($data),
                    'type' => 'select'
                ]);
            } else {
                $affected = DB::statement($query);
                return response()->json([
                    'status' => 'success',
                    'message' => 'Query executed successfully.',
                    'affected' => $affected,
                    'type' => 'statement'
                ]);
            }
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 422);
        }
    }
}

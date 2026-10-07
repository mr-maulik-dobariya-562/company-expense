<?php

namespace App\Imports;

use App\Models\Stock;
use App\Models\StockDetail;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToCollection;

class StockImport implements ToCollection
{
    /**
     * @param Collection $collection
     */
    public function collection(Collection $collection)
    {
        $collection = collect($collection); // Ensure it's a Laravel Collection

        // Extract the "As On" date safely
        $asOnRow = $collection->first(function ($row) {
            return Str::contains($row[0] ?? '', 'As On:');
        });

        $date = null;
        if ($asOnRow) {
            preg_match('/\d{1,2}-\d{1,2}-\d{4}/', $asOnRow[0], $matches);
            $date = $matches[0] ?? null;
        }

        // Loop through the collection
        foreach ($collection as $key => $value) {
            // Ensure we have enough elements in the array
            if (!isset($value[0], $value[1], $value[4])) {
                continue; // Skip rows that do not have required data
            }

            // Process Parent Item (Stock)
            if ($value[2] == null && $value[3] == null) {
                $stock = Stock::create([
                    'date' => date('Y-m-d', strtotime($date)),
                    'item_name' => trim($value[0]),
                    'qty' => (float) str_replace(',', '', $value[1]),
                    'amount' => (float) str_replace(',', '', $value[4]),
                    'created_by' => auth()->user()->id
                ]);

                $id = $stock->id; // Save the last inserted stock ID
            }

            // Process Stock Detail (If $id exists and additional fields are available)
            elseif ($value[2] != null && $value[3] != null && is_numeric((int) $value[2]) && is_numeric((int) $value[3])) {
                if (!isset($id)) {
                    continue; // Skip if no stock ID is available
                }

                StockDetail::create([
                    'stock_id' => $id,
                    'date' => date('Y-m-d', strtotime($date)),
                    'item_name' => trim($value[0]),
                    'qty' => (float) str_replace(',', '', $value[1]),
                    'unit_name' => trim($value[2]),
                    'price' => (float) str_replace(',', '', $value[3]),
                    'amount' => (float) str_replace(',', '', $value[4]),
                    'created_by' => auth()->user()->id
                ]);
            }
        }
    }
}

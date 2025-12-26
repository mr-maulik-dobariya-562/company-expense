<?php

namespace App\Traits;

use Illuminate\Http\Request;

trait ToggleStatus
{
    /**
     * @param Request $request
     * @return $this|false|string
     */

    public function toggleStatus($status, $model, $id, $column = "status")
    {
        $model = "App\Models\\" . $model;
        if ($status) {
            return $model::where('id', $id)->update([
                $column => 0
            ]);
        } else {
           return $model::where('id', $id)->update([
                $column => 1
            ]);
        }
    }
}
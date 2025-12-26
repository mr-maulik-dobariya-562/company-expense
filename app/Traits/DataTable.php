<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

trait DataTable
{
    public static $createFormatArrayCallBack;
    public static $createWhereCallback;
    public $searchableColumns = [];
    protected $enableDateFiltering = false;
    protected $dateFilterColumn = 'created_at';
    protected $order = [
        "column" => "id",
        "order" => "DESC",
    ];
    /**
     * @var Model
     * */
    public $model;
    public $with = [];
    public $filters = [];
    public function getListAjax(array $searchableColumns = [])
    {
        $request = request();
        $draw = intval($request->post('draw'));
        $start = intval($request->post('start'));
        $rowPerPage = intval($request->post('length'));
        $columnIndex = $request->post('order')[0]['column'] ?? null;
        $columnName = $request->post('columns')[$columnIndex]['data'] ?? $this->order['order'];
        $columnSortOrder = $request->post('order')[0]['dir'] ?? $this->order['order'];
        $searchValue = $request->post('search')['value'] ?? null;

        $model = $this->model;

        $query = $model::query();

        if ($this->with) {
            $query->with($this->with);
        }
        $searchableColumns = [...$searchableColumns, ...$this->searchableColumns];

        $totalRecords = $query->count();

        $query = $this->applyFilters($query, $this->filters);

        $query = $this->applySearch($query, $searchableColumns ?? [], $searchValue);

        // if (is_callable(static::$createWhereCallback)) {
        //     $query = call_user_func(static::$createWhereCallback, $query);
        // }

        if (is_callable(static::$createWhereCallback)) {
            $modifiedQuery = call_user_func(static::$createWhereCallback, $query);
            if ($modifiedQuery instanceof Builder) {
                $query = $modifiedQuery;
            } else {
                Log::error('createWhereCallback did not return a valid query object.');
            }
        }

        $totalRecordWithFilter = $query->count();

        /**
         * @var  Model $query
         * */

        $records = $query
            ->skip($start)
            ->take($rowPerPage)
            ->orderBy(in_array($columnName, (new $this->model)->getFillable()) ? $columnName : $this->order['column'], $columnSortOrder)
            ->get()
        ;

        // dd($records->toSql());

        $data = $this->formatDataTableRows($records);

        return response()->json([
            "draw" => intval($draw),
            "iTotalRecords" => $totalRecords,
            "iTotalDisplayRecords" => $totalRecordWithFilter,
            "aaData" => $data
        ]);
    }
    private function formatDataTableRows($records)
    {
        $data = [];
        $isCallable = is_callable(static::$createFormatArrayCallBack);
        foreach ($records as $index => $record) {

            if ($isCallable) {

                $data[] = call_user_func(static::$createFormatArrayCallBack, $record, $index);
            } else {

                if (method_exists($this, 'getData'))

                    $data[] = $this->getData($record, $index);
                else
                    $data[] = $record;
            }
        }
        return $data;
    }
    public function formateArray(\Closure $createFormatArrayCallBack)
    {
        static::$createFormatArrayCallBack = $createFormatArrayCallBack;
        return $this;
    }
    public function where(\Closure $createWhereCallback)
    {
        static::$createWhereCallback = $createWhereCallback;
        return $this;
    }
    public function orderBy($columName = "id", $order = "ASC")
    {
        $this->order = [
            "column" => $columName,
            "order" => $order
        ];
        return $this;
    }

    public function filter(array $filters)
    {
        $this->filters = $filters;
        return $this;
    }
    protected function applyFilters($query, array $filters)
    {
        foreach ($filters ?? [] as $filter => $value) {
            if (strpos($filter, ':') !== false) {
                list($relation, $relatedColumn) = explode(':', $filter, 2);
                if (is_array($value) && !empty($value)) {
                    $query->whereHas($relation, function ($q) use ($relatedColumn, $value) {
                        $q->whereIn($relatedColumn, $value);
                    });
                } else if (!empty($value)) {
                    $query->whereHas($relation, function ($q) use ($relatedColumn, $value) {
                        $q->where($relatedColumn, $value);
                    });
                }
            } elseif (method_exists($this->model, 'scope' . ucfirst($filter))) {
                // Apply the scope if it exists in the model
                $query->$filter($value);
            } else {
                if (is_array($value) && !empty($value)) {
                    $query->whereIn($filter, $value);
                } else if (!empty($value)) {
                    $query->where($filter, $value);
                }
            }
        }

        if ($this->enableDateFiltering) {
            $this->applyDateFilters($query);
        }
        return $query;
    }
    protected function applySearch($query, array $searchableColumns, $searchValue)
    {
        $searchValue = trim($searchValue);

        if (!empty($searchValue)) {
            $query->where(function ($q) use ($searchableColumns, $searchValue) {
                foreach ($searchableColumns as $column) {
                    if (strpos($column, ':') != false) {
                        list($relationName, $fields) = explode(':', $column, 2);
                        $q->orWhereHas($relationName, function ($qr) use ($fields, $searchValue) {
                            $fields = explode(',', $fields);
                            foreach ($fields as $field) {
                                $field = trim($field);
                                $qr->where($field, 'like', '%' . $searchValue . '%');
                            }
                        });
                    } else {
                        $q->orWhere($column, 'like', '%' . $searchValue . '%');
                    }
                }
            });
        }
        return $query;
    }
    public function model($model, $with = [])
    {
        $this->model = $model;
        $this->with = $with;
        return $this;
    }

    protected function applyDateFilters($query)
    {
        $filters = [];
        $filters['from_date'] = request('from_date');
        $filters['to_date'] = request('to_date');

        if (!empty($filters['from_date']) && !empty($filters['to_date'])) {
            $toDate = Carbon::parse($filters['to_date'])->endOfDay();
            $query->where($this->dateFilterColumn, '>=', $filters['from_date'])
                ->where($this->dateFilterColumn, '<=', $toDate);
        } else if (!empty($filters['from_date'])) {
            $query->where($this->dateFilterColumn, '>=', $filters['from_date']);
        } else if (!empty($filters['to_date'])) {
            $toDate = Carbon::parse($filters['to_date'])->endOfDay();
            $query->where($this->dateFilterColumn, '<=', $toDate);
        }
    }

    public function enableDateFilters($column = 'created_at')
    {
        $this->enableDateFiltering = true;
        $this->dateFilterColumn = $column;
        return $this;
    }

    public function searchAbleColumns($searchAbleColumns = [])
    {
        $this->searchableColumns = $searchAbleColumns;
        return $this;
    }
}

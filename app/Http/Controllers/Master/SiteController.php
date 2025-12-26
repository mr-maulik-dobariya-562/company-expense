<?php

namespace App\Http\Controllers\Master;

use App\Models\Site;
use App\Models\Buyer;
use App\Traits\DataTable;
use App\Models\WorkCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\BuyerSingle;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Validation\Rule;

class SiteController extends Controller implements HasMiddleware
{
    use DataTable;

    public static function middleware(): array
    {
        return [
            new Middleware('permission:site-create', only: ['create']),
            new Middleware('permission:site-view', only: ['index', "getList"]),
            new Middleware('permission:site-edit', only: ['edit', "update"]),
            new Middleware('permission:site-delete', only: ['destroy']),
        ];
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $work_category = WorkCategory::all();
        $buyer = BuyerSingle::all();
        return view('Master::site.index', compact('work_category', 'buyer'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            "name" => "required|unique:sites,name",
            "work_category_id" => "required",
            "buyer_id" => "required|array|min:1",
        ]);

        $sites = Site::create([
            "name" => $request->name,
            "work_category_id" => $request->work_category_id,
            "created_by" => auth()->id()
        ]);

        foreach ($request->buyer_id as $key => $value) {
            Buyer::create([
                "site_id" => $sites->id,
                "buyer_single_id" => $value,
                "created_by" => auth()->id()
            ]);
        }

        if ($request->ajax()) {
            return $this->withSuccess("Site created successfully");
        }
        return $this->withSuccess("Site created successfully")->back();
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Site $site)
    {
        // dd($request->all());
        $request->validate([
            "name" => ["required", Rule::unique('sites', 'name')->ignore($site->id)],
            "work_category_id" => "required",
            "buyer_id" => "required|array|min:1",
        ]);

        try {
            DB::beginTransaction();
            $site->update([
                "name" => $request->name,
                "work_category_id" => $request->work_category_id
            ]);

            $existingBuyers = Buyer::where("site_id", $site->id)->pluck("buyer_single_id")->toArray();
            $newBuyers = $request->buyer_id;
            $buyersToDelete = array_diff($existingBuyers, $newBuyers);
            Buyer::where("site_id", $site->id)->whereIn("buyer_single_id", $buyersToDelete)->delete();
            $buyersToAdd = array_diff($newBuyers, $existingBuyers);
            foreach ($buyersToAdd as $buyerSingleId) {
                Buyer::create([
                    "site_id" => $site->id,
                    "buyer_single_id" => $buyerSingleId,
                    "created_by" => auth()->id()
                ]);
            }
            // $buyers = Buyer::where("site_id", $site->id)->pluck("id")->toArray();
            // $newBuyers = [];
            // if (!empty($request->buyer_id) && is_array($request->buyer_id)) {
            //     foreach ($request->buyer_id as $key => $value) {
            //         if (isset($request->buyer_id[$key])) {
            //             if (in_array($request->buyer_id[$key], $buyers)) {
            //                 Buyer::where(["id" => $request->buyer_id[$key], "site_id" => $site->id])->update([
            //                     "buyer_single_id" => $value
            //                 ]);
            //                 $newBuyers[] = $request->buyer_id[$key];
            //             }
            //         }
            //     }
            //     Buyer::whereNotIn("id", $newBuyers)->where("site_id", $site->id)->delete();
            //     foreach ($request->buyer_id as $key => $value) {
            //         if (!isset($request->buyer_id[$key])) {
            //             Buyer::create([
            //                 "site_id" => $site->id,
            //                 "buyer_single_id" => $value,
            //                 "created_by" => auth()->id()
            //             ]);
            //         }
            //     }
            // }
            DB::commit();

            if ($request->ajax()) {
                return $this->withSuccess("Site Updated successfully");
            }
            return $this->withSuccess("Site Updated successfully")->back();
        } catch (\Throwable $th) {
            DB::rollBack();
            return $this->withError($th->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Site $site)
    {
        // $site->delete();
        // if (request()->ajax()) {
        //     return $this->withSuccess("Site Deleted successfully");
        // }
        return $this->withSuccess("Site Deleted successfully")->back();
    }

    public function getList(Request $request)
    {
        $searchableColumns = [
            'id',
            'name',
        ];

        $this->model(model: Site::class, with: ['buyer.buyerSingles:id,name']);


        $editPermission = $this->hasPermission("site-edit");
        $deletePermission = $this->hasPermission("site-delete");

        $this->formateArray(function ($row, $index) use ($editPermission, $deletePermission) {
            $delete = route("master.site.delete", ['site' => $row->id]);
            $action = "";

            if ($editPermission) {
                $action .= "
                            <a class='btn edit-btn  btn-action bg-success text-white me-2'
                                data-id='{$row->id}'
                                data-name='{$row->name}'
                                data-work_category_id='" . json_encode($row->work_category_id) . "'
                                data-buyer='" . json_encode($row->buyer) . "'
                                data-bs-toggle='tooltip' data-bs-placement='top' data-bs-original-title='Edit' href='javascript:void(0);'>
                                <i class='far fa-edit' aria-hidden='true'></i>
                            </a>
                        ";
            }
            if ($deletePermission) {
                // $action .= "
                //             <a class='btn btn-action bg-danger text-white me-2 btn-delete'
                //                 data-id='{$row->id}'
                //                 data-bs-toggle='tooltip'
                //                 data-bs-placement='top' data-bs-original-title='Delete'
                //                 href='{$delete}'>
                //                 <i class='fas fa-trash'></i>
                //             </a>
                //         ";
            }
            $buyerSingleNames = $row->buyer->pluck('buyerSingles.name')->implode(", ");

            $checked = $row->email_enable == 1 ? "checked" : "";
            $switch = "<div class='custom-control custom-switch'>
                        <label class='switch'>
                            <input class='hiddenValue' type='hidden'  value='1'>
                            <input class='switchInput' type='checkbox' data-id='{$row->id}' {$checked}>
                        <label/>
                    </div>";

            return [
                "id" => $row->id,
                "name" => $row->name,
                "action" => $action,
                "switch" => $switch,
                "work_category" => $row->workCategory?->name,
                "buyer" => $buyerSingleNames,
                "created_by" => $row->createdBy?->displayName(),
                "created_at" => $row->created_at ? $row->created_at->format('d/m/Y H:i:s') : '',
                "updated_at" => $row->updated_at ? $row->updated_at->format('d/m/Y H:i:s') : '',
            ];
        });
        return $this->getListAjax($searchableColumns);
    }

    public function changeStatus(Request $request)
    {
        $site = Site::find($request->id);

        $site->email_enable = $request->status;
        $site->save();
        return $this->withSuccess("Status Updated successfully");
    }
}

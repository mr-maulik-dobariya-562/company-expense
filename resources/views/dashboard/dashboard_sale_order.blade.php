@extends('Layouts.app')

@section('title', 'Sale Order Report')

@section('header')
    <div class="row mb-2">
        <div class="col-md-2">
            <label class="form-label">Start Date</label>
            <input class="form-control" id="start_date" type="date" name="start_date" placeholder="Start Date" value="">
        </div>
        <div class="col-md-2">
            <label class="form-label">End Date</label>
            <input class="form-control" id="end_date" type="date" name="end_date" placeholder="End Date" value="">
        </div>
        <div class="col-md-3">
            <label class="form-label">Party</label>
            <select class="form-select select2" id="party_id" multiple style="width: 100%" name="party_id">
                <option value="">Select Type</option>
                @foreach ($partys as $party)
                    <option value="{{ $party->id }}">{{ $party->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label">Item</label>
            <select class="form-select select2" id="item_id" multiple style="width: 100%" name="item_id">
                <option value="">Select Type</option>
                @foreach ($items as $item)
                    <option value="{{ $item->id }}">{{ $item->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label">Work Category</label>
            <select class="form-select select2" id="filter_work_category" multiple style="width: 100%"
                name="filter_work_category">
                <option value="">Select Work Category</option>
                @foreach ($workCategories as $category)
                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="col-md-2">
            <label class="form-label">Site</label>
            <select class="form-select select2" id="filter_site" multiple style="width: 100%" name="filter_site">
                <option value="">Select Site</option>
                @foreach ($site as $value)
                    <option value="{{ $value->id }}">{{ $value->name }}</option>
                @endforeach
            </select>
        </div>
        @if (auth()->user()->hasPermissionTo('sale-order-filter'))
            <div class="col-md-2">
                <label class="form-label">Buyer</label>
                <select class="form-select select2" id="filter_buyer" multiple style="width: 100%" name="filter_buyer">
                    <option value="">Select Buyer</option>
                    @foreach ($buyer as $key => $value)
                        <option value="{{ $value->id }}">{{ $value->name }}</option>
                    @endforeach
                </select>
            </div>

        @endif
        <div class="col-md-2">
            <label class="form-label">Type</label>
            <select class="form-select select2" id="filter_type" multiple style="width: 100%" name="filter_type">
                <option value="">Select Type</option>
                @foreach ($types as $value)
                    <option value="{{ $value->name }}">{{ $value->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label">Color</label>
            <select class="form-select select2" id="color" style="width: 100%" name="color">
                <option value="">Select Type</option>
                <option value="red">Red</option>
                <option value="green">Green</option>
            </select>
        </div>
        <div class="col-md-1 pt-4">
            <button class="btn btn-danger" id="search" type="button">Search</button>
        </div>
    </div>

@endsection
@section('content')
    @if (auth()->user()->hasPermissionTo('export-button'))
        @php
            $button = true;
        @endphp
    @else
        @php
            $button = false;
        @endphp
    @endif
    <div class="row">
        <div class="col-12 col-sm-12">
            <div class="card card-danger card-outline card-outline-tabs">
                <div class="card-header p-0 border-bottom-0">
                    <ul class="nav nav-tabs" id="custom-tabs-four-tab" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" id="pending-tab" data-status="pending" data-toggle="pill"
                                href="#custom-tabs-four-profile" role="tab" aria-controls="custom-tabs-four-profile"
                                aria-selected="true">Pending</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="completed-tab" data-status="completed" data-toggle="pill"
                                href="#custom-tabs-four-messages" role="tab" aria-controls="custom-tabs-four-messages"
                                aria-selected="false">Completed</a>
                        </li>
                    </ul>
                </div>
                <div class="card-body">
                    <div class="tab-content" id="custom-tabs-four-tabContent">
                        <div class="tab-pane fade active show" id="custom-tabs-four-profile" role="tabpanel"
                            aria-labelledby="pending-tab">
                            <div class="row">
                                <div class="col-md-12 table-responsive">
                                    <table class="table table-loader table-vcenter table-hover card-table"
                                        id="pending-table" data-page_length="10" data-button="{{ $button }}">
                                        <thead>
                                            <tr>
                                                <th data-name="action" data-export="disabled">Action</th>
                                                <th data-name="date">Date</th>
                                                <th data-name="name_1">Client Name</th>
                                                <th data-name="vch_No">Purchase Order No</th>
                                                <th data-name="amount">Amount</th>
                                                <th data-name="work_category_id">Work Category</th>
                                                <th data-name="site_id">Site</th>
                                                @if (auth()->user()->hasPermissionTo('sale-order-column'))
                                                    <th data-name="buyer_id">Buyer</th>
                                                @endif
                                                @if (auth()->user()->hasPermissionTo('sale-order-attachment'))
                                                    <th data-name="document">PO Copy</th>
                                                @endif
                                                <th data-name="new_type">Type</th>
                                                <th data-visible='false' data-name="created_by">Created By</th>
                                                <th data-visible='false' data-name="created_at">Created At</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="custom-tabs-four-messages" role="tabpanel"
                            aria-labelledby="pending-tab">
                            <div class="row">
                                <div class="col-md-12 table-responsive">
                                    <table class="table table-loader table-vcenter table-hover card-table"
                                        id="completed-table" data-page_length="10" data-button="{{ $button }}">
                                        <thead>
                                            <tr>
                                                <th data-name="action" data-export="disabled">Action</th>
                                                <th data-name="date">Date</th>
                                                <th data-name="name_1">Client Name</th>
                                                <th data-name="vch_No">Purchase Order No</th>
                                                <th data-name="amount">Amount</th>
                                                <th data-name="work_category_id">Work Category</th>
                                                <th data-name="site_id">Site</th>
                                                @if (auth()->user()->hasPermissionTo('sale-order-column'))
                                                    <th data-name="buyer_id">Buyer</th>
                                                @endif
                                                @if (auth()->user()->hasPermissionTo('sale-order-attachment'))
                                                    <th data-name="document">PO Copy</th>
                                                @endif
                                                <th data-name="new_type">Type</th>
                                                <th data-visible='false' data-name="created_by">Created By</th>
                                                <th data-visible='false' data-name="created_at">Created At</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- /.card -->
                </div>
            </div>
        </div>

        <form id="modal-form" action="" enctype="multipart/form-data" method="POST">
            @csrf
            <div class="modal modal-blur fade" id="country-modal" tabindex="-1" role="dialog" aria-hidden="true">
                <div class="modal-dialog modal-xl" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title"> <span class="title">Update</span> Sale Order</h5>
                            <button class="close" data-dismiss="modal" type="button" aria-label="Close">
                                <span aria-hidden="true">×</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <input id="id" type="hidden" name="id">
                            <input id="vch_No" type="hidden" name="vch_No">
                            <div class="row">
                                <div class="col-xl-4">
                                    <div class="row">
                                        <div class="col-xl-12">
                                            <label class="form-label">Store Phone No</label>
                                            <input type="tel" class="form-control" id="store_phone"
                                                name="store_phone" placeholder="Store Phone" pattern="^[6-9]\d{9}$"
                                                title="Mobile number should be 10 digit and start with 6,7,8 or 9">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xl-4">
                                    <div class="row">
                                        <div class="col-xl-12">
                                            <label class="form-label">Store Email</label>
                                            <input type="email" class="form-control" id="store_email"
                                                name="store_email[]" placeholder="Store Email">
                                        </div>
                                    </div>
                                    <div id="storeRowAddContainer" class="dynamic-container"></div>
                                    <div class="col-md-4 pt-2">
                                        <button class="btn btn-danger" type="button" id="add_store">+ Add</button>
                                    </div>
                                </div>
                                <div class="col-xl-4">
                                    <div class="row">
                                        <div class="col-xl-12">
                                            <label class="form-label">Store CC Email</label>
                                            <input type="email" class="form-control" id="store_cc_email"
                                                name="store_cc_email[]" placeholder="CC Email">
                                        </div>
                                    </div>
                                    <div id="storeRowAddContainerEmail" class="dynamic-container"></div>
                                    <div class="col-md-3 pt-2">
                                        <button class="btn btn-danger" type="button" id="add_store_email">+
                                            Add</button>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-xl-4">
                                    <div class="row">
                                        <div class="col-xl-12">
                                            <label class="form-label">Purchase Phone No</label>
                                            <input type="tel" class="form-control" id="purchase_phone"
                                                name="purchase_phone" placeholder="Purchase Phone" pattern="^[6-9]\d{9}$"
                                                title="Mobile number should be 10 digit and start with 6,7,8 or 9">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xl-4">
                                    <div class="row">
                                        <div class="col-xl-12">
                                            <label class="form-label">Purchase Email</label>
                                            <input type="email" class="form-control" id="purchase_email"
                                                name="purchase_email[]" placeholder="Purchase Email">
                                        </div>
                                    </div>
                                    <div id="purchaseRowAddContainer" class="dynamic-container"></div>

                                    <div class="col-md-3 pt-2">
                                        <button class="btn btn-danger" type="button" id="add_purchase">+ Add</button>
                                    </div>
                                </div>
                                <div class="col-xl-4">
                                    <div class="row">
                                        <div class="col-xl-12">
                                            <label class="form-label">Purchase CC Email</label>
                                            <input type="email" class="form-control" id="purchase_cc_email"
                                                name="purchase_cc_email[]" placeholder="CC Email">
                                        </div>
                                    </div>
                                    <div id="purchaseRowAddContainerEmail" class="dynamic-container"></div>

                                    <div class="col-md-3 pt-2">
                                        <button class="btn btn-danger" type="button" id="add_purchase_email">+
                                            Add</button>
                                    </div>
                                </div>
                            </div>
                            <div class="row mb-3 align-items-end">
                                <div class="col-md-4">
                                    <label class="form-label">Work Category</label>
                                    <select class="form-select need-required-permission" id="work_category"
                                        style="width: 100%;" name="work_category">
                                        <option value="">Select Work Category</option>
                                        @foreach ($workCategories as $value)
                                            <option value="{{ $value->id }}">{{ $value->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Site</label>
                                    <select class="form-select need-required-permission" id="site"
                                        style="width: 100%;" name="site">
                                        <option value="">Select Site</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Buyer</label>
                                    <select class="form-select need-required-permission" id="buyer"
                                        style="width: 100%;" name="buyer">
                                        <option value="">Select Buyer</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Status</label>
                                    <select class="form-select" id="status" style="width: 100%;" name="status">
                                        @foreach (['PENDING', 'COMPLETED'] as $value)
                                            <option value="{{ $value }}">{{ $value }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">PO Copy</label>
                                    <input type="file" class="form-control need-required-permission" id="document"
                                        name="document" accept="*">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Type</label>
                                    <select class="form-select need-required-permission" id="new_type"
                                        style="width: 100%;" name="new_type">
                                        <option value="">Select Type</option>
                                        @foreach ($types as $value)
                                            <option value="{{ $value->name }}">{{ $value->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="modal-footer d-flex justify-content-between">
                                <button class="btn me-auto" data-dismiss="modal" type="button">Close</button>
                                <div>
                                    <button class="btn btn-warning mr-1" id="reset-form" type="button">Reset</button>
                                    <button class="btn btn-danger" type="submit">
                                        Save <i class="fas fa-1x fa-sync-alt fa-spin save-loader"
                                            style="display:none"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>

        <div class="modal modal-blur fade" id="item-show-modal" aria-modal="true" role="dialog">
            <div class="modal-dialog modal-xl" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="fas fa-globe"></i> {{ env('APP_NAME') }}</h5>
                        <button class="close" data-dismiss="modal" type="button" aria-label="Close">
                            <span aria-hidden="true">×</span>
                        </button>
                    </div>
                    <div class="modal-body"></div>
                    <div class="modal-footer justify-content-between">
                        <button class="btn btn-default" data-dismiss="modal" type="button">Close</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade show" id="order-invoice-modal" aria-modal="true" role="dialog">
            <div class="modal-dialog modal-xl" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="fas fa-globe"></i> {{ env('APP_NAME') }}</h5>
                        <button class="close" data-dismiss="modal" type="button" aria-label="Close">
                            <span aria-hidden="true">×</span>
                        </button>
                    </div>
                    <div class="modal-body"></div>
                    <div class="modal-footer justify-content-between">
                        <button class="btn btn-default" data-dismiss="modal" type="button">Close</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal modal-blur fade" id="sale-return-show-modal" aria-modal="true" role="dialog">
            <div class="modal-dialog modal-xl" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="fas fa-globe"></i> {{ env('APP_NAME') }}</h5>
                        <button class="close" data-dismiss="modal" type="button" aria-label="Close">
                            <span aria-hidden="true">×</span>
                        </button>
                    </div>
                    <div class="modal-body"></div>
                    <div class="modal-footer justify-content-between">
                        <button class="btn btn-default" data-dismiss="modal" type="button">Close</button>
                    </div>
                </div>
            </div>
        </div>
    @endsection
    @push('javascript')
        <script>
            $(document).ready(function() {
                var pending = null;
                var permission = "{{ auth()->user()->hasPermissionTo('compulsory-field') ? 'true' : 'false' }}";
                var completed = null;
                var modal = $("#country-modal");
                $(document).on("shown.bs.tab", "#pending-tab, #completed-tab", function(e) {
                    var status = $(this).data("status");
                    var startDate = $("#start_date").val();
                    var endDate = $("#end_date").val();
                    var partyId = $("#party_id").val();
                    var itemId = $("#item_id").val();
                    var workCategoryId = $("#filter_work_category").val();
                    var siteId = $("#filter_site").val();
                    var buyerId = $("#filter_buyer").val();
                    var newType = $("#filter_type").val();


                    if (status == "pending") {
                        if (pending) {
                            return pending.ajax.reload();
                        }
                        pending = window.table(
                            "#pending-table",
                            "{{ route('getSaleOrderReport') }}", {
                                additionalData: () => {
                                    return {
                                        _token: "{{ csrf_token() }}",
                                        status: status,
                                        from_date: $("#start_date").val(),
                                        to_date: $("#end_date").val(),
                                        party_id: $("#party_id").val(),
                                        item_id: $("#item_id").val(),
                                        work_category_id: $("#filter_work_category").val(),
                                        site_id: $("#filter_site").val(),
                                        buyer_id: $("#filter_buyer").val(),
                                        new_type: $("#filter_type").val(),
                                        color: $('#color').val()
                                    }
                                },
                                createdRow: function(row, data, dataIndex) {
                                    // Apply inline styles received from the backend
                                    if (data.row_style) {
                                        $(row).attr('style', data.row_style);
                                    }
                                }
                            }
                        );
                    } else if (status == "completed") {
                        if (completed) {
                            return completed.ajax.reload();
                        }
                        completed = window.table(
                            "#completed-table",
                            "{{ route('getSaleOrderReport') }}", {
                                additionalData: () => {
                                    return {
                                        _token: "{{ csrf_token() }}",
                                        status: status,
                                        from_date: $("#start_date").val(),
                                        to_date: $("#end_date").val(),
                                        party_id: $("#party_id").val(),
                                        item_id: $("#item_id").val(),
                                        work_category_id: $("#filter_work_category").val(),
                                        site_id: $("#filter_site").val(),
                                        buyer_id: $("#filter_buyer").val(),
                                        new_type: $("#filter_type").val(),
                                        color: $('#color').val()
                                    }
                                },
                                createdRow: function(row, data, dataIndex) {
                                    // Apply inline styles received from the backend
                                    if (data.row_style) {
                                        $(row).attr('style', data.row_style);
                                    }
                                },
                                onInit: function() {
                                    table.load();
                                }
                            }
                        );
                    }
                })
                $('#pending-tab').trigger('shown.bs.tab');
                $("#search").click(function() {
                    $('.nav-link.active').trigger('shown.bs.tab');
                });

                window.edit = false;
                $(document).on("click", ".edit-btn", async function() {
                    window.edit = true;
                    var id = $(this).data("id");
                    var buyer = $(this).data("buyer");
                    var work_category = $(this).data("work_category");
                    var site = $(this).data("site");
                    var vch_No = $(this).data("vch-no");
                    var status = $(this).data("status");
                    var new_type = $(this).data("new_type");
                    var poCopyFile = $(this).data("document");

                    $('.need-required-permission').attr('required', (permission == "true"));
                    $('#document').prop('required', (permission == "true" && !poCopyFile));

                    var storePhone = $(this).data("store_phone");
                    var storeEmail = $(this).data("store_email") ? $(this).data("store_email").split(",") :
                        [];
                    var storeCcEmail = $(this).data("store_cc_email") ? $(this).data("store_cc_email")
                        .split(
                            ",") : [];
                    var purchasePhone = $(this).data("purchase_phone");
                    var purchaseEmail = $(this).data("purchase_email") ? $(this).data("purchase_email")
                        .split(
                            ",") : [];
                    var purchaseCcEmail = $(this).data("purchase_cc_email") ? $(this).data(
                            "purchase_cc_email")
                        .split(",") : [];

                    $('#buyer, #work_category, #site, #status, #new_type').select2();

                    $("#id").val(id);
                    $('#vch_No').val(vch_No);
                    $("#work_category").val(work_category).trigger('change', {
                        site_id: site,
                        buyer_id: buyer
                    });
                    $("#status").val(status).trigger('change');
                    $("#new_type").val(new_type).trigger('change');

                    // Clear existing dynamic rows
                    $("#storeRowAddContainer").html("");
                    $("#storeRowAddContainerEmail").html("");
                    $("#purchaseRowAddContainer").html("");
                    $("#purchaseRowAddContainerEmail").html("");

                    // Fill in the default fields (first row)
                    if (storeEmail.length > 0) {
                        $("#store_email").val(storeEmail[0] || "");
                        $("#store_cc_email").val(storeCcEmail[0] || "");
                    } else {
                        $("#store_email").val("");
                        $("#store_cc_email").val("");
                    }

                    if (purchaseEmail.length > 0) {
                        $("#purchase_email").val(purchaseEmail[0] || "");
                        $("#purchase_cc_email").val(purchaseCcEmail[0] || "");
                    } else {
                        $("#purchase_email").val("");
                        $("#purchase_cc_email").val("");
                    }

                    // Add additional rows for Store Emails (if any)
                    for (let i = 1; i < storeEmail.length; i++) {
                        var newRow = $("<div>").addClass("row mt-2 align-items-end");
                        var cols = `
						<div class="col-xl-10">
							<label class="form-label">Store Email</label>
							<input type="email" class="form-control" name="store_email[]" placeholder="Store Email" value="${storeEmail[i]}">
						</div>
						<div class="col-xl-2 text-end">
							<button type="button" class="btn btn-danger btn-sm delete-row">Delete</button>
						</div>
						`;
                        newRow.html(cols);
                        $("#storeRowAddContainer").append(newRow);
                    }

                    for (let i = 1; i < storeCcEmail.length; i++) {
                        var newRow = $("<div>").addClass("row mt-2 align-items-end");
                        var cols = `
						<div class="col-xl-10">
							<label class="form-label">Store CC Email</label>
							<input type="email" class="form-control" name="store_cc_email[]" placeholder="CC Email" value="${storeCcEmail[i]}">
						</div>
						<div class="col-xl-2 text-end">
							<button type="button" class="btn btn-danger btn-sm delete-row">Delete</button>
						</div>
						`;
                        newRow.html(cols);
                        $("#storeRowAddContainerEmail").append(newRow);
                    }

                    // Add additional rows for Purchase Emails (if any)
                    for (let i = 1; i < purchaseEmail.length; i++) {
                        var newRow = $("<div>").addClass("row mt-2 align-items-end");
                        var cols = `
						<div class="col-xl-10">
							<label class="form-label">Purchase Email</label>
							<input type="email" class="form-control" name="purchase_email[]" placeholder="Purchase Email" value="${purchaseEmail[i]}">
						</div>
						<div class="col-xl-2 text-end">
							<button type="button" class="btn btn-danger btn-sm delete-row">Delete</button>
						</div>
						`;
                        newRow.html(cols);
                        $("#purchaseRowAddContainer").append(newRow);
                    }

                    for (let i = 1; i < purchaseCcEmail.length; i++) {
                        var newRow = $("<div>").addClass("row mt-2 align-items-end");
                        var cols = `
						<div class="col-xl-10">
							<label class="form-label">Purchase CC Email</label>
							<input type="email" class="form-control" name="purchase_cc_email[]" placeholder="CC Email" value="${purchaseCcEmail[i]}">
						</div>
						<div class="col-xl-2 text-end">
							<button type="button" class="btn btn-danger btn-sm delete-row">Delete</button>
						</div>
						`;
                        newRow.html(cols);
                        $("#purchaseRowAddContainerEmail").append(newRow);
                    }

                    // Fill other fields
                    $("#store_phone").val(storePhone);
                    $("#purchase_phone").val(purchasePhone);

                    modal.modal("show");

                });

                $("#modal-form").submit(function(e) {
                    e.preventDefault();
                    const F = $(this);
                    removeErrors();
                    F.find(".save-loader").show();

                    var formData = new FormData(this);

                    $.ajax({
                        url: '{{ route('saleOrderDataUpdate') }}',
                        type: 'POST',
                        data: formData,
                        processData: false,
                        contentType: false,
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function(res) {
                            if (res.success) {
                                if (pending !== null) pending.ajax.reload();
                                if (completed !== null) completed.ajax.reload();
                                modal.modal("hide");
                                sweetAlert("success", res.message);
                            } else {
                                sweetAlert("error", res.message);
                            }
                        },
                        error: function(xhr, status, error) {
                            sweetAlert("error", "An error occurred while processing the request.");
                        },
                        complete: function() {
                            F.find(".save-loader").hide();
                        }
                    });
                });
            });
        </script>

        <!-- Item Show Model Script -->
        <script>
            $(document).ready(function() {
                $(document).on("click", ".itemModel", function() {
                    $(".page-loader").show();
                    var id = $(this).data("id");
                    $.ajax({
                        url: "{{ route('saleOrderModelData') }}",
                        type: 'POST',
                        data: {
                            id: id
                        },
                        success: function(res) {
                            if (res) {
                                $("#item-show-modal .modal-body").html(res);
                                $("#item-show-modal").modal("show");
                            } else {
                                sweetAlert("error", res.message);
                            }
                        },
                        error: function(xhr, status, error) {
                            sweetAlert("error", "An error occurred while processing the request.");
                        }
                    }).always(function() {
                        $(".page-loader").hide();
                    });

                });

                $(document).on("change", "#work_category", function(e, data) {
                    const {
                        site_id,
                        buyer_id
                    } = data || {};

                    var id = $(this).val();
                    $("#buyer").empty().append('<option value="">Select Buyer</option>');

                    if (id == "") {
                        $("#site").empty().append('<option value="">Select Site</option>');
                        return;
                    }

                    $.ajax({
                        url: "{{ route('getSite') }}",
                        type: 'POST',
                        data: {
                            work_category_id: id
                        },
                        success: function(res) {
                            $("#site").empty();
                            $("#site").append('<option value="">Select Site</option>');

                            if (res.success && res.data.length > 0) {


                                $.each(res.data, function(key, value) {
                                    var selected = site_id == value.id ? 'selected' : '';
                                    $("#site").append(
                                        `<option value="${value.id}" ${selected}>${value.name}</option>`
                                    );
                                });

                                if (buyer_id) {
                                    $("#site").trigger('change', {
                                        selected_id: buyer_id
                                    });
                                }

                            } else {
                                $("#buyer").empty().append(
                                    '<option value="">Select Buyer</option>');
                            }
                        }
                    });
                });

                $(document).on("change", "#site", function(e, trigger) {
                    const buyer_selected_id = trigger?.selected_id ?? null


                    var id = $(this).val();
                    if (id == "") {
                        $("#buyer").empty().append('<option value="">Select Buyer</option>');
                        return;
                    }
                    $.ajax({
                        url: "{{ route('getBuyer') }}",
                        type: 'POST',
                        data: {
                            site_id: id
                        },
                        success: function(res) {
                            if (res.success) {
                                $("#buyer").empty().append(
                                    '<option value="">Select Buyer</option>');
                                $.each(res.data, function(key, value) {
                                    var selected = buyer_selected_id == value.id ?
                                        'selected' : '';
                                    $("#buyer").append('<option value="' + value.id + '" ' +
                                        selected + '>' +
                                        value.name + '</option>');
                                });
                            }
                        }
                    });
                });

            });
        </script>

        <!-- Reset Button Script -->
        <script>
            $(document).on("click", "#reset-form", function() {
                var form = $("#modal-form")[0];
                form.reset();

                $("#buyer, #work_category, #site").val('').trigger('change');
                $('#status').val('PENDING').trigger('change');
                $("input[type=checkbox], input[type=radio]").prop("checked", false);
            });
        </script>
        <script>
            $(document).ready(function() {
                $(document).on("click", ".invoiceModel", function() {
                    var id = $(this).data("id");
                    $.ajax({
                        url: "{{ route('saleModelData') }}",
                        type: 'POST',
                        data: {
                            id: id,
                            action: 'invoice'
                        },
                        success: function(res) {
                            if (res) {
                                $("#order-invoice-modal .modal-body").html(res);
                                $("#order-invoice-modal").modal("show");
                            } else {
                                sweetAlert("error", res.message);
                            }
                        },
                        error: function(xhr, status, error) {
                            sweetAlert("error", "An error occurred while processing the request.");
                        }
                    });
                });
            });
        </script>

        <!-- Filter Work Category,Site,Buyer Script -->
        <script>
            $(document).on("change", "#filter_work_category", function() {
                var id = $(this).val();
                $("#filter_buyer").empty().append('<option value="">Select Buyer</option>');

                if (id == "") {
                    $("#filter_site").empty().append('<option value="">Select Site</option>');
                    return;
                }

                $.ajax({
                    url: "{{ route('getSite') }}",
                    type: 'POST',
                    data: {
                        work_category_id: id
                    },
                    success: function(res) {
                        $("#filter_site").empty();

                        if (res.success && res.data.length > 0) {
                            if (res.data.length === 1) {
                                $("#filter_site").append('<option value="' + res.data[0].id + '">' +
                                        res.data[0].name + '</option>')
                                    .val(res.data[0].id).trigger('change');
                            } else {
                                $("#filter_site").append('<option value="">Select Site</option>');
                                $.each(res.data, function(key, value) {
                                    $("#filter_site").append('<option value="' + value.id +
                                        '">' + value.name + '</option>');
                                });
                            }
                        } else {
                            $("#filter_buyer").empty().append(
                                '<option value="">Select Buyer</option>');
                        }
                    }
                });
            });

            $(document).on("change", "#filter_site", function() {
                var id = $(this).val();
                if (id == "") {
                    $("#filter_buyer").empty().append('<option value="">Select Buyer</option>');
                    return;
                }
                $.ajax({
                    url: "{{ route('getBuyer') }}",
                    type: 'POST',
                    data: {
                        site_id: id
                    },
                    success: function(res) {
                        if (res.success) {
                            $("#filter_buyer").empty().append(
                                '<option value="">Select Buyer</option>');
                            $.each(res.data, function(key, value) {
                                $("#filter_buyer").append('<option value="' + value
                                    .buyer_single_id + '">' +
                                    value.name + '</option>');
                            });
                        }
                    }
                });
            });
        </script>

        <!-- Add new row script -->
        <script>
            $(document).ready(function() {
                $("#add_store").click(function() {
                    var newRow = $("<div>").addClass("row mt-2 align-items-end");
                    var cols = "";
                    cols +=
                        '<div class="col-xl-10"><label class="form-label">Store Email</label><input type="email" class="form-control" name="store_email[]" placeholder="Store Email"></div>';
                    cols +=
                        '<div class="col-xl-2 text-end"><button type="button" class="btn btn-danger btn-sm delete-row">Delete</button></div>';
                    newRow.html(cols);
                    $("#storeRowAddContainer").append(newRow);
                });
                $("#add_store_email").click(function() {
                    var newRow = $("<div>").addClass("row mt-2 align-items-end");
                    var cols = "";
                    cols +=
                        '<div class="col-xl-10"><label class="form-label">Store CC Email</label><input type="email" class="form-control" name="store_cc_email[]" placeholder="CC Email"></div>';
                    cols +=
                        '<div class="col-xl-2 text-end"><button type="button" class="btn btn-danger btn-sm delete-row">Delete</button></div>';
                    newRow.html(cols);
                    $("#storeRowAddContainerEmail").append(newRow);
                });

                $(document).on("click", ".delete-row", function() {
                    $(this).closest(".row").remove();
                });

                $("#add_purchase").click(function() {
                    var newRow = $("<div>").addClass("row mt-2 align-items-end");
                    var cols = "";
                    cols +=
                        '<div class="col-xl-10"><label class="form-label">Purchase Email</label><input type="email" class="form-control" name="purchase_email[]" placeholder="Purchase Email"></div>';
                    cols +=
                        '<div class="col-xl-2 text-end"><button type="button" class="btn btn-danger btn-sm delete-row">Delete</button></div>';
                    newRow.html(cols);
                    $("#purchaseRowAddContainer").append(newRow);
                });
                $("#add_purchase_email").click(function() {
                    var newRow = $("<div>").addClass("row mt-2 align-items-end");
                    var cols = "";
                    cols +=
                        '<div class="col-xl-10"><label class="form-label">Purchase CC Email</label><input type="email" class="form-control" name="purchase_cc_email[]" placeholder="CC Email"></div>';
                    cols +=
                        '<div class="col-xl-2 text-end"><button type="button" class="btn btn-danger btn-sm delete-row">Delete</button></div>';
                    newRow.html(cols);
                    $("#purchaseRowAddContainerEmail").append(newRow);
                });
            });
        </script>

        <script>
            $(document).ready(function() {
                $(document).on("click", ".saleReturnModel", function() {
                    var id = $(this).data("id");
                    $.ajax({
                        url: "{{ route('report.saleReturnModelData') }}",
                        type: 'POST',
                        data: {
                            id: id,
                            action: 'invoice'
                        },
                        success: function(res) {
                            if (res) {
                                $("#sale-return-show-modal .modal-body").html(res);
                                $("#sale-return-show-modal").modal("show");
                            } else {
                                sweetAlert("error", res.message);
                            }
                        },
                        error: function(xhr, status, error) {
                            sweetAlert("error", "An error occurred while processing the request.");
                        }
                    });
                });
            });
        </script>

        {{-- Delete Button Script --}}
        <script>
            $(document).ready(function() {
                $(document).on("click", ".delete-btn", function() {
                    var id = $(this).data("id");

                    // Confirmation alert before proceeding
                    if (confirm("Are you sure you want to delete this item?")) {
                        $.ajax({
                            url: "{{ route('saleOrderDelete') }}",
                            type: 'POST',
                            data: {
                                id: id
                            },
                            success: function(res) {
                                if (res.success) {
                                    sweetAlert("success", res.message);
                                    $('#pending-tab').trigger('shown.bs.tab');
                                } else {
                                    sweetAlert("error", res.message);
                                }
                            },
                            error: function(xhr, status, error) {
                                sweetAlert("error",
                                    "An error occurred while processing the request.");
                            }
                        });
                    }
                });

            });
        </script>
    @endpush

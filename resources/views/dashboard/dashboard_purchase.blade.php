@extends('Layouts.app')

@section('title', 'Purchase Invoice Report')

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
            <select class="form-select select2" id="party_id" style="width: 100%" multiple name="party_id">
                <option value="">Select Type</option>
                @foreach ($partys as $party)
                    <option value="{{ $party->id }}">{{ $party->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label">Item</label>
            <select class="form-select select2" id="item_id" style="width: 100%" multiple name="item_id">
                <option value="">Select Type</option>
                @foreach ($items as $item)
                    <option value="{{ $item->id }}">{{ $item->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label">Type</label>
            <select class="form-select select2" id="filter_type" style="width: 100%" multiple name="filter_type">
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
                <option value="orange">Orange</option>
                <option value="white">White</option>
                <option value="default">Default</option>
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
                            <a class="nav-link active" id="pending-tab" data-status="info" data-toggle="pill"
                                href="#custom-tabs-four-profile" role="tab" aria-controls="custom-tabs-four-profile"
                                aria-selected="true">Info</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="upcoming-payment-tab" data-status="payment" data-toggle="pill"
                                href="#custom-tabs-four-upcoming-payment" role="tab"
                                aria-controls="custom-tabs-four-upcoming-payment" aria-selected="false">Payment</a>
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
                                                <th data-name="id" data-export="disabled">Status </th>
                                                <th data-name="action" data-export="disabled">Action</th>
                                                <th data-name="vch_No">Invoice No</th>
                                                <th data-name="date">Date</th>
                                                <th data-name="name_1">Vendor Name</th>
                                                <th data-name="amount">Amount</th>

                                                <th data-name="mtc_no">MTC No</th>
                                                <th data-name="due_date">Due Date</th>
                                                <th data-name="mtc_document">MTC Document</th>
                                                <th data-name="new_type">Type</th>

                                                <th data-visible="false" data-name="created_by">Created By</th>
                                                <th data-visible="false" data-name="created_at">Created At</th>
                                                <th data-visible="false" data-name="updated_at">Updated At</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="custom-tabs-four-upcoming-payment" role="tabpanel"
                            aria-labelledby="upcoming-payment-tab">
                            <div class="row">
                                <div class="col-md-12 table-responsive">
                                    <table class="table table-loader table-vcenter table-hover card-table"
                                        id="upcoming-payment-table" data-page_length="10"
                                        data-button="{{ $button }}">
                                        <thead>
                                            <tr>
                                                <th data-name="id" data-export="disabled">Status</th>
                                                <th data-name="action" data-export="disabled">Action</th>
                                                <th data-name="vch_No">Invoice No</th>
                                                <th data-name="date">Date</th>
                                                <th data-name="name_1">Vendor Name</th>
                                                <th data-name="amount">Amount</th>
                                                <th data-name="pendingAmount">Due Amount</th>

                                                <th data-name="mtc_no">MTC No</th>
                                                <th data-name="due_date">Due Date</th>
                                                <th data-name="mtc_document">MTC Document</th>
                                                <th data-name="new_type">Type</th>

                                                <th data-visible="false" data-name="created_by">Created By</th>
                                                <th data-visible="false" data-name="created_at">Created At</th>
                                                <th data-visible="false" data-name="updated_at">Updated At</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="custom-tabs-four-messages" role="tabpanel"
                            aria-labelledby="completed-tab">
                            <div class="row">
                                <div class="col-md-12 table-responsive">
                                    <table class="table table-loader table-vcenter table-hover card-table"
                                        id="completed-table" data-page_length="10" data-button="{{ $button }}">
                                        <thead>
                                            <tr>
                                                {{-- <th data-name="id">Status</th> --}}
                                                <th data-name="action">Action</th>
                                                <th data-name="vch_No">Invoice No</th>
                                                <th data-name="date">Date</th>
                                                <th data-name="name_1">Vendor Name</th>
                                                <th data-name="amount">Amount</th>

                                                <th data-name="mtc_no">MTC No</th>
                                                <th data-name="due_date">Due Date</th>
                                                <th data-name="mtc_document">MTC Document</th>
                                                <th data-name="new_type">Type</th>

                                                <th data-visible="false" data-name="created_by">Created By</th>
                                                <th data-visible="false" data-name="created_at">Created At</th>
                                                <th data-visible="false" data-name="updated_at">Updated At</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- /.card -->
            </div>
        </div>
    </div>

    <form id="modal-form" action="{{ route('purchaseDataUpdate') }}" enctype="multipart/form-data" method="POST">
        @csrf
        <div class="modal modal-blur fade" id="country-modal" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title"> <span class="title">Update</span> Purchase</h5>
                        <button class="close" data-dismiss="modal" type="button" aria-label="Close">
                            <span aria-hidden="true">×</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <input id="id" type="hidden" name="id">
                        <input id="vch_no" type="hidden" name="vch_No">
                        <div class="row mt-3">
                            <div class="col-md-12">
                                <label class="form-label">MTC No</label>
                                <input class="form-control need-required-permission" id="mtc_no" type="text"
                                    name="mtc_no" placeholder="Enter MTC No">
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">MTC Document</label>
                                <input class="form-control" id="mtc_document" type="file" name="mtc_document"
                                    accept="*" placeholder="MTC Document">
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">Credit Days</label>
                                <input class="form-control need-required-permission" id="credited_days" type="text"
                                    name="credited_days" placeholder="Credit Days">
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">Type</label>
                                <select class="form-select form-control select2Type need-required-permission"
                                    id="new_type" style="width: 100%;" name="new_type">
                                    <option value="">Select Type</option>
                                    @foreach ($types as $value)
                                        <option value="{{ $value->name }}">{{ $value->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer justify-content-between">
                        <button class="btn me-auto" data-dismiss="modal" type="button">Close</button>
                        <div>
                            <button class="btn btn-warning mr-1 reset-form" type="button">Reset</button>
                            <button class="btn btn-danger" type="submit">
                                Save <i class="fas fa-1x fa-sync-alt fa-spin save-loader" style="display:none"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>

    <form id="payment-form" action="{{ route('purchasePaymentDataUpdate') }}" enctype="multipart/form-data"
        method="POST">
        @csrf
        <div class="modal modal-blur fade" id="payment-modal" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document-modal">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title"> <span class="title">Update</span> Payment Sale</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">×</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="id" id="id-payment">
                        <input type="hidden" name="vch_No" id="vch_no_payment">
                        <div class="row mb-3 align-items-end">
                            <div class="col-md-6">
                                <label class="form-label">TDS Category</label>
                                <select class="form-select form-control select2" id="tds_category" style="width: 100%;"
                                    name="tds_category">
                                    <option value="">Select TDS Category</option>
                                    @foreach ($tdsCategories as $tds_category)
                                        <option value="{{ $tds_category->id }}">{{ $tds_category->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check">
                                    <label class="form-label">TDS Amount</label>
                                    <input type="number" class="form-control" id="tds_amount" name="tds_amount"
                                        placeholder="TDS Amount" step="any" min="0"
                                        title="Only numeric characters are allowed">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Amount</label>
                                <input type="number" class="form-control" id="amount" name="amount[]"
                                    placeholder="Amount" step="any" min="0"
                                    title="Only numeric characters are allowed">
                            </div>
                            <div class="col-md-6 p-2">
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" id="payment_checkbox"
                                        name="payment_checkbox" value="1">
                                    <label class="form-check-label" for="payment_checkbox">Check me out</label>
                                </div>
                            </div>
                        </div>

                        <!-- Container for dynamically added Amount rows -->
                        <div id="amountRowAddContainer" class="dynamic-container"></div>

                        <div class="row mt-3">
                            <div class="col-md-3 pt-2 pb-1">
                                <button class="btn btn-danger" type="button" id="add_amount">+ Add</button>
                            </div>
                        </div>
                        <div class="modal-footer justify-content-between">
                            <button class="btn me-auto" data-dismiss="modal" type="button">Close</button>
                            <div>
                                <button type="button" class="btn btn-warning mr-1 reset-form">Reset</button>
                                <button class="btn btn-danger" type="submit">
                                    Save <i class="fas fa-1x fa-sync-alt fa-spin save-loader" style="display:none"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>

    <div class="modal fade show" id="item-show-modal" aria-modal="true" role="dialog">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-globe"></i> {{ env('APP_NAME') }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id" id="id">
                    <div class="row mb-3 align-items-end">
                        <div class="col-md-4">
                            <label class="form-label">Store Phone No</label>
                            <input type="tel" class="form-control" id="store_phone" name="store_phone"
                                placeholder="Store Phone" pattern="^[6-9]\d{9}$"
                                title="Mobile number should be 10 digit and start with 6,7,8 or 9">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Store Email</label>
                            <input type="email" class="form-control" id="store_email" name="store_email[]"
                                placeholder="Store Email">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Store CC Email</label>
                            <input type="email" class="form-control" id="store_cc_email" name="store_cc_email[]"
                                placeholder="CC Email">
                        </div>
                    </div>

                    <!-- Container for dynamically added rows -->
                    <div id="storeRowAddContainer" class="dynamic-container"></div>

                    <div class="row mt-3">
                        <div class="col-md-4"></div>
                        <div class="col-md-3 pt-2">
                            <button class="btn btn-danger" type="button" id="add_store">+ Add</button>
                        </div>
                    </div>

                    <div class="row mt-3">
                        <div class="col-md-4">
                            <label class="form-label">Purchase Phone No</label>
                            <input type="text" class="form-control" id="purchase_phone" name="purchase_phone"
                                placeholder="Purchase Phone" pattern="^[6-9]\d{9}$"
                                title="Mobile number should be 10 digit and start with 6,7,8 or 9">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Purchase Email</label>
                            <input type="email" class="form-control" id="purchase_email" name="purchase_email[]"
                                placeholder="Purchase Email">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Purchase CC Email</label>
                            <input type="email" class="form-control" id="purchase_cc_email" name="purchase_cc_email[]"
                                placeholder="CC Email">
                        </div>
                    </div>

                    <!-- Container for dynamically added rows -->
                    <div id="purchaseRowAddContainer" class="dynamic-container"></div>

                    <div class="row mt-3">
                        <div class="col-md-4"></div>
                        <div class="col-md-3 pt-2">
                            <button class="btn btn-danger" type="button" id="add_purchase">+ Add</button>
                        </div>
                    </div>

                    <div class="row mt-3">
                        <div class="col-md-4">
                            <label class="form-label">Lead Delivery Date</label>
                            <input type="date" class="form-control" id="lead_delivery_date" name="lead_delivery_date"
                                placeholder="Lead Delivery Date">
                        </div>
                    </div>
                </div>
                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    <button class="btn btn-danger" type="submit">
                        Save <i class="fas fa-1x fa-sync-alt fa-spin save-loader" style="display:none"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade show" id="purchase-return-show-modal" aria-modal="true" role="dialog">
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
            var completed = null;
            var upcoming_payment = null;
            var modal = $("#country-modal");
            var paymentModal = $("#payment-modal");
            var permission = "{{ auth()->user()->hasPermissionTo('compulsory-field') ? 'true' : 'false' }}";
            $(document).on("shown.bs.tab", "#pending-tab, #completed-tab, #upcoming-payment-tab",
                function(e) {
                    var status = $(this).data("status");
                    var startDate = $("#start_date").val();
                    var endDate = $("#end_date").val();
                    var partyId = $("#party_id").val();
                    var itemId = $("#item_id").val();
                    var color = $("#color").val();
                    var newType = $("#filter_type").val();

                    if (status == "info") {
                        if (pending) {
                            return pending.ajax.reload();
                        }
                        pending = window.table(
                            "#pending-table",
                            "{{ route('getPurchaseReport') }}", {
                                additionalData: () => {
                                    return {
                                        _token: "{{ csrf_token() }}",
                                        status: status,
                                        from_date: $("#start_date").val(),
                                        to_date: $("#end_date").val(),
                                        party_id: $("#party_id").val(),
                                        item_id: $("#item_id").val(),
                                        color: $("#color").val(),
                                        new_type: $("#filter_type").val()
                                    }
                                },
                                createdRow: function(row, data, dataIndex) {
                                    // Apply inline styles received from the backend
                                    if (data.row_style) {
                                        $(row).find('td').not(':first').attr('style', data.row_style);
                                    }
                                }
                            },
                        );
                    } else if (status == "payment") {
                        if (upcoming_payment) {
                            return upcoming_payment.ajax.reload();
                        }
                        upcoming_payment = window.table(
                            "#upcoming-payment-table",
                            "{{ route('getPurchaseReport') }}", {
                                additionalData: () => {
                                    return {
                                        _token: "{{ csrf_token() }}",
                                        status: status,
                                        from_date: $("#start_date").val(),
                                        to_date: $("#end_date").val(),
                                        party_id: $("#party_id").val(),
                                        item_id: $("#item_id").val(),
                                        color: $("#color").val(),
                                        new_type: $("#filter_type").val()
                                    }
                                },
                                createdRow: function(row, data, dataIndex) {
                                    // Apply inline styles received from the backend
                                    if (data.row_style) {
                                        $(row).find('td').not(':first').attr('style', data.row_style);
                                    }
                                }
                            }
                        )
                    } else if (status == "completed") {
                        if (completed) {
                            return completed.ajax.reload();
                        }
                        completed = window.table(
                            "#completed-table",
                            "{{ route('getPurchaseReport') }}", {
                                additionalData: () => {
                                    return {
                                        _token: "{{ csrf_token() }}",
                                        status: status,
                                        from_date: $("#start_date").val(),
                                        to_date: $("#end_date").val(),
                                        party_id: $("#party_id").val(),
                                        item_id: $("#item_id").val(),
                                        color: $("#color").val(),
                                        new_type: $("#filter_type").val()
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

            $(document).on("click", ".edit-btn", function() {
                var id = $(this).data("id");
                var vch_No = $(this).data("vch-no");
                var mtc_no = $(this).data("mtc_no");
                var credited_days = $(this).data("credited_days");
                var new_type = $(this).data("new_type");

                $('.need-required-permission').attr('required', (permission == "true"));

                // Fill other fields
                $("#id").val(id);
                $("#vch_no").val(vch_No);
                $("#mtc_no").val(mtc_no);
                $("#credited_days").val(credited_days);
                $("#new_type").val(new_type).trigger('change');

                // Show the modal
                var modal = $("#country-modal");
                modal.modal("show");
            });

            $(document).on("click", ".payment-edit-btn", function() {
                var id = $(this).data("id");
                var vch_No = $(this).data("vch-no");
                var amountData = $(this).data("amount");
                var tdsCategoryId = $(this).data("tds_category_id");
                var tdsAmount = $(this).data("tds_amount");
                var paymentCheckbox = $(this).data("payment_checkbox");
                $("#amountRowAddContainer").html("");

                if (amountData) {
                    // var amounts = amountData.split(","); // Convert to array
                    var amounts = String(amountData).split(",");

                    // Set the first value in the default input
                    $("#amount").val(amounts[0] || "");

                    // Add additional rows for the remaining amounts
                    for (let i = 1; i < amounts.length; i++) {
                        var newRow = $("<div>").addClass("row mt-2 align-items-end");
                        var cols = `
                        <div class="col-md-6">
                            <label class="form-label">Amount</label>
                            <input type="number" class="form-control" name="amount[]" placeholder="Amount" step="any" min="0" value="${amounts[i]}">
                        </div>
                        <div class="col-md-6 text-end">
                            <button type="button" class="btn btn-danger btn-sm delete-row">Delete</button>
                        </div>
                    `;
                        newRow.html(cols);
                        $("#amountRowAddContainer").append(newRow);
                    }
                } else {
                    $("#amount").val(""); // Clear input if no data
                }

                // $("#amount").val(amount);
                $("#payment_checkbox").val(1);
                $("#tds_category").val(tdsCategoryId).trigger('change');
                $("#tds_amount").val(tdsAmount);
                $("#payment_checkbox").prop("checked", paymentCheckbox == 1);
                $("#id-payment").val(id);
                $("#vch_no_payment").val(vch_No);
                paymentModal.modal("show");
            });

            $("#modal-form, #payment-form").submit(function(e) {
                e.preventDefault();
                const F = $(this);
                removeErrors();
                F.find(".save-loader").show();
                var formData = new FormData(this);
                $.ajax({
                    url: $(this).attr("action"),
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
                            if (upcoming_payment !== null) upcoming_payment.ajax.reload();
                            modal.modal("hide");
                            paymentModal.modal("hide");
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


            $('.select2Type').select2({
                dropdownParent: modal
            });

        });
    </script>

    <!-- Item Show Model Script -->
    <script>
        $(document).on("click", ".itemModel", function() {
            var id = $(this).data("id");
            $.ajax({
                url: "{{ route('purchaseModelData') }}",
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
            });
        });
    </script>

    <!-- Add Amount Script -->
    <script>
        $(document).ready(function() {
            $("#add_amount").click(function() {
                var newRow = $("<div>").addClass("row mt-2 align-items-end");
                var cols = `
            <div class="col-md-6">
                <label class="form-label">Amount</label>
                <input type="number" class="form-control" name="amount[]" placeholder="Amount" step="any" min="0">
            </div>
            <div class="col-md-6 text-end">
                <button type="button" class="btn btn-danger btn-sm delete-row">Delete</button>
            </div>
        `;
                newRow.html(cols);
                $("#amountRowAddContainer").append(newRow);
            });

            // Remove row when delete button is clicked
            $(document).on("click", ".delete-row", function() {
                $(this).closest(".row").remove();
            });
        });
    </script>

    <!-- Reset Button Script -->
    <script>
        $(document).ready(function() {
            $(document).on("click", ".reset-form", function() {
                var form = $(this).closest("form")[0]; // Get the parent form
                if (form) {
                    form.reset(); // Reset all input fields

                    // Reset select2 dropdowns if they are used
                    $(form).find("select").val('').trigger('change');

                    // Clear dynamically added rows in the form
                    $(form).find(".dynamic-container").html(
                        ""); // Use a common class for all dynamic containers

                    // Reset checkboxes and radio buttons
                    $(form).find("input[type=checkbox], input[type=radio]").prop("checked", false);
                }
            });
        });
    </script>

    <script>
        $(document).ready(function() {
            $(document).on("click", ".purchaseReturnModel", function() {
                var id = $(this).data("id");
                $.ajax({
                    url: "{{ route('report.purchaseReturnModelData') }}",
                    type: 'POST',
                    data: {
                        id: id,
                        action: 'invoice'
                    },
                    success: function(res) {
                        if (res) {
                            $("#purchase-return-show-modal .modal-body").html(res);
                            $("#purchase-return-show-modal").modal("show");
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
                        url: "{{ route('purchaseDelete') }}",
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

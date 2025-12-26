@extends('Layouts.app')

@section('title', 'Production Sale Order Report')

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
        <div class="col-md-3">
            <label class="form-label">Item</label>
            <select class="form-select select2" id="item_id" multiple style="width: 100%" name="item_id">
                <option value="">Select Type</option>
                @foreach ($items as $item)
                    <option value="{{ $item->id }}">{{ $item->name }}</option>
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
        <div class="col-md-2">
            <label class="form-label">status</label>
            <select class="form-select select2" id="is_boq_status" style="width: 100%" name="is_boq_status">
                <option value="">Select Status</option>
                <option value="Pending">Pending</option>
                <option value="Partially Completed">Partially Completed</option>
                <option value="Completed">Completed</option>
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
    <style>
        .select2-container {
            width: 100% !important;
        }
    </style>
    <div class="col-12 col-sm-12">
        <div class="card card-danger card-outline card-outline-tabs">
            <div class="card-header p-0 border-bottom-0">
                <ul class="nav nav-tabs" id="custom-tabs-four-tab" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" id="pending-tab" data-status="pending" data-toggle="pill"
                            href="#custom-tabs-four-profile" role="tab" aria-controls="custom-tabs-four-profile"
                            aria-selected="true">Pending</a>
                    </li>
                    <!-- <li class="nav-item">
                        <a class="nav-link" id="completed-tab" data-status="completed" data-toggle="pill"
                            href="#custom-tabs-four-messages" role="tab" aria-controls="custom-tabs-four-messages"
                            aria-selected="false">Completed</a>
                    </li> -->
                </ul>
            </div>
            <div class="card-body">
                <div class="tab-content" id="custom-tabs-four-tabContent">
                    <div class="tab-pane fade active show" id="custom-tabs-four-profile" role="tabpanel"
                        aria-labelledby="pending-tab">
                        <div class="row">
                            <div class="col-md-12 table-responsive">
                                <table class="table table-loader table-vcenter table-hover card-table" id="pending-table"
                                    data-page_length="10" data-button="{{ $button }}">
                                    <thead>
                                        <tr>
                                            <th data-name="action" data-export="disabled">Action</th>
                                            <th data-name="date">Date</th>
                                            <th data-name="name_1">Client Name</th>
                                            <th data-name="vch_No">Purchase Order No</th>
                                            <th data-name="qty">Qty</th>
                                            <th data-name="is_boq_status">Status</th>
                                            <th data-name="remark">Remark</th>
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
                    <div class="tab-pane fade" id="custom-tabs-four-messages" role="tabpanel" aria-labelledby="pending-tab">
                        <div class="row">
                            <div class="col-md-12 table-responsive">
                                <table class="table table-loader table-vcenter table-hover card-table" id="completed-table"
                                    data-page_length="10" data-button="{{ $button }}">
                                    <thead>
                                        <tr>
                                            <th data-name="action" data-export="disabled">Action</th>
                                            <th data-name="date">Date</th>
                                            <th data-name="name_1">Client Name</th>
                                            <th data-name="vch_No">Purchase Order No</th>
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
                                        <input type="tel" class="form-control" id="store_phone" name="store_phone"
                                            placeholder="Store Phone" pattern="^[6-9]\d{9}$"
                                            title="Mobile number should be 10 digit and start with 6,7,8 or 9">
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-4">
                                <div class="row">
                                    <div class="col-xl-12">
                                        <label class="form-label">Store Email</label>
                                        <input type="email" class="form-control" id="store_email" name="store_email[]"
                                            placeholder="Store Email">
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
                                        <input type="email" class="form-control" id="store_cc_email" name="store_cc_email[]"
                                            placeholder="CC Email">
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
                                        <input type="tel" class="form-control" id="purchase_phone" name="purchase_phone"
                                            placeholder="Purchase Phone" pattern="^[6-9]\d{9}$"
                                            title="Mobile number should be 10 digit and start with 6,7,8 or 9">
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-4">
                                <div class="row">
                                    <div class="col-xl-12">
                                        <label class="form-label">Purchase Email</label>
                                        <input type="email" class="form-control" id="purchase_email" name="purchase_email[]"
                                            placeholder="Purchase Email">
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
                                <select class="form-select need-required-permission" id="work_category" style="width: 100%;"
                                    name="work_category">
                                    <option value="">Select Work Category</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Site</label>
                                <select class="form-select need-required-permission" id="site" style="width: 100%;"
                                    name="site">
                                    <option value="">Select Site</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Buyer</label>
                                <select class="form-select need-required-permission" id="buyer" style="width: 100%;"
                                    name="buyer">
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
                                <select class="form-select need-required-permission" id="new_type" style="width: 100%;"
                                    name="new_type">
                                    <option value="">Select Type</option>
                                </select>
                            </div>
                        </div>
                        <div class="modal-footer d-flex justify-content-between">
                            <button class="btn me-auto" data-dismiss="modal" type="button">Close</button>
                            <div>
                                <button class="btn btn-warning mr-1" id="reset-form" type="button">Reset</button>
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

    <div class="modal fade" id="boqModal" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-globe"></i> {{ env('APP_NAME') }}</h5>
                    <button class="close" data-dismiss="modal" type="button" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <form id="boqForm">
                    <div class="modal-body">
                        <div id="boqLoader" class="text-center py-5">
                            <div class="spinner-border text-primary"></div>
                        </div>

                        <div id="boqModalBody" class="d-none"></div>
                    </div>
                    <div class="modal-footer justify-content-between">
                        <button class="btn btn-default" data-dismiss="modal" type="button">Close</button>
                        <div class="d-flex justify-content-end mt-3">
                            <button type="submit" class="btn btn-primary" id="boq-model">
                                Submit BOQ
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
@push('javascript')
    <script>
        $(document).ready(function () {
            var pending = null;
            var permission = "{{ auth()->user()->hasPermissionTo('compulsory-field') ? 'true' : 'false' }}";
            var completed = null;
            var modal = $("#country-modal");
            $(document).on("shown.bs.tab", "#pending-tab, #completed-tab", function (e) {
                var status = $(this).data("status");
                var startDate = $("#start_date").val();
                var endDate = $("#end_date").val();
                var partyId = $("#party_id").val();
                var itemId = $("#item_id").val();


                if (status == "pending") {
                    if (pending) {
                        return pending.ajax.reload();
                    }
                    pending = window.table(
                        "#pending-table",
                        "{{ route('getProductionSaleOrderReport') }}", {
                        additionalData: () => {
                            return {
                                _token: "{{ csrf_token() }}",
                                status: status,
                                from_date: $("#start_date").val(),
                                to_date: $("#end_date").val(),
                                party_id: $("#party_id").val(),
                                item_id: $("#item_id").val(),
                                color: $('#color').val(),
                                is_boq_status: $('#is_boq_status').val(),
                            }
                        },
                        createdRow: function (row, data, dataIndex) {
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
                        "{{ route('getProductionSaleOrderReport') }}", {
                        additionalData: () => {
                            return {
                                _token: "{{ csrf_token() }}",
                                status: status,
                                from_date: $("#start_date").val(),
                                to_date: $("#end_date").val(),
                                party_id: $("#party_id").val(),
                                item_id: $("#item_id").val(),
                                color: $('#color').val(),
                                is_boq_status: $('#is_boq_status').val(),
                            }
                        },
                        createdRow: function (row, data, dataIndex) {
                            // Apply inline styles received from the backend
                            if (data.row_style) {
                                $(row).attr('style', data.row_style);
                            }
                        },
                        onInit: function () {
                            table.load();
                        }
                    }
                    );
                }
            })
            $('#pending-tab').trigger('shown.bs.tab');
            $("#search").click(function () {
                $('.nav-link.active').trigger('shown.bs.tab');
            });
        });
    </script>

    <!-- Item Show Model Script -->
    <script>
        $(document).ready(function () {
            $(document).on("click", ".itemModel", function () {
                $(".page-loader").show();
                var id = $(this).data("id");
                $.ajax({
                    url: "{{ route('productionSaleOrderModelData') }}",
                    type: 'POST',
                    data: {
                        id: id
                    },
                    success: function (res) {
                        if (res) {
                            $("#item-show-modal .modal-body").html(res);
                            $("#item-show-modal").modal("show");
                        } else {
                            sweetAlert("error", res.message);
                        }
                    },
                    error: function (xhr, status, error) {
                        sweetAlert("error", "An error occurred while processing the request.");
                    }
                }).always(function () {
                    $(".page-loader").hide();
                });
            });
        });
    </script>

    <script>
        $(document).ready(function () {
            $(document).on("click", ".invoiceModel", function () {
                var id = $(this).data("id");
                $.ajax({
                    url: "{{ route('saleModelData') }}",
                    type: 'POST',
                    data: {
                        id: id,
                        action: 'invoice'
                    },
                    success: function (res) {
                        if (res) {
                            $("#order-invoice-modal .modal-body").html(res);
                            $("#order-invoice-modal").modal("show");
                        } else {
                            sweetAlert("error", res.message);
                        }
                    },
                    error: function (xhr, status, error) {
                        sweetAlert("error", "An error occurred while processing the request.");
                    }
                });
            });

            $(document).on("click", "#item-show-modal .sale_order_form", function (e) {
                e.preventDefault(); // important if button is type="submit"

                var ref = $(this).closest('.row');

                var remark = ref.find('[name="remark"]').val();
                var is_boq_status = ref.find('[name="is_boq_status"]').val();
                var id = $(this).data("id");

                $.ajax({
                    url: "{{ route('productionSaleOrderForm') }}",
                    type: "POST",
                    loaders: true,
                    data: {
                        id: id,
                        remark: remark,
                        is_boq_status: is_boq_status
                    },
                    headers: {
                        "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content") // Laravel CSRF
                    },
                    success: function (res) {
                        if (res.success) {
                            sweetAlert("success", res.message);
                            $("#pending-tab").trigger("shown.bs.tab");
                            $("#item-show-modal").modal("hide");
                        } else {
                            sweetAlert("error", res.message || "Something went wrong");
                        }
                    },
                    error: function (xhr) {
                        sweetAlert("error", xhr.responseJSON?.message || "An error occurred while processing the request.");
                    }
                });
            });

        });
    </script>

    <script>
        $(document).ready(function () {
            $(document).on("click", ".saleReturnModel", function () {
                var id = $(this).data("id");
                $.ajax({
                    url: "{{ route('report.saleReturnModelData') }}",
                    type: 'POST',
                    data: {
                        id: id,
                        action: 'invoice'
                    },
                    success: function (res) {
                        if (res) {
                            $("#sale-return-show-modal .modal-body").html(res);
                            $("#sale-return-show-modal").modal("show");
                        } else {
                            sweetAlert("error", res.message);
                        }
                    },
                    error: function (xhr, status, error) {
                        sweetAlert("error", "An error occurred while processing the request.");
                    }
                });
            });
        });
    </script>

    <script>
        $(document).ready(function () {

            $(document).on('click', '.boqModel', function () {
                let saleOrderId = $(this).data('id');
                let vch_No = $(this).data('vch_no');
                let type = $(this).data('type');
                if (type == 'show') {
                    $("#boqForm #boq-model").hide();
                } else {
                    $("#boqForm #boq-model").show();
                }
                // Reset modal content
                $('#boqLoader').removeClass('d-none');
                $('#boqModalBody').addClass('d-none').html('');

                // 1️⃣ OPEN MODAL FIRST
                $('#boqModal').modal({
                    backdrop: 'static',
                    keyboard: false
                });

                // Fix z-index for nested modal
                setTimeout(function () {
                    $('.modal-backdrop').not(':first').css('z-index', 1055);
                    $('#boqModal').css('z-index', 1060);
                }, 10);

                // 2️⃣ CALL AJAX AFTER MODAL IS SHOWN
                $('#boqModal').one('shown.bs.modal', function () {

                    $.ajax({
                        url: "{{ route('getBoqModalData') }}",
                        data: {
                            saleOrderId: saleOrderId,
                            id: saleOrderId,
                            type: type
                        },
                        type: 'GET',
                        success: function (response) {
                            $('#boqLoader').addClass('d-none');
                            $('#boqModalBody').removeClass('d-none').html(response);
                            $('#boqModalBody #sale_id').val(saleOrderId);
                            $('#boqModalBody #sale_ref').val(vch_No);
                            initSelect2();
                        },
                        error: function () {
                            $('#boqLoader').html('<p class="text-danger">Failed to load BOQ</p>');
                        }
                    });

                });
            });

            let rowIndex = 0;

            // Call this when modal content is loaded (after AJAX success)
            function syncRowIndex() {
                rowIndex = $('#boqTable tbody tr').length; // next index will be current row count
            }

            // Rebuild all name indexes sequentially (0,1,2,3...)
            function reindexRows() {
                $('#boqTable tbody tr').each(function (i) {
                    $(this).find('select[name^="items["]').attr('name', `items[${i}][item_id]`);
                    $(this).find('input[name^="items["]').attr('name', `items[${i}][qty]`);
                });
                rowIndex = $('#boqTable tbody tr').length;
            }

            // ADD ROW
            $(document).on('click', '#addRow', function () {

                // Ensure rowIndex is correct before adding
                syncRowIndex();

                let row = `
                        <tr>
                            <td>
                                <select name="items[${rowIndex}][item_id]" class="form-control item-select" required>
                                    <option value="">Select Item</option>
                                    @foreach ($items as $item)
                                        <option value="{{ $item->id }}">{{ $item->name }}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td>
                                <input type="number" name="items[${rowIndex}][qty]" class="form-control" required min="1">
                            </td>
                            <td>
                                <button type="button" class="btn btn-sm btn-danger removeRow"><i class="fa fa-times"></i></button>
                            </td>
                        </tr>
                    `;

                $('#boqTable tbody').append(row);
                initSelect2();
                rowIndex++;
            });


            // REMOVE ROW (keep at least 1 row)
            $(document).on('click', '.removeRow', function () {
                if ($('#boqTable tbody tr').length > 1) {
                    $(this).closest('tr').remove();
                    reindexRows();
                } else {
                    alert('At least one item is required.');
                }
            });

            // SUBMIT FORM (AJAX)
            $(document).on('submit', '#boqForm', function (e) {
                e.preventDefault();

                let form = $(this);
                let submitBtn = form.find('button[type="submit"]');

                submitBtn.prop('disabled', true).text('Submitting...');

                $.ajax({
                    url: "{{ route('boqStore') }}",
                    type: "POST",
                    data: form.serialize(),
                    success: function (res) {
                        if (res.status) {
                            sweetAlert('success', res.message);
                            $('#boqModal').modal('hide');
                        }
                    },
                    error: function (xhr) {
                        sweetAlert('error', 'Validation failed!');
                    },
                    complete: function () {
                        submitBtn.prop('disabled', false).text('Submit BOQ');
                    }
                });
            });

            // RESET FORM WHEN MODAL OPENS
            $('#boqModal').on('shown.bs.modal', function () {
                initSelect2();
                rowIndex = $('#boqTable tbody tr').length;
            });

        });
        function initSelect2(context = document) {
            $(context).find('.item-select').select2({
                placeholder: 'Select Item',
                width: '100%',
                dropdownParent: $('#boqModal') // VERY IMPORTANT for modal
            });
        }
    </script>


@endpush
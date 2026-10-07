@extends("Layouts.app")

@section("title", "Manage Customers")

@section("header")
@php
$statusList =[
[ "name" => "All", "count" => 2,"class" => "secondary" ],
[ "name" => "Active", "count" => 5 ,"class" => "success" ],
[ "name" => "Inactive", "count" => 6,"class" => "danger" ],
]
@endphp
<style>
</style>
<div class="page-header d-print-none">
    <div class="row g-2 align-items-center">
        <div class="col">
            <div class="page-pretitle">
                Manage Users
            </div>
            <h2 class="page-title">
                Customers
            </h2>
        </div>
        <div class="col-auto ms-auto d-print-none">
            <div class="btn-list">
                <a class="btn btn-danger d-none d-sm-inline-block add-new-btn " href="{{ route("customer.create") }}">
                    <svg class="icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                        viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round"
                        stroke-linejoin="round">
                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                        <path d="M12 5l0 14" />
                        <path d="M5 12l14 0" />
                    </svg>
                    Create Customers
                </a>
                <a class="btn btn-danger d-sm-none btn-icon add-new-btn" href="{{ route("customer.create") }}" aria-label="Create new report">
                    <svg class="icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                        viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round"
                        stroke-linejoin="round">
                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                        <path d="M12 5l0 14" />
                        <path d="M5 12l14 0" />
                    </svg>
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
@section("content")

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-status-top bg-primary"></div>
            <div class="mt-3 p-2">
                <div class="card-body">
                    <div class="container-fluid">
                        <div class="row row-cards">
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="status" class="form-label">Status</label>
                                    <select class="form-control select2" id="status">
                                        <option value="">All</option>
                                        <option value="ACTIVE">ACTIVE</option>
                                        <option value="INACTIVE">INACTIVE</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2 mb-3">
                                <div class="form-group">
                                    <label class="form-label">State <span class="text-danger">*</span></label>
                                    <?php
                                    \App\Helpers\Forms::select2(
                                        "state_id",
                                        [
                                            "configs" => [
                                                "ajax" => [
                                                    "type" => "POST",

                                                    "url" => route("common.getStateSelect2"),

                                                    "dataType" => "json",

                                                    "data" => []
                                                ],

                                                "allowClear" => true,

                                                "placeholder" => __("Select State"),
                                            ],
                                            "required" => true,
                                        ],
                                        [],
                                        true
                                    );
                                    ?>
                                </div>
                            </div>
                            <div class="col-md-2 mb-3">
                                <div class="form-group">
                                    <label class="form-label">City <span class="text-danger">*</span></label>
                                    <?php
                                    \App\Helpers\Forms::select2(
                                        "city_id",
                                        [
                                            "configs" => [
                                                "ajax" => [
                                                    "type" => "POST",

                                                    "url" => route("common.getCitySelect2"),

                                                    "dataType" => "json",

                                                    "data" => [
                                                        "state_id" => "[name='state_id']",
                                                    ],
                                                ],

                                                "allowClear" => true,

                                                "placeholder" => __("Select City"),
                                            ],
                                            "required" => true,
                                        ],
                                        isset($customer) && !empty($customer->city_id) ? [$customer->city_id, $customer->city?->name] : [],
                                        true
                                    );
                                    ?>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="from" class="form-label">From Date</label>
                                    <input type="date" value="" class=" form-control from" id="from">
                                </div>
                            </div>
                            <div class="col-md-2 ">
                                <div class="form-group">
                                    <label for="to" class="form-label">To Date</label>
                                    <input type="date" class=" form-control to" value="<?= date('Y-m-d'); ?>" id="to">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <button type="button" class="btn btn-outline-primary float-end" id="search">Search</button>
                </div>
            </div>
        </div>
        <div class="card mt-1">
            <div class="card-status-top bg-primary"></div>
            <div class="card-header d-print-none justify-content-between">
                <h3 class="card-title">
                    Customers
                </h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-12">
                        <div class="table-responsive">
                            <form action="{{ route('customer.coverPrint') }}" method="post" target="_blank">
                                @csrf
                                <table class="table table-vcenter table-hover card-table" id="datatable11">
                                    <thead>
                                        <tr>
                                            <th>
                                                <input type="checkbox" class="all-check" name="select_all" value="1">
                                            </th>
                                            <th>Serial No</th>
                                            <!-- <th>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Action &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</th> -->
                                            <th style="padding-left: 25px !important;padding-right: 34px !important;">Action</th>
                                            <th>Name</th>
                                            <th>City</th>
                                            <th>Mobile</th>
                                            <th>Email</th>
                                            <th>State</th>
                                            <th>Country</th>
                                            <th>Address</th>
                                            <th>Coutact Person</th>
                                            <th>Address 2</th>
                                            <th>Pincode</th>
                                            <th>Password</th>
                                            <th>GST</th>
                                            <th>PAN</th>
                                            <th>Reason Remark</th>
                                            <th>Status</th>
                                            <th>Created By</th>
                                            <th>Created At</th>
                                            <th>Updated At</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                </table>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@push("javascript")
<script>
    $(document).ready(function() {
        var table = $('#datatable11').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('customer.getList') }}",
                type: 'POST',
                data: function(d) {
                    d._token = "{{ csrf_token() }}";
                    d.status = $('#status').val();
                    d.fromDate = $('#from').val();
                    d.toDate = $('#to').val();
                    d.partyId = $('#party_id').val();
                    d.state = $('#select_state_id').val();
                    d.city = $('#select_city_id').val();
                },
            },
            columns: [{
                    data: 'checkbox',
                    orderable: false,
                    searchable: false,
                    render: function(data, type, full, meta) {
                        return '<input type="checkbox" class="print-check" name="id[]" value="' + full.id + '">';
                    }
                },
                {
                    data: 'id'
                },
                {
                    data: 'action',
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'name'
                },
                {
                    data: 'city'
                },
                {
                    data: 'mobile'
                },
                {
                    data: 'email'
                },
                {
                    data: 'state'
                },
                {
                    data: 'country'
                },
                {
                    data: 'address'
                },
                {
                    data: 'contact_person'
                },
                {
                    data: 'area'
                },
                {
                    data: 'pincode'
                },
                {
                    data: 'password'
                },
                {
                    data: 'gst'
                },
                {
                    data: 'pan_no'
                },
                {
                    data: 'status'
                },
                {
                    data: 'reference'
                },
                {
                    data: 'created_by'
                },
                {
                    data: 'created_at',
                    render: function(data, type, full, meta) {
                        return new Date(data).toLocaleString('en-GB', {
                            timeZone: 'Asia/Kolkata',
                            hour12: false
                        });
                    }
                },
                {
                    data: 'updated_at',
                    render: function(data, type, full, meta) {
                        return new Date(data).toLocaleString('en-GB', {
                            timeZone: 'Asia/Kolkata',
                            hour12: false
                        });
                    }
                },
            ],
            order: [
                [1, 'asc']
            ],
            responsive: false,
            lengthMenu: [10, 100, 500, 1000, 2000, 5000],
            pageLength: 100,
            dom: "<'row'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6'f>>" +
                "<'row'<'col-sm-12'tr>>" +
                "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>" +
                "B",
            // buttons: ["excel"],
        });

        table
            .buttons()
            .container()
            .appendTo(`#datatable11_wrapper .col-md-6:eq(0)`);

        $(document).on('click', '.all-check', function() {
            $('.print-check').prop('checked', $(this).prop('checked'));
        });

        $(document).on('click', '#search', function() {
            table.draw();
        });
    });
</script>
@endpush

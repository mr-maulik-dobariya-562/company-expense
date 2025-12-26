@extends('Layouts.app')

@section('title', 'Sale Report')

@section('header')
    <form class="row mb-2" id="filter-form">
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
            <select class="form-select select2" id="party_id" style="width: 100%" multiple name="party_id[]">
                <option value="">Select Type</option>
                @foreach ($partys as $party)
                    <option value="{{ $party->id }}">{{ $party->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label">Item</label>
            <select class="form-select select2" id="item_id" style="width: 100%" multiple name="item_id[]">
                <option value="">Select Type</option>
                @foreach ($items as $item)
                    <option value="{{ $item->id }}">{{ $item->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label">Work Category</label>
            <select class="form-select select2" id="filter_work_category" style="width: 100%" multiple
                name="work_category_id[]">
                <option value="">Select Work Category</option>
                @foreach ($workCategories as $category)
                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="col-md-2">
            <label class="form-label">Site</label>
            <select class="form-select select2" id="filter_site" style="width: 100%" multiple name="filter_site">
                <option value="">Select Site</option>
                @foreach ($site as $value)
                    <option value="{{ $value->id }}">{{ $value->name }}</option>
                @endforeach
            </select>
        </div>
        @if (auth()->user()->hasPermissionTo('sale-filter'))
            <div class="col-md-2">
                <label class="form-label">Buyer</label>
                <select class="form-select select2" id="filter_buyer" style="width: 100%" multiple name="filter_buyer">
                    <option value="">Select Buyer</option>
                    @foreach ($buyer as $key => $value)
                        <option value="{{ $value->id }}">{{ $value->name }}</option>
                    @endforeach
                </select>
            </div>


        @endif
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
                <option value="">Select color</option>
                <option value="red">Red</option>
                <option value="green">Green</option>
                <option value="orange">Orange</option>
                <option value="purple">Purple</option>
                {{-- <option value="white">White</option> --}}
                <option value="default">Default</option>
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label">&nbsp;</label>
            <input type="text" class="form-control" name="search_manual" placeholder="Search" autocomplete="off">
        </div>
        <div class="col-md-1 pt-4">
            <button class="btn btn-danger" id="search" type="button">Search</button>
        </div>
    </form>

@endsection
@if (auth()->user()->hasPermissionTo('export-button'))
    @php
        $button = true;
    @endphp
@else
    @php
        $button = false;
    @endphp
@endif
@section('content')
    <div class="row">
        <div class="col-12 col-sm-12">
            <div class="card card-danger card-outline card-outline-tabs">
                <div class="card-header p-0 border-bottom-0">
                    <ul class="nav nav-tabs" id="custom-tabs-four-tab" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" id="pending-tab" data-status="info" data-toggle="pill"
                                href="#custom-tabs-four-profile" role="tab" aria-controls="custom-tabs-four-profile"
                                aria-selected="true">Info Pending</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="upcoming-mrn-tab" data-status="mrn" data-toggle="pill"
                                href="#custom-tabs-four-upcoming-mrn" role="tab"
                                aria-controls="custom-tabs-four-upcoming-mrn" aria-selected="false">MRN</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="upcoming-payment-tab" data-status="payment" data-toggle="pill"
                                href="#custom-tabs-four-upcoming-payment" role="tab"
                                aria-controls="custom-tabs-four-upcoming-payment" aria-selected="false">Payment</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="completed-tab" data-status="completed" data-toggle="pill"
                                href="#custom-tabs-four-messages" role="tab"
                                aria-controls="custom-tabs-four-messages" aria-selected="false">Completed</a>
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
                                                <th data-name="sale_order">Purchase Order</th>
                                                <th data-name="vch_No">Invoice No</th>
                                                <th data-name="date">Date</th>
                                                <th data-name="name_1">Client Name</th>
                                                <th data-name="amount">Amount</th>
                                                <th data-name="pendingAmount">Pending Amount</th>
                                                <th data-name="mrn_no">MRN Number</th>
                                                <th data-name="mrn_date">MRN Date</th>
                                                <th data-name="due_date">Due Date</th>
                                                @if (auth()->user()->hasPermissionTo('sale-column'))
                                                    <th data-name="mrn_attachment">Mrn Attachment</th>
                                                @endif
                                                @if (auth()->user()->hasPermissionTo('sale-attachment'))
                                                    <th data-name="document">Invoice Copy</th>
                                                @endif
                                                <th data-name="new_type">Type</th>
                                                <th data-name="vehicle_no">Vehicle No</th>
                                                <th data-name="transport">Transport</th>
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
                        <div class="tab-pane fade" id="custom-tabs-four-upcoming-mrn" role="tabpanel"
                            aria-labelledby="upcoming-mrn-tab">
                            <div class="row">
                                <div class="col-md-12 table-responsive">
                                    <table class="table table-loader table-vcenter table-hover card-table"
                                        id="upcoming-mrn-table" data-page_length="10" data-button="{{ $button }}">
                                        <thead>
                                            <tr>
                                                <th data-name="id" data-export="disabled">Serial No </th>
                                                <th data-name="action" data-export="disabled">Action</th>
                                                <th data-name="sale_order">Purchase Order</th>
                                                <th data-name="vch_No">Invoice No</th>
                                                <th data-name="date">Date</th>
                                                <th data-name="name_1">Client Name</th>
                                                <th data-name="amount">Amount</th>
                                                <th data-name="pendingAmount">Pending Amount</th>
                                                <th data-name="mrn_no">MRN Number</th>
                                                <th data-name="mrn_date">MRN Date</th>
                                                <th data-name="due_date">Due Date</th>
                                                @if (auth()->user()->hasPermissionTo('sale-column'))
                                                    <th data-name="mrn_attachment">Mrn Attachment</th>
                                                @endif
                                                @if (auth()->user()->hasPermissionTo('sale-attachment'))
                                                    <th data-name="document">Invoice Copy</th>
                                                @endif
                                                <th data-name="new_type">Type</th>
                                                <th data-name="vehicle_no">Vehicle No</th>
                                                <th data-name="transport">Transport</th>
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
                                                <th data-name="id" data-export="disabled">Status </th>
                                                <th data-name="action" data-export="disabled">Action</th>
                                                <th data-name="sale_order">Purchase Order</th>
                                                <th data-name="vch_No">Invoice No</th>
                                                <th data-name="date">Date</th>
                                                <th data-name="name_1">Client Name</th>
                                                <th data-name="amount">Amount</th>
                                                <th data-name="pendingAmount">Pending Amount</th>
                                                <th data-name="mrn_no">MRN Number</th>
                                                <th data-name="mrn_date">MRN Date</th>
                                                <th data-name="due_date">Due Date</th>
                                                @if (auth()->user()->hasPermissionTo('sale-column'))
                                                    <th data-name="mrn_attachment">Mrn Attachment</th>
                                                @endif
                                                @if (auth()->user()->hasPermissionTo('sale-attachment'))
                                                    <th data-name="document">Invoice Copy</th>
                                                @endif
                                                <th data-name="new_type">Type</th>
                                                <th data-name="vehicle_no">Vehicle No</th>
                                                <th data-name="transport">Transport</th>
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
                                                <th data-name="action" data-export="disabled">Action</th>
                                                <th data-name="sale_order">Purchase Order</th>
                                                <th data-name="vch_No">Invoice No</th>
                                                <th data-name="date">Date</th>
                                                <th data-name="name_1">Client Name</th>
                                                <th data-name="amount">Amount</th>
                                                <th data-name="pendingAmount">Pending Amount</th>
                                                <th data-name="mrn_no">MRN Number</th>
                                                <th data-name="mrn_date">MRN Date</th>
                                                <th data-name="due_date">Due Date</th>
                                                @if (auth()->user()->hasPermissionTo('sale-column'))
                                                    <th data-name="mrn_attachment">Mrn Attachment</th>
                                                    <th data-name="new_type">Type</th>
                                                @endif
                                                @if (auth()->user()->hasPermissionTo('sale-attachment'))
                                                    <th data-name="document">Invoice Copy</th>
                                                @endif
                                                <th data-name="vehicle_no">Vehicle No</th>
                                                <th data-name="transport">Transport</th>
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


    <form id="modal-form" action="{{ route('saleDataUpdate') }}" enctype="multipart/form-data" method="POST">
        @csrf
        <div class="modal modal-blur fade" id="country-modal" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-xl" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title"> <span class="title">Update</span> Sale</h5>
                        <button class="close" data-dismiss="modal" type="button" aria-label="Close">
                            <span aria-hidden="true">×</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <input id="id" type="hidden" name="id">
                        <input id="vch_no" type="hidden" name="vch_No">
                        <div class="hide-section">
                            <div class="row">
                                <div class="col-xl-4">
                                    <div class="row">
                                        <div class="col-xl-12">
                                            <label class="form-label">Store Phone No</label>
                                            <input class="form-control" id="store_phone" type="tel"
                                                name="store_phone" placeholder="Store Phone" pattern="^[6-9]\d{9}$"
                                                title="Mobile number should be 10 digit and start with 6,7,8 or 9">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xl-4">
                                    <div class="row">
                                        <div class="col-xl-12">
                                            <label class="form-label">Store Email</label>
                                            <input class="form-control" id="store_email" type="email"
                                                name="store_email[]" placeholder="Store Email">
                                        </div>
                                    </div>
                                    <div class="dynamic-container" id="storeRowAddContainer"></div>
                                    <div class="col-md-4 pt-2">
                                        <button class="btn btn-danger" id="add_store" type="button">+ Add</button>
                                    </div>
                                </div>
                                <div class="col-xl-4">
                                    <div class="row">
                                        <div class="col-xl-12">
                                            <label class="form-label">Store CC Email</label>
                                            <input class="form-control" id="store_cc_email" type="email"
                                                name="store_cc_email[]" placeholder="CC Email">
                                        </div>
                                    </div>
                                    <div class="dynamic-container" id="storeRowAddContainerEmail"></div>
                                    <div class="col-md-3 pt-2">
                                        <button class="btn btn-danger" id="add_store_email" type="button">+
                                            Add</button>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-xl-4">
                                    <div class="row">
                                        <div class="col-xl-12">
                                            <label class="form-label">Purchase Phone No</label>
                                            <input class="form-control" id="purchase_phone" type="tel"
                                                name="purchase_phone" placeholder="Purchase Phone" pattern="^[6-9]\d{9}$"
                                                title="Mobile number should be 10 digit and start with 6,7,8 or 9">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xl-4">
                                    <div class="row">
                                        <div class="col-xl-12">
                                            <label class="form-label">Purchase Email</label>
                                            <input class="form-control" id="purchase_email" type="email"
                                                name="purchase_email[]" placeholder="Purchase Email">
                                        </div>
                                    </div>
                                    <div class="dynamic-container" id="purchaseRowAddContainer"></div>

                                    <div class="col-md-3 pt-2">
                                        <button class="btn btn-danger" id="add_purchase" type="button">+ Add</button>
                                    </div>
                                </div>
                                <div class="col-xl-4">
                                    <div class="row">
                                        <div class="col-xl-12">
                                            <label class="form-label">Purchase CC Email</label>
                                            <input class="form-control" id="purchase_cc_email" type="email"
                                                name="purchase_cc_email[]" placeholder="CC Email">
                                        </div>
                                    </div>
                                    <div class="dynamic-container" id="purchaseRowAddContainerEmail"></div>

                                    <div class="col-md-3 pt-2">
                                        <button class="btn btn-danger" id="add_purchase_email" type="button">+
                                            Add</button>
                                    </div>
                                </div>
                            </div>
                            <div class="row mt-3">
                                <div class="col-md-3">
                                    <label class="form-label">Work Category</label>
                                    <select class="form-select form-control select2 need-required-permission"
                                        id="work_category" style="width: 100%;" name="work_category">
                                        <option value="">Select Work Category</option>
                                        @foreach ($workCategories as $value)
                                            <option value="{{ $value->id }}">{{ $value->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Site</label>
                                    <select class="form-select form-control select2 need-required-permission"
                                        id="site" style="width: 100%;" name="site">
                                        <option value="">Select Site</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Buyer</label>
                                    <select class="form-select form-control select2 need-required-permission"
                                        id="buyer" style="width: 100%;" name="buyer">
                                        <option value="">Select Buyer</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Type</label>
                                    <select class="form-select form-control select2 need-required-permission"
                                        id="new_type" style="width: 100%;" name="new_type">
                                        <option value="">Select Type</option>
                                        @foreach ($types as $value)
                                            <option value="{{ $value->name }}">{{ $value->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-md-4">
                                <label class="form-label">Lead Delivery Date</label>
                                <input class="form-control" id="lead_delivery_date" type="date"
                                    name="lead_delivery_date" placeholder="Lead Delivery Date">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Invoice Copy</label>
                                <input class="form-control" id="document" type="file" name="document"
                                    accept="*">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label" for="mrn_reminder">MRN Email Reminder</label>
                                <div class="form-check">
                                    <input class="form-check-input not-empty" id="mrn_reminder" type="checkbox"
                                        name="mrn_reminder" value="1">
                                    <label class="form-check-label" for="mrn_reminder">Subscribe to MRN Emails</label>
                                </div>
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

    <form id="mrn-form" action="{{ route('saleMrnDataUpdate') }}" enctype="multipart/form-data" method="POST">
        @csrf
        <div class="modal modal-blur fade" id="mrn-modal" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg" role="document-modal">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title"> <span class="title">Update</span> MRN Sale</h5>
                        <button class="close" data-dismiss="modal" type="button" aria-label="Close">
                            <span aria-hidden="true">×</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <input id="id-upcoming" type="hidden" name="id">
                        <input id="vch_no_mrn" type="hidden" name="vch_No">
                        <div class="row mb-3 align-items-end">
                            <div class="col-md-6">
                                <label class="form-label">MRN No</label>
                                <input class="form-control need-required-permission" id="mrn_no" type="text"
                                    name="mrn_no" placeholder="MRN No" title="">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">MRN Date</label>
                                <input class="form-control need-required-permission" id="mrn_date" type="date"
                                    name="mrn_date" placeholder="MRN Date">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Credit Days</label>
                                <input class="form-control need-required-permission" id="credited_days" type="number"
                                    name="credited_days" placeholder="created days" pattern="^[0-9]*$"
                                    title="Only numeric characters are allowed">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Document</label>
                                <input class="form-control" id="mrn_image" type="file" name="mrn_image"
                                    accept="*">
                            </div>
                            <div class="col-md-6 mt-3">
                                <div class="form-check">
                                    <input class="form-check-input not-empty" id="payment_reminder" type="checkbox"
                                        name="payment_reminder" value="1">
                                    <label class="form-check-label" for="payment_reminder">Subscribe to Payment
                                        Emails</label>
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
        </div>
    </form>
    <form id="payment-form" action="{{ route('salePaymentDataUpdate') }}" enctype="multipart/form-data" method="POST">
        @csrf
        <div class="modal modal-blur fade" id="payment-modal" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document-modal">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title"> <span class="title">Update</span> Payment Sale</h5>
                        <button class="close" data-dismiss="modal" type="button" aria-label="Close">
                            <span aria-hidden="true">×</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <input id="id-payment" type="hidden" name="id">
                        <input id="vch_no_payment" type="hidden" name="vch_No">
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
                                    <input class="form-control" id="tds_amount" type="number" name="tds_amount"
                                        placeholder="TDS Amount" step="any" min="0"
                                        title="Only numeric characters are allowed">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Amount</label>
                                <input class="form-control" id="amount" type="number" name="amount[]"
                                    placeholder="Amount" step="any" min="0"
                                    title="Only numeric characters are allowed">
                            </div>
                            <div class="col-md-6 p-2">
                                <div class="form-check">
                                    <input class="form-check-input" id="payment_checkbox" type="checkbox"
                                        name="payment_checkbox" value="1">
                                    <label class="form-check-label" for="payment_checkbox">Check me out</label>
                                </div>
                            </div>
                        </div>

                        <!-- Container for dynamically added Amount rows -->
                        <div class="dynamic-container" id="amountRowAddContainer"></div>

                        <div class="row mt-3">
                            <div class="col-md-3 pt-2 pb-1">
                                <button class="btn btn-danger" id="add_amount" type="button">+ Add</button>
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
        </div>
    </form>

    <div class="modal fade show" id="item-show-modal" aria-modal="true" role="dialog">
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

    <div class="modal fade show" id="sale-return-show-modal" aria-modal="true" role="dialog">
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
            var upcoming_mrn = null;
            var upcoming_payment = null;
            var permission = "{{ auth()->user()->hasPermissionTo('compulsory-field') ? 'true' : 'false' }}";
            var modal = $("#country-modal");
            var upcomingModal = $("#mrn-modal");
            var paymentModal = $("#payment-modal");
            $(document).on("shown.bs.tab", "#pending-tab, #completed-tab, #upcoming-mrn-tab, #upcoming-payment-tab",
                function(e) {
                    var status = $(this).data("status");
                    var startDate = $("#start_date").val();
                    var endDate = $("#end_date").val();
                    var partyId = $("#party_id").val();
                    var itemId = $("#item_id").val();
                    var workCategoryId = $("#filter_work_category").val();
                    var siteId = $("#filter_site").val();
                    var buyerId = $("#filter_buyer").val();
                    var newType = $("#filter_type").val();
                    // var color = $("#color").val();
                    if (status == "info") {
                        if (pending) {
                            return pending.ajax.reload();
                        }

                        pending = window.table(
                            "#pending-table",
                            "{{ route('getSaleReport') }}", {
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
                                        search_manual: $('input[name="search_manual"]').val()
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
                    } else if (status == "mrn") {
                        if (upcoming_mrn) {
                            return upcoming_mrn.ajax.reload();
                        }
                        upcoming_mrn = window.table(
                            "#upcoming-mrn-table",
                            "{{ route('getSaleReport') }}", {
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
                                        search_manual: $('input[name="search_manual"]').val()
                                    }
                                },
                                createdRow: function(row, data, dataIndex) {
                                    // Apply inline styles received from the backend
                                    if (data.row_style) {
                                        $(row).find('td').not(':first').attr('style', data.row_style);
                                    }
                                }
                            }
                        );
                    } else if (status == "payment") {
                        if (upcoming_payment) {
                            return upcoming_payment.ajax.reload();
                        }
                        upcoming_payment = window.table(
                            "#upcoming-payment-table",
                            "{{ route('getSaleReport') }}", {
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
                                        search_manual: $('input[name="search_manual"]').val()
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
                            "{{ route('getSaleReport') }}", {
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
                                        search_manual: $('input[name="search_manual"]').val()
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

            $(document).on("click", ".edit-btn", async function() {
                var id = $(this).data("id");
                var sale_order_id = $(this).data("sale_order_id");
                var vch_No = $(this).data("vch-no");
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
                var leadDeliveryDate = $(this).data("lead_delivery_date");
                var buyer = $(this).data("buyer");
                var work_category = $(this).data("work_category");
                var site = $(this).data("site");
                var mrn_reminder = $(this).data("mrn_reminder");
                var new_type = $(this).data("new_type");
                var invoiceCopyFile = $(this).data("document");

                $('#buyer, #work_category, #site,#new_type').select2();
                $("#work_category").val(work_category).trigger('change', {
                    site_id: site,
                    buyer_id: buyer
                });
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
                $("#lead_delivery_date").val(leadDeliveryDate);
                $("#id").val(id);
                $("#vch_no").val(vch_No);
                $("#new_type").val(new_type).trigger('change');
                $("#mrn_reminder").prop("checked", mrn_reminder);

                if (sale_order_id > 0) {
                    $(".hide-section").hide();
                } else {
                    $(".hide-section").show();
                    $('.need-required-permission').attr('required', (permission == "true"));
                }

                $('#lead_delivery_date').prop('required', (permission == "true"));
                $('#document').prop('required', (permission == "true" && !invoiceCopyFile));

                // Show the modal
                var modal = $("#country-modal");
                modal.modal("show");

                // await new Promise(resolve => setTimeout(resolve, 800));
                // $("#site").val(site).trigger('change');
                // await new Promise(resolve => setTimeout(resolve, 500));
                // $("#buyer").val(buyer).trigger('change');
            });

            $(document).on("click", ".mrn-edit-btn", function() {
                var id = $(this).data("id");
                var vch_No = $(this).data("vch-no");
                var mrnNo = $(this).data("mrn_no");
                var mrnDate = $(this).data("mrn_date");
                var CreatedDays = $(this).data("credited_days");
                var payment_reminder = $(this).data("payment_reminder");

                $('.need-required-permission').attr('required', (permission == "true"));
                $("#mrn_no").val(mrnNo);
                $("#mrn_date").val(mrnDate);
                $("#credited_days").val(CreatedDays);
                // $("#mrn_reminder").prop("checked", mrn_reminder);
                $("#id-upcoming").val(id);
                $("#vch_no_mrn").val(vch_No);
                $("#payment_reminder").prop("checked", +payment_reminder);
                upcomingModal.modal("show");
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

            $("#modal-form, #mrn-form, #payment-form").submit(function(e) {
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
                            // if (pending !== null) pending.ajax.reload();
                            // if (completed !== null) completed.ajax.reload();
                            // if (upcoming_mrn !== null) upcoming_mrn.ajax.reload();
                            // if (upcoming_payment !== null) upcoming_payment.ajax.reload();
                            $('.nav-link.active').trigger('shown.bs.tab');
                            modal.modal("hide");
                            upcomingModal.modal("hide");
                            paymentModal.modal("hide");
                            sweetAlert("success", res.message);
                        } else {
                            sweetAlert("error", res.message);
                        }
                    },
                    error: function(xhr, status, error) {
                        sweetAlert("error", xhr?.responseJSON?.message ||
                            "An error occurred while processing the request.");
                    },
                    complete: function() {
                        F.find(".save-loader").hide();
                    }
                });
            });

            $(document).on("change", "#work_category", function(e, trigger) {
                const {
                    site_id,
                    buyer_id
                } = trigger ?? {}
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
                                    buyer_id
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
                const {
                    buyer_id
                } = trigger || {}
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
                                let selected = buyer_id == value.id ? 'selected' : '';
                                $("#buyer").append(
                                    `<option value="${value.id}" ${selected}>${value.name}</option>`
                                );
                            });
                        }
                    }
                });
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

    <!-- Item Show Model Script -->
    <script>
        $(document).on("click", ".itemModel", function() {
            var id = $(this).data("id");
            $.ajax({
                url: "{{ route('saleModelData') }}",
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

        $(document).on("click", ".invoiceModel", function() {
            var id = $(this).data("id");
            $.ajax({
                url: "{{ route('saleOrderModelData') }}",
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

    <!-- Select2 Code For Model -->
    <script>
        $(document).ready(function() {
            $('#country-modal').on('shown.bs.modal', function() {
                $(".select2", $("#country-modal")).select2({
                    dropdownParent: $("#country-modal")
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
                        url: "{{ route('saleDelete') }}",
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

    <script>
        $(document).ready(function() {
            $('input[name="search_manual"]').on('keyup', function() {
                // const value = $(this).val();
                $('.nav-link.active').trigger('shown.bs.tab');
            });
        });
    </script>
@endpush

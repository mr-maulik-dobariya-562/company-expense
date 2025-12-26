@extends('Layouts.app')

@section('title', 'Expense')

@section('header')
    <style>
        #nprogress .bar {
            z-index: 2000;
        }

        #nprogress .peg {
            box-shadow: 0 0 10px #29d, 0 0 5px #29d;
        }
    </style>
    <div class="page-header d-print-none">
        <div class="row g-2 align-items-center">
            <div class="col">
                <div class="page-pretitle">
                    Master
                </div>
                <h2 class="page-title">
                    Expense
                </h2>
            </div>
            <div class="col-auto ms-auto d-print-none">
                @if (auth()->user()->hasPermissionTo('expense-create'))
                <div class="btn-list">
                    <a class="btn btn-danger d-none d-sm-inline-block add-new-btn" data-bs-toggle="modal"
                        data-bs-target="#city-modal" href="#">
                        <svg class="icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                            viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round"
                            stroke-linejoin="round">
                            <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                            <path d="M12 5l0 14" />
                            <path d="M5 12l14 0" />
                        </svg>
                        Create new expense
                    </a>
                    <a class="btn btn-danger d-sm-none btn-icon add-new-btn" data-bs-toggle="modal"
                        data-bs-target="#city-modal" href="#" aria-label="Create new report">
                        <svg class="icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                            viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round"
                            stroke-linejoin="round">
                            <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                            <path d="M12 5l0 14" />
                            <path d="M5 12l14 0" />
                        </svg>
                    </a>
                </div>
                @endif
            </div>
        </div>
    </div>
@endsection
@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-status-top bg-primary"></div>
                <div class="card-header">
                    <h3 class="card-title">Expense</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-12 table-responsive">
                            <table class="table table-loader table-vcenter card-table" id="expense-table">
                                <thead>
                                    <tr>
                                        <th data-name="id">Serial No </th>
                                        <th data-name="amount">Amount</th>
                                        <th data-name="date">Date</th>
                                        <th data-name="description">Description</th>
                                        <th data-name="created_by">created by</th>
                                        <th data-name="created_at">Created At</th>
                                        <th data-name="updated_at">Last Update At</th>
                                        <th data-name="action" data-orderable="false">Action</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <form id="modal-form" action="{{ route('master.expense.store') }}" method="POST">
        @csrf
        <div class="modal modal-blur fade" id="modal" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-dialog-scrollable modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title"> <span class="title">Add</span> Expense</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">×</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="row mb-3 align-items-end">
                            <div class="col-md-6">
                                <label class="form-label">Amount <span class="text-danger">*</span></label>
                                <input class="form-control" type="text" name="amount" placeholder="Enter Expense Amount" />
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Date <span class="text-danger">*</span></label>
                                <input class="form-control" type="date" name="date" placeholder="Enter Expense Date" />
                            </div>
                            <div class="col-md-12 pt-2">
                                <label class="form-label">Description <span class="text-danger">*</span></label>
                                <textarea class="form-control" name="description"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button class="btn me-auto" data-dismiss="modal" type="button">Close</button>
                        <button class="btn btn-danger" type="submit">
                            Save <i class="fas fa-1x fa-sync-alt fa-spin save-loader" style="display:none"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
@endsection

@push('javascript')
    <script>
        $(document).ready(function() {
            const modal = $("#modal");
            window.edit = false;
            var table = window.table(
                "#expense-table",
                "{{ route('master.expense.getList') }}",
            );


            $(".add-new-btn").click(function() {
                // Clear existing dynamic rows
                $("#storeRowAddContainer").html("");
                modal.modal("show");
                modal.find(".title").text("Add");
                modal.find("input").val("");
                modal.parents("form").attr("action", '{{ route("master.expense.store") }}');
                window.edit = false;
            });

            $("#modal-form").submit(function(e) {
                e.preventDefault();
                const F = $(this)
                removeErrors();
                F.find(".save-loader").show();
                const http = App.http.jqClient;
                var U;
                if (window.edit) {
                    U = http.put(
                        F.attr("action"),
                        F.serialize(),
                    );
                } else {
                    U = http.post(
                        F.attr("action"),
                        F.serialize(),
                    );
                }
                U.then(res => {
                    if (res.success) {
                        table.ajax.reload();
                        modal.modal("hide");
                        sweetAlert("success", res.message);
                    } else {
                        sweetAlert("error", res.message);

                    }
                }).always(() => {
                    F.find(".save-loader").hide()
                })

            });

            $(document).on('click', '.edit-btn', function() {
                // Clear existing dynamic rows
                $("#storeRowAddContainer").html("");
                const {
                    id,
                    amount,
                    date,
                    description
                } = $(this).data();
                const edit_url = "{{ route('master.expense.update', ':id') }}";
                modal.parents("form").attr("action", edit_url.replace(":id", id));
                modal.find(".title").text("Edit");
                modal.find("input[name=amount]").val(amount);
                modal.find("input[name=date]").val(date);
                modal.find("textarea[name=description]").val(description);
                modal.modal("show");
                window.edit = true;
            });
        });
    </script>
@endpush

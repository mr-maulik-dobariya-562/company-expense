@extends('Layouts.app')

@section('title', 'Manage Users')

@section('header')
    <style>
    </style>
    <div class="page-header d-print-none">
        <div class="row g-2 align-items-center">
            <div class="col">
                <div class="page-pretitle">
                    Manage Users
                </div>
                <h2 class="page-title">
                    Users
                </h2>
            </div>
            <div class="col-auto ms-auto d-print-none">
                <div class="btn-list">
                    <a class="btn btn-danger d-none d-sm-inline-block add-new-btn " data-bs-toggle="modal"
                        data-bs-target="#modal" href="#">
                        <svg class="icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                            viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round"
                            stroke-linejoin="round">
                            <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                            <path d="M12 5l0 14" />
                            <path d="M5 12l14 0" />
                        </svg>
                        Create Users
                    </a>
                    <a class="btn btn-danger d-sm-none btn-icon add-new-btn" data-bs-toggle="modal" data-bs-target="#modal"
                        href="#" aria-label="Create new report">
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
@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-status-top bg-primary"></div>
                <div class="card-header">
                    <h3 class="card-title">User</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-12 table-responsive">
                            <table class="table table-loader table-vcenter table-hover card-table" id="datatable">
                                <thead>
                                    <tr>
                                        <th data-name="id">Serial No </th>
                                        <th data-name="name">Name</th>
                                        <th data-name="mobile">Mobile</th>
                                        <th data-name="email">Email</th>
                                        <th data-name="status">Status</th>
                                        <th data-name="role">Role</th>
                                        <th data-name="created_by">Created By</th>
                                        <th data-name="created_at">Created At</th>
                                        <th data-name="updated_at">Last Update At</th>
                                        <th data-name="action" data-orderable="false">Action</th>
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
    </div>
    <form id="modal-form" action="{{ route('users.store') }}" enctype="multipart/form-data" method="POST">
        @csrf
        <div class="modal modal-blur fade" id="modal" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-xl modal-dialog-scrollable" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title"> <span class="title">Add</span> User</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">×</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <fieldset class="form-fieldset row">
                            <div class="col-md-6 mb-2">
                                <label class="form-label required">Name</label>
                                <input class="form-control" type="text" name="name" placeholder="Enter Name"
                                    autocomplete="off">
                            </div>
                            <div class="col-md-6 mb-2">
                                <label class="form-label required">Mobile</label>
                                <input class="form-control" type="text" name="mobile" placeholder="Enter Mobile"
                                    autocomplete="off">
                            </div>
                            <div class="col-md-6 mb-2">
                                <label class="form-label required">Email</label>
                                <input class="form-control" type="email" name="email" placeholder="Enter Email"
                                    autocomplete="off">
                            </div>
                            <div class="col-md-6 mb-2">
                                <label class="form-label required">Uername</label>
                                <input class="form-control" type="text" name="username" placeholder="Enter Username"
                                    autocomplete="off" pattern="^(?:(?! ).)*$"
                                    title="White spaces are not allowed in this field">
                            </div>
                            <div class="col-md-6 mb-2">
                                <label class="form-label">Password</label>
                                <input class="form-control" type="password" name="password" placeholder="Enter Password"
                                    autocomplete="off">
                            </div>
                            <div class="col-md-6 mb-2">
                                <label class="form-label">Role <span class="text-danger">*</span></label>
                                <select class="form-select select2" name="role">
                                    <option value="">Select Role</option>
                                    @foreach ($roles as $role)
                                        <option value="{{ $role->name }}">{{ $role->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6 mb-2">
                                <label class="form-label">Status <span class="text-danger">*</span></label>
                                <select class="form-select select2" name="status">
                                    <option value="">Select Status</option>
                                    @foreach (['ACTIVE', 'INACTIVE'] as $status)
                                        <option value="{{ $status }}">{{ $status }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </fieldset>
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
            $('.select2').select2({
                dropdownAutoWidth: true,
                width: '100%',
                placeholder: "Select something",
                dropdownParent: modal
            })
            var table = window.table(
                "#datatable",
                "{{ route('users.getList') }}",
            );
            window.edit = false;

            $(".add-new-btn").click(function() {
                modal.modal("show");
                modal.find("input").val("");
                modal.find(".select2").val("").trigger("change");
                modal.find(".title").text("Add");
                modal.parents("form").attr("action", '{{ route('users.store') }}');
                window.edit = false;
            });

            $("#modal-form").submit(function(e) {
                ;
                e.preventDefault();
                const F = $(this);
                removeErrors();
                F.find(".save-loader").show();

                // Create a new FormData object
                var formData = new FormData(this);

                // Determine the request type and URL
                var actionUrl = F.attr("action");
                var requestType = window.edit ? 'POST' :
                    'POST'; // Use POST for both, handle PUT in server-side

                // If it's a PUT request, append _method=PUT to the form data
                if (window.edit) {
                    formData.append('_method', 'PUT');
                }

                $.ajax({
                    url: actionUrl,
                    type: requestType,
                    data: formData,
                    processData: false,
                    contentType: false,
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(res) {
                        if (res.success) {
                            table.ajax.reload();
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
            })

            $(document).on('click', '.edit-btn', function() {
                const dataset = $(this).data();
                $.each(dataset, function(key, value) {
                    const selector = modal.find("[name='" + key + "']");
                    if (selector.length) {
                        if (selector.is("select")) {
                            selector.val(value).trigger("change");
                        } else {
                            selector.val(value);
                        }
                    }
                })
                const branch_ids = typeof dataset.branch_id === "string" ? dataset.branch_id.split(",") : [
                    dataset.branch_id
                ];
                modal.find("select[name='branch_id[]']").val(branch_ids).trigger("change");
                const location_ids = typeof dataset.location_id === "string" ? dataset.location_id.split(
                    ",") : [dataset.location_id];
                modal.find("select[name='location_id[]']").val(location_ids).trigger("change");
                const edit_url = "{{ route('users.update', ':id') }}";
                modal.parents("form").attr("action", edit_url.replace(":id", dataset.id));
                modal.find(".title").text("Edit");
                modal.modal("show");
                window.edit = true;
            })
        });
    </script>
@endpush

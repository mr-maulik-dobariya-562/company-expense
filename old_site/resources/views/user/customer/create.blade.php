@extends("Layouts.app")

@section("title", "Customers")
@php
$actionRoute = isset($customer) ? route("customer.update", ["customer" => $customer->id]) : route("customer.store");
@endphp
@section("header")
<div class="page-header d-print-none mt-2">
    <div class="row g-2 align-items-center">
        <div class="col">
            <div class="page-pretitle">
                Manage Users
            </div>
            <h2 class="page-title">
                Customer
            </h2>
        </div>
        <div class="col-auto ms-auto d-print-none">
            <div class="btn-list">
                <a class="btn btn-danger d-none d-sm-inline-block add-new-btn" href="{{ route('customer.index') }}">
                    <i class="fas fa-arrow-left me-2"></i>
                    Back
                </a>
                <a class="btn btn-danger d-sm-none btn-icon add-new-btn" href="{{ route('customer.index') }}" aria-label="Create new report">
                    <i class="fa-solid fa-arrow-left"></i>
                </a>
            </div>
        </div>
    </div>
</div>
@endsection

@section("content")
<div class="row">
    <div class="col-md-12">
        <form id="customerForm" action="{{ $actionRoute }}" method="POST" enctype="multipart/form-data">
            @csrf
            @if (isset($customer))
            @method("PUT")
            @else
            @method("POST")
            @endif
            <div class="card">
                <div class="card-status-top bg-primary"></div>
                <div class="card-header">
                    <h3 class="card-title">Customer</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <div class="form-group">
                                        <label class='form-label'>Name: <strong class="text-danger">*</strong>
                                        </label>
                                        <input class='form-control @error("name") is-invalid @enderror' value='{{ old("name", $customer?->name ?? "") }}' name="name" placeholder="name" type="text" required>
                                        <x-input-error class="mt-2" :messages="$errors->get('name')" />
                                    </div>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <div class="form-group">
                                        <label class='form-label'>Mobile: </label>
                                        <input class='form-control @error("mobile") is-invalid @enderror' value='{{ old("mobile", $customer?->mobile ?? "") }}' name="mobile" placeholder="mobile" type="text" pattern="[0-9]{10}" maxlength="10">
                                        <x-input-error class="mt-2" :messages="$errors->get('mobile')" />
                                    </div>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <div class="form-group">
                                        <label class='form-label'>Email: <strong class="text-danger">*</strong></label>
                                        <input class='form-control @error("email") is-invalid @enderror' value='{{ old("email", $customer?->email ?? "") }}' name="email" placeholder="email" type="email" required>
                                        <x-input-error class="mt-2" :messages="$errors->get('email')" />
                                    </div>
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Country <span class="text-danger">*</span></label>
                                    <?php
                                    \App\Helpers\Forms::select2(
                                        "country_id",
                                        [
                                            "configs" => [
                                                "ajax" => [
                                                    "type" => "POST",

                                                    "url" => route("common.getCountrySelect2"),

                                                    "dataType" => "json",
                                                ],

                                                "allowClear" => true,

                                                "placeholder" => __("Select Country"),
                                            ],
                                            "required" => true,
                                        ],
                                        isset($customer) && !empty($customer->country_id) ? [$customer->country_id, $customer->country?->name] : false,
                                    );
                                    ?>
                                </div>
                                <div class="col-md-4 mb-3">
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

                                                    "data" => [
                                                        "country_id" => "[name='country_id']",
                                                    ],
                                                ],

                                                "allowClear" => true,

                                                "placeholder" => __("Select State"),
                                            ],
                                            "required" => true,
                                        ],
                                        isset($customer) && !empty($customer->state_id) ? [$customer->state_id, $customer->state?->name] : false,
                                    );
                                    ?>
                                </div>
                                <div class="col-md-4 mb-3">
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
                                        isset($customer) && !empty($customer->city_id) ? [$customer->city_id, $customer->city?->name] : false,
                                    );
                                    ?>
                                </div>

                                <div class="col-md-4 mb-3">
                                    <div class="form-group">
                                        <label class='form-label'>Address: <strong class="text-danger">*</strong>
                                        </label>
                                        <input class='form-control @error("address") is-invalid @enderror' value='{{ old("address", $customer?->address ?? "") }}' name="address" placeholder="address" type="text" required>
                                        <x-input-error class="mt-2" :messages="$errors->get('address')" />
                                    </div>
                                </div>

                                <div class="col-md-4 mb-3">
                                    <div class="form-group">
                                        <label class='form-label'>Contact Person:
                                        </label>
                                        <input class='form-control @error("contact_person") is-invalid @enderror' value='{{ old("contact_person", $customer?->contact_person ?? "") }}' name="contact_person" placeholder="contact person" type="text">
                                        <x-input-error class="mt-2" :messages="$errors->get('contact_person')" />
                                    </div>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <div class="form-group">
                                        <label class='form-label'>Area/Address 2: </label>
                                        <input class='form-control @error("pincode") is-invalid @enderror' value='{{ old("area", $customer?->area ?? "") }}' name="area" placeholder="area" type="text" required>
                                        <x-input-error class="mt-2" :messages="$errors->get('area')" />
                                    </div>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <div class="form-group">
                                        <label class='form-label'>Pincode: <strong class="text-danger">*</strong> </label>
                                        <input class='form-control @error("pincode") is-invalid @enderror' value='{{ old("pincode", $customer?->pincode ?? "") }}' name="pincode" placeholder="pincode" type="number" required>
                                        <x-input-error class="mt-2" :messages="$errors->get('pincode')" />
                                    </div>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <div class="form-group">
                                        <label class='form-label'>Password: <?= !isset($customer) ? '<strong class="text-danger">*</strong>' : '' ?>
                                        </label>
                                        <input class='form-control @error("password") is-invalid @enderror' value='{{ old("password", $customer?->password ?? "") }}' name="password" placeholder="password" type="text">
                                        <x-input-error class="mt-2" :messages="$errors->get('password')" />
                                    </div>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <div class="form-group">
                                        <label class='form-label'>GST No:
                                        </label>
                                        <input class='form-control @error("gst") is-invalid @enderror' value='{{ old("gst", $customer?->gst ?? "") }}' name="gst" placeholder="GST No" type="text">
                                        <x-input-error class="mt-2" :messages="$errors->get('gst')" />
                                    </div>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <div class="form-group">
                                        <label class='form-label'>PAN No:
                                        </label>
                                        <input class='form-control @error("pan_no") is-invalid @enderror' value='{{ old("pan_no", $customer?->pan_no ?? "") }}' name="pan_no" placeholder="Pan No" type="text">
                                        <x-input-error class="mt-2" :messages="$errors->get('pan_no')" />
                                    </div>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <div class="form-group">
                                        <label class='form-label'>Discount:
                                        </label>
                                        <input class='form-control @error("discount") is-invalid @enderror' value='{{ old("discount", $customer?->discount ?? "") }}' name="discount" placeholder="Discount" type="number">
                                        <x-input-error class="mt-2" :messages="$errors->get('discount')" />
                                    </div>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <div class="form-group">
                                        <label class='form-label'>Reason Remark:</label>
                                        <input class='form-control @error("other_reason_remark") is-invalid @enderror' value='{{ old("other_reason_remark", $customer?->other_reason_remark ?? "") }}' name="other_reason_remark" placeholder="Hide Reason Remark" type="text">
                                        <x-input-error class="mt-2" :messages="$errors->get('other_reason_remark')" />
                                    </div>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <div class="form-group">
                                        <label class="form-label">Status <span class="text-danger">*</span></label>
                                        <select class="form-control select2" id="status" name="status">
                                            <option value="ACTIVE" <?php echo old("status", $customer?->status ?? '') == 'ACTIVE' ? 'selected' : ''; ?>>ACTIVE</option>
                                            <option value="INACTIVE" <?php echo old("status", $customer?->status ?? '') == 'INACTIVE' ? 'selected' : ''; ?>>INACTIVE</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <div class="form-group">
                                        <label class='form-label'>Reference:</label>
                                        <input class='form-control @error("reference") is-invalid @enderror' value='{{ old("reference", $customer?->reference ?? "") }}' name="reference" placeholder="Reference" type="text">
                                        <x-input-error class="mt-2" :messages="$errors->get('reference')" />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer text-center">
                    <button class="btn btn-danger ms-auto" type="submit">
                        Submit <i class="fas fa-1x fa-sync-alt fa-spin save-loader" style="display:none"></i>
                    </button>
                    <a class=" btn btn-warning me-2" href="{{ route('customer.index') }}">Back</a>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@push("javascript")
<script>
    window.edit = <?php echo isset($customer) ? "true" : "false"; ?>;

    $(document).ready(function() {
        $("#customerForm").submit(function(e) {
            e.preventDefault();
            const F = $(this)
            removeErrors();
            F.find(".save-loader").show();
            const http = App.http.jqClient;
            http[window.edit ? 'put' : 'post'](
                F.attr("action"),
                F.serialize()).then(res => {
                if (res.success) {
                    sweetAlert("success", res.message);
                    setTimeout(() => {
                        window.location = "{{ route('customer.index') }}";
                    }, 1000);
                } else {
                    sweetAlert("error", res.message);
                }
            }).always(() => {
                F.find(".save-loader").hide()
            }).catch(function() {
                $('html, body').animate({
                    scrollTop: 0
                }, 'slow');
            });
        });

        $('.addNewRow').click(function() {
            // Clone the last row
            var newRow = $('.mainRow:last').clone();

            // Clear the input values in the cloned row
            newRow.find('input').val('');
            newRow.find('.is-invalid').removeClass('is-invalid'); // Remove validation error classes if any

            // Append the new row to the customerDetails div
            $('#customerDetails').append(newRow);
        });

        $(document).on('click', '.remove-btn', function() {
            var $row = $(this).closest('.mainRow');
            var $tbody = $row.closest('#customerDetails');

            if ($tbody.find('.mainRow').length > 1) {
                $row.remove();
            } else {
                sweetAlert("error", 'Last row cannot be deleted.');
            }
        });
    });
</script>
@endpush

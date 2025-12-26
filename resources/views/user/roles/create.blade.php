@extends('Layouts.app')

@section('title', 'Role & Permission')

@php
    $actionRoute = isset($role) ? route('users.role.update', ['role' => $role->id]) : route('users.role.store');
@endphp

@section('header')
    <style>
        #nprogress .bar {
            z-index: 2000;
        }

        #nprogress .peg {
            box-shadow: 0 0 10px #29d, 0 0 5px #29d;
        }

        .table th {
            padding: 12px 70px 12px 70px;
        }

        .table .parent {
            padding: 10px 10px 10px 90px !important;
        }

        .table .sub-parent {
            padding: 10px 10px 10px 150px !important;
        }
    </style>
    <div class="page-header d-print-none">
        <x-loader display='flex' />
        <div class="row g-2 align-items-center">
            <div class="col">
                <div class="page-pretitle">
                    Roles
                </div>
                <h2 class="page-title">
                    Roles & Permissions
                </h2>
            </div>
            <div class="col-auto ms-auto d-print-none">
                <div class="btn-list">
                    <a class="btn btn-danger d-none d-sm-inline-block add-new-btn" href="{{ route('users.role.index') }}">
                        Back
                    </a>
                    <a class="btn btn-danger d-sm-none btn-icon add-new-btn" href="{{ route('users.role.index') }}"
                        aria-label="Create new report">
                        <i class="fa-solid fa-arrow-left"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('content')
    <div class="row">
        <div class="col-md-12">
            <form action="{{ $actionRoute }}" method="POST" enctype="multipart/form-data">
                @csrf
                @csrf
                @if (isset($role))
                    @method('PUT')
                @else
                    @method('POST')
                @endif
                <div class="card">
                    <div class="card-status-top bg-primary"></div>
                    <div class="card-header">
                        <h3 class="card-title">Roles & Permission</h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-12 mb-5">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class='form-label'>Role Name: <strong class="text-danger">*</strong>
                                            </label>
                                            <input class='form-control @error('name') is-invalid @enderror'
                                                value='{{ old('name', $role?->name ?? '') }}' name="name"
                                                placeholder="Enter Role name" type="text" required>
                                            <x-input-error class="mt-2" :messages="$errors->get('name')" />
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class='form-label'>Status: <strong class="text-danger">*</strong>
                                            </label>
                                            <select class="form-select select2" name='status' required
                                                style="width: 100%;">
                                                @foreach (['active', 'inactive'] as $row)
                                                    <option value="{{ $row }}" @selected($row == old('status', $role?->status ?? $loop->first))>
                                                        {{ __($row) }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            <x-input-error class="mt-2" :messages="$errors->get(' status')" />
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-12 mt-2 table-responsive ">
                                <table class="table table-vcenter card-table" id="role-table">
                                    <thead>
                                        <tr>
                                            <th>Menus</th>
                                            <th>View</th>
                                            <th>Create</th>
                                            <th>Edit</th>
                                            <th>Delete</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @if (!isset($role))
                                            <?php $sidebarMenu = App\Helpers\Theme::getMenu(); ?>
                                        @endif
                                        <td class="" colspan="6">
                                            <label class="form-check">
                                                <input class="form-check-input master-check" type="checkbox">
                                                <span class="form-check-label h5"> All Permission</span>
                                            </label>
                                        </td>
                                        @foreach ($sidebarMenu as $index => $row)
                                            <?php if (isset($row['children'])) {
                                                $col = 6;
                                            } else {
                                                $col = 0;
                                            } ?>
                                            <tr>
                                                <td colspan="{{ $col }}">
                                                    <i class="icon me-2 text-secondary fs-3 {{ $row['icon'] }}"></i>
                                                    <span>{{ $row['name'] }}</span>
                                                </td>
                                                @if (!isset($row['children']) && !empty($row['url']))
                                                    <input type="hidden" name="slug[]" value="{{ $row['menu'] }}">
                                                    <td>
                                                        <div class="custom-control custom-switch">
                                                            <label class="switch">
                                                                <input class="hiddenValue" type="hidden" name="view[]"
                                                                    value="<?php if (isset($row['view']) && $row['view'] == 1) {
                                                                        echo '1';
                                                                    } else {
                                                                        echo '0';
                                                                    } ?>">
                                                                <input class="switchInput" type="checkbox"
                                                                    <?php if (isset($row['view']) && $row['view'] == 1) {
                                                                        echo 'checked';
                                                                    } else {
                                                                    } ?> value="">
                                                            </label>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <div class="custom-control custom-switch">
                                                            <label class="switch">
                                                                <input class="hiddenValue" type="hidden" name="create[]"
                                                                    value="<?php if (isset($row['create']) && $row['create'] == 1) {
                                                                        echo '1';
                                                                    } else {
                                                                        echo '0';
                                                                    } ?>">
                                                                <input class="switchInput" type="checkbox"
                                                                    <?php if (isset($row['create']) && $row['create'] == 1) {
                                                                        echo 'checked';
                                                                    } else {
                                                                    } ?> value="">
                                                            </label>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <div class="custom-control custom-switch">
                                                            <label class="switch">
                                                                <input class="hiddenValue" type="hidden" name="edit[]"
                                                                    value="<?php if (isset($row['edit']) && $row['edit'] == 1) {
                                                                        echo '1';
                                                                    } else {
                                                                        echo '0';
                                                                    } ?>">
                                                                <input class="switchInput" type="checkbox"
                                                                    <?php if (isset($row['edit']) && $row['edit'] == 1) {
                                                                        echo 'checked';
                                                                    } else {
                                                                    } ?> value="">
                                                            </label>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <div class="custom-control custom-switch">
                                                            <label class="switch">
                                                                <input class="hiddenValue" type="hidden" name="delete[]"
                                                                    value="<?php if (isset($row['delete']) && $row['delete'] == 1) {
                                                                        echo '1';
                                                                    } else {
                                                                        echo '0';
                                                                    } ?>">
                                                                <input class="switchInput" type="checkbox"
                                                                    <?php if (isset($row['delete']) && $row['delete'] == 1) {
                                                                        echo 'checked';
                                                                    } else {
                                                                    } ?> value="">
                                                            </label>
                                                        </div>
                                                    </td>
                                                @endif
                                                @if (isset($row['children']))
                                                    @foreach ($row['children'] as $child)
                                                        <?php if (isset($child['children'])) {
                                                            $col = 6;
                                                        } else {
                                                            $col = 0;
                                                        } ?>
                                            <tr>
                                                <td colspan="{{ $col }}" class="parent">
                                                    <i class="icon me-2 text-secondary fs-3 {{ $child['icon'] }}"></i>
                                                    <span>{{ $child['name'] }}</span>
                                                </td>
                                                @if (!isset($child['children']) && !empty($child['url']))
                                                    <input type="hidden" name="slug[]" value="{{ $child['menu'] }}">
                                                    <td>
                                                        <div class="custom-control custom-switch">
                                                            <label class="switch">
                                                                <input class="hiddenValue" type="hidden" name="view[]"
                                                                    value="<?php if (isset($child['view']) && $child['view'] == 1) {
                                                                        echo '1';
                                                                    } else {
                                                                        echo '0';
                                                                    } ?>">
                                                                <input class="switchInput slave-check view"
                                                                    data-permission="view"
                                                                    data-index="{{ $index }}" type="checkbox"
                                                                    <?php if (isset($child['view']) && $child['view'] == 1) {
                                                                        echo 'checked';
                                                                    } ?> value="">
                                                            </label>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <div class="custom-control custom-switch">
                                                            <label class="switch">
                                                                <input class="hiddenValue" type="hidden" name="create[]"
                                                                    value="<?php if (isset($child['create']) && $child['create'] == 1) {
                                                                        echo '1';
                                                                    } else {
                                                                        echo '0';
                                                                    } ?>">
                                                                <input class="switchInput slave-check create"
                                                                    data-permission="create"
                                                                    data-index="{{ $index }}" type="checkbox"
                                                                    <?php if (isset($child['create']) && $child['create'] == 1) {
                                                                        echo 'checked';
                                                                    } ?> value="">
                                                            </label>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <div class="custom-control custom-switch">
                                                            <label class="switch">
                                                                <input class="hiddenValue" type="hidden" name="edit[]"
                                                                    value="<?php if (isset($child['edit']) && $child['edit'] == 1) {
                                                                        echo '1';
                                                                    } else {
                                                                        echo '0';
                                                                    } ?>">
                                                                <input class="switchInput slave-check edit"
                                                                    data-permission="edit"
                                                                    data-index="{{ $index }}" type="checkbox"
                                                                    <?php if (isset($child['edit']) && $child['edit'] == 1) {
                                                                        echo 'checked';
                                                                    } ?> value="">
                                                            </label>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <div class="custom-control custom-switch">
                                                            <label class="switch">
                                                                <input class="hiddenValue" type="hidden" name="delete[]"
                                                                    value="<?php if (isset($child['delete']) && $child['delete'] == 1) {
                                                                        echo '1';
                                                                    } else {
                                                                        echo '0';
                                                                    } ?>">
                                                                <input class="switchInput slave-check delete"
                                                                    data-permission="delete"
                                                                    data-index="{{ $index }}" type="checkbox"
                                                                    <?php if (isset($child['delete']) && $child['delete'] == 1) {
                                                                        echo 'checked';
                                                                    } ?> value="">
                                                            </label>
                                                        </div>
                                                    </td>
                                                @endif
                                                @if (isset($child['children']))
                                                    @foreach ($child['children'] as $subChild)
                                            <tr>
                                                <input type="hidden" name="slug[]" value="{{ $subChild['menu'] }}">
                                                <td class="sub-parent">
                                                    <i class="icon me-2 text-secondary fs-3 {{ $subChild['icon'] }}"></i>
                                                    {{ $subChild['name'] }}
                                                </td>
                                                <td>
                                                    <div class="custom-control custom-switch">
                                                        <label class="switch">
                                                            <input class="hiddenValue" type="hidden" name="view[]"
                                                                value="<?php if (isset($subChild['view']) && $subChild['view'] == 1) {
                                                                    echo '1';
                                                                } else {
                                                                    echo '0';
                                                                } ?>">
                                                            <input class="switchInput slave-check view"
                                                                data-permission="view" data-index="{{ $index }}"
                                                                type="checkbox" <?php if (isset($subChild['view']) && $subChild['view'] == 1) {
                                                                    echo 'checked';
                                                                } ?> value="">
                                                        </label>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="custom-control custom-switch">
                                                        <label class="switch">
                                                            <input class="hiddenValue" type="hidden" name="create[]"
                                                                value="<?php if (isset($subChild['create']) && $subChild['create'] == 1) {
                                                                    echo '1';
                                                                } else {
                                                                    echo '0';
                                                                } ?>">
                                                            <input class="switchInput slave-check create"
                                                                data-permission="create" data-index="{{ $index }}"
                                                                type="checkbox" <?php if (isset($subChild['create']) && $subChild['create'] == 1) {
                                                                    echo 'checked';
                                                                } ?> value="">
                                                        </label>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="custom-control custom-switch">
                                                        <label class="switch">
                                                            <input class="hiddenValue" type="hidden" name="edit[]"
                                                                value="<?php if (isset($subChild['edit']) && $subChild['edit'] == 1) {
                                                                    echo '1';
                                                                } else {
                                                                    echo '0';
                                                                } ?>">
                                                            <input class="switchInput slave-check edit"
                                                                data-permission="edit" data-index="{{ $index }}"
                                                                type="checkbox" <?php if (isset($subChild['edit']) && $subChild['edit'] == 1) {
                                                                    echo 'checked';
                                                                } ?> value="">
                                                        </label>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="custom-control custom-switch">
                                                        <label class="switch">
                                                            <input class="hiddenValue" type="hidden" name="delete[]"
                                                                value="<?php if (isset($subChild['delete']) && $subChild['delete'] == 1) {
                                                                    echo '1';
                                                                } else {
                                                                    echo '0';
                                                                } ?>">
                                                            <input class="switchInput slave-check delete"
                                                                data-permission="delete" data-index="{{ $index }}"
                                                                type="checkbox" <?php if (isset($subChild['delete']) && $subChild['delete'] == 1) {
                                                                    echo 'checked';
                                                                } ?> value="">
                                                        </label>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                        @endif
                                        </tr>
                                        @endforeach
                                        @endif
                                        </tr>
                                        @endforeach
                                        <tr>
                                            <td>
                                                <label class="form-check">
                                                    <span class="form-check-label">Dashboard Sale Order</span>
                                                </label>
                                            </td>
                                            <td>
                                                <div class="custom-control custom-switch">
                                                    <label class="switch">
                                                        <input class="switchInput slave-check" name="saleorderbox"
                                                            data-index="" type="checkbox" value="sale-order-box"
                                                            <?php if (isset($permissionData) && in_array('sale-order-box', $permissionData)) {
                                                                echo 'checked';
                                                            } ?>>
                                                    </label>
                                                </div>
                                            </td>
                                            <td>
                                                <label class="form-check">
                                                    <span class="form-check-label">Dashboard Sale Invoice</span>
                                                </label>
                                            </td>
                                            <td colspan="2">
                                                <div class="custom-control custom-switch">
                                                    <label class="switch">
                                                        <input class="switchInput slave-check" name="salebox"
                                                            data-index="" type="checkbox" value="sale-box"
                                                            <?php if (isset($permissionData) && in_array('sale-box', $permissionData)) {
                                                                echo 'checked';
                                                            } ?>>
                                                    </label>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <label class="form-check">
                                                    <span class="form-check-label">Dashboard Purchase</span>
                                                </label>
                                            </td>
                                            <td>
                                                <div class="custom-control custom-switch">
                                                    <label class="switch">
                                                        <input class="switchInput slave-check" name="purchasebox"
                                                            data-index="" type="checkbox" value="purchase-box"
                                                            <?php if (isset($permissionData) && in_array('purchase-box', $permissionData)) {
                                                                echo 'checked';
                                                            } ?>>
                                                    </label>
                                                </div>
                                            </td>
                                            <td>
                                                <label class="form-check">
                                                    <span class="form-check-label">Sale Order Filter</span>
                                                </label>
                                            </td>
                                            <td colspan="2">
                                                <div class="custom-control custom-switch">
                                                    <label class="switch">
                                                        <input class="switchInput slave-check" name="saleorderfilter"
                                                            data-index="" type="checkbox" value="sale-order-filter"
                                                            <?php if (isset($permissionData) && in_array('sale-order-filter', $permissionData)) {
                                                                echo 'checked';
                                                            } ?>>
                                                    </label>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <label class="form-check">
                                                    <span class="form-check-label">Sale Invoice Filter</span>
                                                </label>
                                            </td>
                                            <td>
                                                <div class="custom-control custom-switch">
                                                    <label class="switch">
                                                        <input class="switchInput slave-check" name="salefilter"
                                                            data-index="" type="checkbox" value="sale-filter"
                                                            <?php if (isset($permissionData) && in_array('sale-filter', $permissionData)) {
                                                                echo 'checked';
                                                            } ?>>
                                                    </label>
                                                </div>
                                            </td>
                                            <td>
                                                <label class="form-check">
                                                    <span class="form-check-label">Sale Order Column</span>
                                                </label>
                                            </td>
                                            <td colspan="2">
                                                <div class="custom-control custom-switch">
                                                    <label class="switch">
                                                        <input class="switchInput slave-check" name="saleordercolumn"
                                                            data-index="" type="checkbox" value="sale-order-column"
                                                            <?php if (isset($permissionData) && in_array('sale-order-column', $permissionData)) {
                                                                echo 'checked';
                                                            } ?>>
                                                    </label>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <label class="form-check">
                                                    <span class="form-check-label">Sale Invoice Column</span>
                                                </label>
                                            </td>
                                            <td>
                                                <div class="custom-control custom-switch">
                                                    <label class="switch">
                                                        <input class="switchInput slave-check" name="salecolumn"
                                                            data-index="" type="checkbox" value="sale-column"
                                                            <?php if (isset($permissionData) && in_array('sale-column', $permissionData)) {
                                                                echo 'checked';
                                                            } ?>>
                                                    </label>
                                                </div>
                                            </td>
                                            <td>
                                                <label class="form-check">
                                                    <span class="form-check-label">Export Button</span>
                                                </label>
                                            </td>
                                            <td colspan="2">
                                                <div class="custom-control custom-switch">
                                                    <label class="switch">
                                                        <input class="switchInput slave-check" name="exportbutton"
                                                            data-index="" type="checkbox" value="export-button"
                                                            <?php if (isset($permissionData) && in_array('export-button', $permissionData)) {
                                                                echo 'checked';
                                                            } ?>>
                                                    </label>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <label class="form-check">
                                                    <span class="form-check-label">Site Column</span>
                                                </label>
                                            </td>
                                            <td>
                                                <div class="custom-control custom-switch">
                                                    <label class="switch">
                                                        <input class="switchInput slave-check" name="sitecolumn"
                                                            data-index="" type="checkbox" value="site-column"
                                                            <?php if (isset($permissionData) && in_array('site-column', $permissionData)) {
                                                                echo 'checked';
                                                            } ?>>
                                                    </label>
                                                </div>
                                            </td>
                                            <td>
                                                <label class="form-check">
                                                    <span class="form-check-label">Sale Order Attachment</span>
                                                </label>
                                            </td>
                                            <td colspan="2">
                                                <div class="custom-control custom-switch">
                                                    <label class="switch">
                                                        <input class="switchInput slave-check" name="saleorderattachment"
                                                            data-index="" type="checkbox" value="sale-order-attachment"
                                                            <?php if (isset($permissionData) && in_array('sale-order-attachment', $permissionData)) {
                                                                echo 'checked';
                                                            } ?>>
                                                    </label>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <label class="form-check">
                                                    <span class="form-check-label">Sale Attachment</span>
                                                </label>
                                            </td>
                                            <td>
                                                <div class="custom-control custom-switch">
                                                    <label class="switch">
                                                        <input class="switchInput slave-check" name="saleattachment"
                                                            data-index="" type="checkbox" value="sale-attachment"
                                                            <?php if (isset($permissionData) && in_array('sale-attachment', $permissionData)) {
                                                                echo 'checked';
                                                            } ?>>
                                                    </label>
                                                </div>
                                            </td>
                                            <td>
                                                <label class="form-check">
                                                    <span class="form-check-label">Compulsory Field</span>
                                                </label>
                                            </td>
                                            <td colspan="2">
                                                <div class="custom-control custom-switch">
                                                    <label class="switch">
                                                        <input class="switchInput slave-check" name="compulsoryfield"
                                                            data-index="" type="checkbox" value="compulsory-field"
                                                            <?php if (isset($permissionData) && in_array('compulsory-field', $permissionData)) {
                                                                echo 'checked';
                                                            } ?>>
                                                    </label>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <label class="form-check">
                                                    <span class="form-check-label">Dashboard Production Sale Order</span>
                                                </label>
                                            </td>
                                            <td>
                                                <div class="custom-control custom-switch">
                                                    <label class="switch">
                                                        <input class="switchInput slave-check"
                                                            name="productionsaleorderbox" data-index="" type="checkbox"
                                                            value="production-sale-order-box" <?php if (isset($permissionData) && in_array('production-sale-order-box', $permissionData)) {
                                                                echo 'checked';
                                                            } ?>>
                                                    </label>
                                                </div>
                                            </td>
                                            <td>
                                                <label class="form-check" for="boqapprovereport">
                                                    <span class="form-check-label">BOQ Approve Report</span>
                                                </label>
                                            </td>
                                            <td colspan="2">
                                                <div class="custom-control custom-switch">
                                                    <label class="switch">
                                                        <input id="boqapprovereport" class="switchInput slave-check"
                                                            name="boqapprovereport" data-index="" type="checkbox"
                                                            value="boq-approve-report" <?php if (isset($permissionData) && in_array('boq-approve-report', $permissionData)) {
                                                                echo 'checked';
                                                            } ?>>
                                                    </label>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <label class="form-check">
                                                    <span class="form-check-label">Approve Boq Updating</span>
                                                </label>
                                            </td>
                                            <td>
                                                <div class="custom-control custom-switch">
                                                    <label class="switch">
                                                        <input class="switchInput slave-check"
                                                            name="boqUpdating" data-index="" type="checkbox"
                                                            value="boq-updating" <?php if (isset($permissionData) && in_array('boq-updating', $permissionData)) {
                                                                echo 'checked';
                                                            } ?>>
                                                    </label>
                                                </div>
                                            </td>
                                            <td>
                                                <label class="form-check" for="prUpdating">
                                                    <span class="form-check-label">Approve PR Updating</span>
                                                </label>
                                            </td>
                                            <td colspan="2">
                                                <div class="custom-control custom-switch">
                                                    <label class="switch">
                                                        <input id="prUpdating" class="switchInput slave-check"
                                                            name="prUpdating" data-index="" type="checkbox"
                                                            value="pr-updating" <?php if (isset($permissionData) && in_array('pr-updating', $permissionData)) {
                                                                echo 'checked';
                                                            } ?>>
                                                    </label>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <label class="form-check">
                                                    <span class="form-check-label">Approve Boq</span>
                                                </label>
                                            </td>
                                            <td>
                                                <div class="custom-control custom-switch">
                                                    <label class="switch">
                                                        <input class="switchInput slave-check"
                                                            name="approveBoq" data-index="" type="checkbox"
                                                            value="approve-boq" <?php if (isset($permissionData) && in_array('approve-boq', $permissionData)) {
                                                                echo 'checked';
                                                            } ?>>
                                                    </label>
                                                </div>
                                            </td>
                                            <td>
                                                <label class="form-check" for="approvePr">
                                                    <span class="form-check-label">Approve PR</span>
                                                </label>
                                            </td>
                                            <td colspan="2">
                                                <div class="custom-control custom-switch">
                                                    <label class="switch">
                                                        <input id="approvePr" class="switchInput slave-check"
                                                            name="approvePr" data-index="" type="checkbox"
                                                            value="approve-pr" <?php if (isset($permissionData) && in_array('approve-pr', $permissionData)) {
                                                                echo 'checked';
                                                            } ?>>
                                                    </label>
                                                </div>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer text-center">
                        <button class="btn btn-danger ms-auto" type="submit">
                            Submit
                        </button>
                        <a class="btn btn-warning me-2" href="{{ route('users.role.index') }}">Back</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('javascript')
    <script>
        setTimeout(function() {
            $(document).ready(function() {
                $(".page-loader").fadeOut();
                const switches = $('.switchInput');
                $(".accordion").on('shown.bs.collapse', function() {
                    switches.each(function() {
                        var isChecked = $(this).is(':checked');
                        $(this).bootstrapSwitch('state', isChecked, true);
                        $(this).parents("td").find(".hiddenValue").val(isChecked ? "1" :
                            "0");
                    });
                });

                switches.bootstrapSwitch({
                    onColor: 'success',
                    offColor: 'danger',
                    onText: 'YES',
                    offText: 'NO'
                }).on('switchChange.bootstrapSwitch', function(event, state) {
                    $(this).parents("td").find(".hiddenValue").val(state ? "1" : "0");
                });

                switches.each(function() {
                    var isChecked = $(this).is(':checked');
                    $(this).bootstrapSwitch('state', isChecked, true);
                    $(this).parents("td").find(".hiddenValue").val(isChecked ? "1" : "0");
                });

                switches.each(function() {
                    var isChecked = $(this).is(':checked');
                    $(this).parents("td").find(".hiddenValue").val(isChecked ? "1" : "0");
                });
                $('.master-check').change(function() {
                    var isChecked = $(this).is(':checked');
                    $("table").find(".switchInput").bootstrapSwitch('state', isChecked).each(
                        function() {
                            var refrence = $(this).parents("td");
                            refrence.find(".hiddenValue").val(isChecked ? "1" : "0");
                        });
                })
            });
        }, 2000);
    </script>
@endpush

<style>
    .symbolcontainer {
        display: flex;
        justify-content: right;
        gap: 20px;
        align-items: right;

        background-color: #f4f4f4;
    }

    .symbol {
        width: 15px;
        /* height: 60px; */
        display: flex;
        justify-content: center;
        align-items: center;
        position: relative;
        border-radius: 50%;
        background-color: #3498db;
        /* Single color for all symbols */
        color: white;
        font-size: 18px;
        font-weight: bold;
        text-align: center;
    }

    .symbol::before {
        content: attr(data-inner);
        /* Displays the number inside */
        width: 30px;
        height: 30px;
        border-radius: 50%;
        position: absolute;
        color: black;
        font-size: 14px;
        display: flex;
        justify-content: center;
        align-items: center;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
    }

    .infocolor::before {
        background-color: {{ $colorsdata['info'] }};
    }

    .mrncolor::before {
        background-color: {{ $colorsdata['mrn'] }};
    }

    .pcolor::before {
        background-color: {{ $colorsdata['payment'] }};
    }

    .ccolor::before {
        background-color: {{ $colorsdata['completed'] }};
    }
</style>
<div class="invoice p-3 mb-3">
    <!-- title row -->
    <div class="row">
        <div class="col-12">
            <h4>
            </h4>
            <div class="symbolcontainer">
                <div class="symbol infocolor" data-inner="1"></div>
                <div class="symbol mrncolor" data-inner="2"></div>
                <div class="symbol pcolor" data-inner="3"></div>
                <div class="symbol ccolor" data-inner="4"></div>
            </div>
        </div>
        <!-- /.col -->
    </div>
    <div class="row invoice-info">
        <div class="col-sm-4 invoice-col">
            To
            <address>
                <strong>{{ $sales->name_1 }}</strong><br>
                <b>Invoice :- {{ $sales->vch_No }}</b><br>
                <b>Challan No :- {{ $sales->ref_no }}</b><br>
                <b>Order No :- {{ $sales?->saleOrder?->vch_No }}</b><br>
                <b>Of1:- {{ $sales?->of1_info }}</b><br>
                <b>Date: {{ date('d/m/Y', strtotime($sales->date)) }}</b><br>
                <b>Transport :- {{ $sales?->transport }}</b><br>
                <b>Vehicle No. :- {{ $sales?->vehicle_no }}</b><br>
            </address>
        </div>
        <div class="col-sm-4 invoice-col">
            <br>
            <b>MRN No. :- {{ $sales?->mrn_no }}</b><br>
            <b>MRN Date. :- {{ $sales?->mrn_date ? date('d/m/Y', strtotime($sales?->mrn_date)) : null }}</b><br>
            <b>Credit Days :- {{ $sales?->credited_days }}</b><br>
            <b>Amount :- {{ $sales?->amount }}</b><br>
            <b>Store Phone No. :- {{ $sales?->store_phone }}</b><br>
            <b>Purchase Phone No. :- {{ $sales?->purchase_phone }}</b><br>
            <b>Lead Delivery Date :-
                {{ $sales?->lead_delivery_date ? date('d/m/Y', strtotime($sales?->lead_delivery_date)) : null }}</b><br><br>
        </div>
        <div class="col-sm-4 invoice-col">
            <br>
            <b>Store Email :-
                @if (!empty($sales?->store_email))
                    <ul style="margin-bottom: 0px; !important;">
                        @foreach (explode(',', $sales?->store_email ?? '') as $email)
                            @if (trim($email) !== '')
                                <li>{{ trim($email) }}</li>
                            @endif
                        @endforeach
                    </ul>
                @endif
            </b><br>
            <b>Store CC Email :-
                @if (!empty($sales?->store_cc_email))
                    <ul style="margin-bottom: 0px; !important;">
                        @foreach (explode(',', $sales?->store_cc_email ?? '') as $email)
                            @if (trim($email) !== '')
                                <li>{{ trim($email) }}</li>
                            @endif
                        @endforeach
                    </ul>
                @endif
            </b><br>
            <b>Purchase Email :-
                @if (!empty($sales?->purchase_email))
                    <ul style="margin-bottom: 0px; !important;">
                        @foreach (explode(',', $sales?->purchase_email ?? '') as $email)
                            @if (trim($email) !== '')
                                <li>{{ trim($email) }}</li>
                            @endif
                        @endforeach
                    </ul>
                @endif
            </b><br>
            <b>Purchase CC Email :-
                @if (!empty($sales?->purchase_cc_email))
                    <ul style="margin-bottom: 0px; !important;">
                        @foreach (explode(',', $sales?->purchase_cc_email ?? '') as $email)
                            @if (trim($email) !== '')
                                <li>{{ trim($email) }}</li>
                            @endif
                        @endforeach
                    </ul>
                @endif
            </b><br>
            <hr style="margin-top: 10px;margin-bottom: 10px">
            <b>Work Category :- {{ $sales?->workCategory?->name }}</b><br>
            <b>Site :- {{ $sales?->site?->name }}</b><br>
            <b>Buyer :- {{ $sales?->buyer?->buyerSingles?->name }}</b><br>
        </div>
    </div>
    <div class="row">
        <div class="col-12 table-responsive">
            <table class="table table-striped">
                <thead>
                    <th>S No</th>
                    <th>Product Name</th>
                    <th>Unit Name</th>
                    <th>Qty</th>
                    <th>Item HSN Code</th>
                    <th>Item Tax Category</th>
                    <th>Price</th>
                    <th>Amount</th>
                    <th>Description</th>
                </thead>
                <tbody>
                    <?php $qtyTotal = 0; ?>
                    <?php $amountTotal = 0; ?>
                    @foreach ($sales->details as $index => $sale)
                    <?php
                    $qtyTotal += $sale->qty;
                    $amountTotal += $sale->amount;
                    ?>
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $sale->item_name }}</td>
                        <td>{{ $sale->unit_name }}</td>
                        <td>{{ $sale->qty }}</td>
                        <td>{{ $sale->item_HSN_code }}</td>
                        <td>{{ $sale->item_tax_category }}</td>
                        <td>{{ $sale->price }}</td>
                        <td>{{ $sale->amount }}</td>
                        <td>{{ $sale->description }}</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="3"></td>
                        <td>{{ $qtyTotal }}</td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td>{{ $amountTotal }}</td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
    <div class="row">
        <div class="col-6">
            @if (empty($action))
            @if (isset($sales->sale_order_id))
            <a class="btn btn-danger invoiceModel text-white m-1" data-id="{{ $sales->sale_order_id }}"
                data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="Edit"
                href="javascript:void(0);" data-original-title="" title="">
                View Order
            </a>
            @endif
            @endif
            <br>
            <hr>
            @if (empty($action))
            @if (isset($sale_returns))
            @foreach ($sale_returns as $sale_return)
            <a class="btn btn-danger saleReturnModel text-white m-1" data-id="{{ $sale_return['id'] }}"
                data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="Edit"
                href="javascript:void(0);" data-original-title="" title="">
                {{ $sale_return['vch_No'] }}
            </a>
            @endforeach
            @endif
            @endif
        </div>
        <div class="col-6">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>BS Name</th>
                            <th>Percent Val</th>
                            <th>Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                        $gstTotal = 0;
                        @endphp
                        @foreach ($sales->billDetails as $mrn)
                        @php
                        $amountTotal += $mrn->amount;
                        $gstTotal += in_array($mrn->bs_name, ['IGST', 'CGST', 'SGST']) ? $mrn->amount : 0;
                        @endphp
                        <tr>
                            <td>{{ $mrn->bs_name }}</td>
                            <td>{{ $mrn->percent_val }}</td>
                            <td>{{ $mrn->amount }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <td>GST Total :- </td>
                            <td>{{ $gstTotal }}</td>
                            <td>{{ $amountTotal }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>

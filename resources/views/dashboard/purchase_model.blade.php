<style>
    .infocolor::before {
        background-color: {{ $colorsdata['info'] }};
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
                <div class="symbol pcolor" data-inner="2"></div>
                <div class="symbol ccolor" data-inner="3"></div>
            </div>
        </div>
        <!-- /.col -->
    </div>
    <div class="row invoice-info">
        <div class="col-sm-4 invoice-col">
            To
            <address>
                <strong>{{ $purchases->name_1 }}</strong><br>
                <b>Invoice :- {{ $purchases->vch_No }}</b><br>
                <b>Date: {{ date('d/m/Y', strtotime($purchases->date)) }}</b><br>
            </address>
        </div>
        <div class="col-sm-4 invoice-col">

        </div>
        <div class="col-sm-4 invoice-col">
            <br>
            <b>MTC No. :- {{ $purchases?->mtc_no }}</b><br>
            <b>Credit Days :- {{ $purchases?->credited_days }}</b><br>
            <b>Amount :- {{ $purchases?->amount }}</b><br>
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
                    @foreach ($purchases->details as $index => $sale)
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
                @if (isset($purchase_returns))
                    @foreach ($purchase_returns as $purchase_return)
                        <a class="btn btn-danger purchaseReturnModel text-white m-1"
                            data-id="{{ $purchase_return['id'] }}" data-bs-toggle="tooltip" data-bs-placement="top"
                            data-bs-original-title="Edit" href="javascript:void(0);" data-original-title=""
                            title="">
                            {{ $purchase_return['vch_No'] }}
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
                        @foreach ($purchases->billDetails as $mrn)
                            @php
                                $amountTotal += $mrn->amount;
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
                            <td></td>
                            <td></td>
                            <td>{{ $amountTotal }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>

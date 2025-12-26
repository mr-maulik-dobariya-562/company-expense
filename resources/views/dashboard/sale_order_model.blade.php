<div class="invoice p-3 mb-3">
    <!-- title row -->
    <div class="row">
        <div class="col-12">
            <h4>
            </h4>
        </div>
    </div>
    <div class="row invoice-info">
        <div class="col-sm-4 invoice-col">
            To
            <address>
                <strong>{{ $saleOrders?->name_1 }}</strong><br>
                <b>PO Number :- {{ $saleOrders?->vch_No }}</b><br>
                <b>Date: {{ date('d/m/Y', strtotime($saleOrders?->date)) }}</b><br>
            </address>
        </div>
        <div class="col-sm-4 invoice-col">

        </div>
        <div class="col-sm-4 invoice-col">
            <b>Work Category :- {{ $saleOrders?->workCategory?->name }}</b><br>
            <b>Site :- {{ $saleOrders?->site?->name }}</b><br>
            <b>Buyer :- {{ $saleOrders?->buyer?->buyerSingles?->name }}</b><br>

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
                    <th>Pending Qty</th>
                    <th>Item HSN Code</th>
                    <th>Item Tax Category</th>
                    <th>Price</th>
                    <th>Amount</th>
                    <th>Description</th>
                </thead>
                <tbody>
                    <?php
                    $qtyTotal = 0;
                    
                    $amountTotal = 0;
                    ?>
                    @foreach ($saleOrders->details as $index => $sale)
                        @php
                            $saleQty = 0;
                            $returnQty = 0;
                        @endphp
                        @foreach ($saleOrders->sale as $saleqty)
                            @foreach ($saleqty->details as $detail)
                                @if ($sale->item_name == $detail->item_name)
                                    @php
                                        $saleQty += $detail->qty;
                                    @endphp
                                @endif
                            @endforeach

                            @foreach ($saleqty->saleReturns as $saleR)
                                @foreach ($saleR->details as $detail)
                                    @if ($sale->item_name == $detail->item_name)
                                        @php
                                            $returnQty += $detail->qty;
                                        @endphp
                                    @endif
                                @endforeach
                            @endforeach
                        @endforeach


                        <?php
                        $qtyTotal += $sale->qty;
                        $amountTotal += $sale->amount;
                        ?>
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $sale->item_name }}</td>
                            <td>{{ $sale->unit_name }}</td>
                            <td>{{ $sale->qty }}</td>
                            <td>{{ (float) $sale->qty - (float) $saleQty + (float) $returnQty }}</td>
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
                @if (isset($saleOrders->sale))
                    @foreach ($saleOrders->sale as $sale)
                        <a class="btn btn-danger invoiceModel text-white m-1" data-id="{{ $sale?->id }}"
                            data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="Edit"
                            href="javascript:void(0);" data-original-title="" title="">
                            {{ $sale->vch_No }}
                        </a>
                    @endforeach
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
                        @foreach ($saleOrders->billDetails as $mrn)
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

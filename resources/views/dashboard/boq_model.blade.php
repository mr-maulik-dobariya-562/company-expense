@csrf
<input type="hidden" id="sale_id" name="id" value="">
<input type="hidden" id="sale_ref" name="ref_no" value="">
<label class="form-label">Remark</label>
<textarea class="form-control" rows="2" name="remark" required>{{ isset($boq->remark) ? $boq?->remark : '' }}</textarea>
<table class="table table-bordered mt-2" id="boqTable">
    <thead>
        <tr>
            <th>Item</th>
            <th>Qty</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        @if (isset($boq->boqDetails))
        @foreach ($boq->boqDetails as $row)
        <tr>
            <td>
                <select name="items[0][item_id]" class="form-control item-select" required>
                    <option value="">Select Item</option>
                    @foreach ($items as $item)
                        <option value="{{ $item->id }}" {{ $item->id == $row->item_id ? 'selected' : '' }}>{{ $item->name }}</option>
                    @endforeach
                </select>
            </td>
            <td><input type="number" name="items[0][qty]" class="form-control" required value="{{ $row?->qty }}"></td>
            <td>
                <button type="button" class="btn btn-sm btn-danger removeRow"><i class="fa fa-times"></i></button>
            </td>
        </tr>
        @endforeach
        @else
        <tr>
            <td>
                <select name="items[0][item_id]" class="form-control item-select" required>
                    <option value="">Select Item</option>
                    @foreach ($items as $item)
                        <option value="{{ $item->id }}">{{ $item->name }}</option>
                    @endforeach
                </select>
            </td>
            <td><input type="number" name="items[0][qty]" class="form-control" required ></td>
            <td>
                <button type="button" class="btn btn-sm btn-danger removeRow"><i class="fa fa-times"></i></button>
            </td>
        </tr>
        @endif
    </tbody>
</table>
@if (!isset($boq->boqDetails))
<button type="button" class="btn btn-sm btn-success" id="addRow">+ Add</button>
@endif
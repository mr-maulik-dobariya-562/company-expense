@extends('Layouts.app')

@section('title', 'Expense Report')

@section('header')
<div class="page-header d-print-none">
  <div class="row g-2 align-items-center">
    <div class="col">
      <div class="page-pretitle">Master</div>
      <h2 class="page-title">Expense Report (Month Wise)</h2>
    </div>
  </div>
</div>
@endsection

@section('content')
<div class="card">
  <div class="card-status-top bg-primary"></div>

  <div class="card-header">
    <div class="row w-100 g-2 align-items-end">
      <div class="col-md-3">
        <label class="form-label mb-1">Month</label>
        <input type="month" id="monthFilter" class="form-control" value="{{ now()->format('Y-m') }}">
      </div>
      <div class="col-md-3">
        <button class="btn btn-primary w-100" id="btnFilter">
          <i class="fas fa-filter me-1"></i> Apply
        </button>
      </div>
      <div class="col-md-6 text-end">
        <span class="text-muted">Only <b>DEBIT</b> entries (created_by wise)</span>
      </div>
    </div>
  </div>

  <div class="card-body">
    <div class="table-responsive">
      <table class="table table-vcenter card-table" id="expense-report-table">
        <thead>
          <tr>
            <th>User</th>
            <th>Total Debit</th>
            <th>Total Paid</th>
            <th>Total Unpaid</th>
            <th style="width:220px;">Action</th>
          </tr>
        </thead>
      </table>
    </div>
  </div>
</div>

{{-- DETAILS MODAL --}}
<div class="modal fade" id="detailsModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-scrollable modal-xl">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Month Expense List</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">
        <div class="table-responsive">
          <table class="table table-vcenter" id="details-table">
            <thead>
              <tr>
                <th>ID</th>
                <th>Amount</th>
                <th>Date</th>
                <th>Description</th>
                <th>Status</th>
                <th>Created At</th>
              </tr>
            </thead>
          </table>
        </div>
      </div>

      <div class="modal-footer">
        <button class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>
@endsection

@push('javascript')
<script>
$(function () {

  const reportTable = $('#expense-report-table').DataTable({
    processing: true,
    serverSide: false,
    ajax: {
      url: "{{ route('master.expense.report.getList') }}",
      type: "POST",
      data: function (d) {
        d._token = "{{ csrf_token() }}";
        d.month = $('#monthFilter').val();
      }
    },
    columns: [
      { data: 'user' },
      { data: 'total_debit' },
      { data: 'total_paid' },
      { data: 'total_unpaid' },
      { data: 'action', orderable: false, searchable: false },
    ]
  });

  $('#btnFilter').on('click', function () {
    reportTable.ajax.reload();
  });

  // details table (modal)
  window.__detailsUserId = null;
  window.__detailsMonth = null;


  

  var detailsTable = null;

  function InitDetailsTable() {

if(detailsTable) return detailsTable.ajax.reload();

    detailsTable = $('#details-table').DataTable({
    processing: true,
    serverSide: false,
    ajax: {
      url: "{{ route('master.expense.report.details') }}",
      type: "POST",
      data: function (d) {
        d._token = "{{ csrf_token() }}";
        d.user_id = window.__detailsUserId;
        d.month = window.__detailsMonth;
      }
    },
    columns: [
      { data: 'id' },
      { data: 'amount' },
      { data: 'date' },
      { data: 'description' },
      { data: 'pay_status', orderable: false, searchable: false },
      { data: 'created_at' },
    ]
  });
  }

  // view
  $(document).on('click', '.view-month', function () {
    window.__detailsUserId = $(this).data('user_id');
    window.__detailsMonth = $('#monthFilter').val();
    $('#detailsModal').modal('show');
    InitDetailsTable();
  });

  // pay month
  $(document).on('click', '.pay-month', function () {
    const userId = $(this).data('user_id');
    const month = $('#monthFilter').val();

    Swal.fire({
      title: 'Pay this month expenses?',
      text: 'All NOT PAID entries will be marked as PAID.',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: 'Yes, Pay'
    }).then((r) => {
      if (!r.isConfirmed) return;

      const http = App.http.jqClient;
      http.post("{{ route('master.expense.report.payMonth') }}", {
        _token: "{{ csrf_token() }}",
        user_id: userId,
        month: month
      }).then(res => {
        if (res.success) {
          sweetAlert('success', res.message);
          reportTable.ajax.reload(); // pay button disappears
          if ($('#detailsModal').hasClass('show')) detailsTable.ajax.reload();
        } else {
          sweetAlert('error', res.message || 'Failed');
        }
      });
    });
  });

});
</script>
@endpush

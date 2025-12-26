@extends('Layouts.app')

@section('title', 'Log Activity')

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
				<h2 class="page-title">
					Log Activity
				</h2>
			</div>
		</div>
	</div>
@endsection
@section('content')
	<div class="row">
		<div class="col-md-12">
			<!-- Filters -->
			<div class="card">
				<div class="card-header">
					<h3 class="card-title">Filters</h3>
				</div>
				<div class="card-body">
					<form id="filter-form">
						<div class="row">
							<div class="col-md-2">
								<label for="from_date" class="form-label">From Date</label>
								<input type="date" name="from_date" id="from_date" class="form-control">
							</div>
							<div class="col-md-2">
								<label for="to_date" class="form-label">To Date</label>
								<input type="date" name="to_date" id="to_date" class="form-control">
							</div>
							<div class="col-md-3">
								<label for="user_id" class="form-label">User</label>
								<select name="user_id" id="user_id" class="form-control select2">
									<option value="">Select User</option>
									@foreach ($users as $user)
										<option value="{{ $user->id }}">{{ $user->name }}</option>
									@endforeach
								</select>
							</div>
							<div class="col-md-2 mt-4">
								<button type="button" class="btn btn-outline-primary float-end"
									id="search">Search</button>
							</div>
						</div>
					</form>
				</div>
			</div>

			<div class="card">
				<div class="card-status-top bg-primary"></div>
				<div class="card-header">
					<h3 class="card-title">Log Activity</h3>
				</div>
				<div class="card-body">
					<div class="row">
						<div class="col-md-12 table-responsive">
							<table class="table card-table table-vcenter datatable" id="log-table">
								<thead>
									<tr>
										<th data-name="id">Serial No </th>
										<th data-name="subject">subject</th>
										<th data-name="agent_view">View Logs</th>
										<th data-name="ip">Ip</th>
										<th data-name="vch_No">Vch No</th>
										<th data-name="created_by">User Name</th>
										<th data-name="created_at">Created At</th>
									</tr>
								</thead>
							</table>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>


		<!-- JSON Modal -->


<div class="modal fade show" id="jsonModal" aria-modal="true" role="dialog">
		<div class="modal-dialog modal-xl" role="document">
			<div class="modal-content">
				<div class="modal-header" style="background-color: #00800040;">
					<h5 class="modal-title"><i class="fas fa-globe"></i>Log activity</h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">×</span>
					</button>
				</div>
				<div class="modal-body">
                <pre id="jsonViewer"></pre>
                </div>
				<div class="modal-footer justify-content-between">
					<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
				</div>
			</div>
		</div>
	</div>

@endsection

@push('javascript')
	<script src="https://cdn.jsdelivr.net/npm/jquery.json-viewer/json-viewer/jquery.json-viewer.js"></script>
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/jquery.json-viewer/json-viewer/jquery.json-viewer.css">

	<script>
		$(document).ready(function() {
			const modal = $("#jsonModal");
			var table = window.table(
				"#log-table",
				"{{ route('log-activity.getList') }}", {
					additionalData: () => {
						return {
							_token: "{{ csrf_token() }}",
							from_date: $("#from_date").val(),
							to_date: $("#to_date").val(),
							user_id: $("#user_id").val(),
						}
					},
				}
				);
			$('#search').click(function() {
				table.ajax.reload()
			});

				// Handle row click event to show JSON in modal
				$('#log-table tbody').on('click', '.view-btn', function() {
					let rowData = table.row($(this).parents("tr")).data();

					// Show JSON in modal
					$('#jsonViewer').jsonViewer(JSON.parse(rowData?.agent));
					$('#jsonModal').modal('show');
				});

				// Close modal button
				$('#closeModal').click(function() {
					$('#jsonModal').modal('hide');
				});

		});
	</script>
@endpush

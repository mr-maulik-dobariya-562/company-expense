<form class="card card-body mb-3" method="GET">
    <div class="filter-bar">
        <div><label class="mb-1">Month</label><input type="month" name="month" value="{{ $month }}" class="form-control"></div>
        <div class="filter-buttons"><button class="btn btn-primary"><i class="fas fa-filter mr-1"></i> Filter</button><a href="{{ $reset }}" class="btn btn-secondary">Reset</a></div>
    </div>
</form>

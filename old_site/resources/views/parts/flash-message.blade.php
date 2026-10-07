{{-- @session("success")
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ $value }}
        <button class="close" data-dismiss="alert" type="button" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
@endsession

@if (session("error"))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        {{ session("error") }}
        <button class="close" data-dismiss="alert" type="button" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
@endif

@if (session("info"))
    <div class="alert alert-info alert-dismissible fade show" role="alert">
        {{ session("info") }}
        <button class="close" data-dismiss="alert" type="button" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
@endif

@if (session("warning"))
    <div class="alert alert-warning alert-dismissible fade show" role="alert">
        {{ session("warning") }}
        <button class="close" data-dismiss="alert" type="button" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
@endif --}}
<style>
    body.swal2-shown>[aria-hidden='true'] {
        transition: 0.1s filter;
        filter: blur(3px);
    }
</style>

@push("javascript")
    <script>
        $(document).ready(function() {
            <?php if (session("success")){ ?>
                sweetAlert('success', '{{ session("success") }}');
            <?php } ?>

            <?php if (session("error")) { ?>
                sweetAlert('error', '{{ session("success") }}');
            <?php } ?>

            <?php if (session("info")) { ?>
                sweetAlert('info', '{{ session("success") }}');
            <?php } ?>

            <?php if (session("warning")){ ?>
                sweetAlert('warning', '{{ session("success") }}');
            <?php } ?>
        });
    </script>
@endpush

@props(["messages"])

@if ($messages)
    @foreach ((array) $messages as $message)
        <span class="invalid-feedback"> <strong>{{ $message }}</strong></span>
    @endforeach
@endif
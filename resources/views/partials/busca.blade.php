{{-- Campo de busca reaproveitável: @include('partials.busca', ['placeholder' => '...']) --}}
<form method="GET" class="row g-2 mb-3">
    <div class="col-sm-6 col-lg-4">
        <input type="search" name="busca" value="{{ request('busca') }}" class="form-control" placeholder="{{ $placeholder ?? 'Buscar...' }}">
    </div>
    <div class="col-auto">
        <button type="submit" class="btn btn-outline-primary">Buscar</button>
        @if (request('busca'))
            <a href="{{ url()->current() }}" class="btn btn-outline-secondary">Limpar</a>
        @endif
    </div>
</form>

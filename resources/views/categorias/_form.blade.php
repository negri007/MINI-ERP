{{-- Campos do formulário, usados tanto no create quanto no edit --}}

{{-- Campo: Nome --}}
<div class="mb-3">
    <label for="nome" class="form-label">Nome <span class="text-danger" aria-hidden="true">*</span></label>
    <input type="text" name="nome" id="nome" aria-required="true" class="form-control @error('nome') is-invalid @enderror" @error('nome') aria-invalid="true" aria-describedby="nome-erro" @enderror value="{{ old('nome', $categoria->nome) }}">
    @error('nome')
        <div class="invalid-feedback" id="nome-erro">{{ $message }}</div>
    @enderror
</div>

{{-- Campo: Descrição --}}
<div class="mb-3">
    <label for="descricao" class="form-label">Descrição</label>
    <textarea name="descricao" id="descricao" rows="3" class="form-control @error('descricao') is-invalid @enderror" @error('descricao') aria-invalid="true" aria-describedby="descricao-erro" @enderror>{{ old('descricao', $categoria->descricao) }}</textarea>
    @error('descricao')
        <div class="invalid-feedback" id="descricao-erro">{{ $message }}</div>
    @enderror
</div>

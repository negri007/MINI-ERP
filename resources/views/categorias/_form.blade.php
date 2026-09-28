{{-- Campos do formulário, usados tanto no create quanto no edit --}}

{{-- Campo: Nome --}}
<div class="mb-3">
    <label for="nome" class="form-label">Nome <span class="text-danger">*</span></label>
    <input type="text" name="nome" id="nome" class="form-control @error('nome') is-invalid @enderror" value="{{ old('nome', $categoria->nome) }}">
    @error('nome')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

{{-- Campo: Descrição --}}
<div class="mb-3">
    <label for="descricao" class="form-label">Descrição</label>
    <textarea name="descricao" id="descricao" rows="3" class="form-control @error('descricao') is-invalid @enderror">{{ old('descricao', $categoria->descricao) }}</textarea>
    @error('descricao')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

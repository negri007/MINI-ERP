{{-- Campos do formulário, usados tanto no create quanto no edit --}}

{{-- Campo: Nome --}}
<div class="mb-3">
    <label for="nome" class="form-label">Nome <span class="text-danger">*</span></label>
    <input type="text" name="nome" id="nome" class="form-control @error('nome') is-invalid @enderror" value="{{ old('nome', $cliente->nome) }}">
    @error('nome')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

{{-- Campo: CPF/CNPJ --}}
<div class="mb-3">
    <div class="rotulo-com-ajuda">
        <label for="cpf_cnpj" class="form-label">CPF/CNPJ</label>
        @include('partials.ajuda-campo', ['id' => 'ajuda-cpf-cnpj', 'campo' => 'o campo CPF/CNPJ'])
    </div>
    <input type="text" name="cpf_cnpj" id="cpf_cnpj" class="form-control @error('cpf_cnpj') is-invalid @enderror" value="{{ old('cpf_cnpj', $cliente->cpf_cnpj) }}" data-mascara="cpfcnpj" inputmode="numeric" aria-describedby="ajuda-cpf-cnpj">
    <p class="ajuda-texto" id="ajuda-cpf-cnpj" hidden>Pode digitar só os números; a máscara entra sozinha. Não é obrigatório.</p>
    @error('cpf_cnpj')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

{{-- Campo: Telefone --}}
<div class="mb-3">
    <label for="telefone" class="form-label">Telefone</label>
    <input type="text" name="telefone" id="telefone" class="form-control @error('telefone') is-invalid @enderror" value="{{ old('telefone', $cliente->telefone) }}" data-mascara="telefone">
    @error('telefone')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

{{-- Campo: E-mail --}}
<div class="mb-3">
    <label for="email" class="form-label">E-mail</label>
    <input type="email" name="email" id="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $cliente->email) }}">
    @error('email')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

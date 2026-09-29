{{--
    Modal "Nova categoria" do formulário de produto: cadastra sem sair da tela
    (o que já foi digitado no produto não se perde). Usa a mesma validação do
    cadastro normal (CategoriaRequest). Fica FORA do <form> do produto.
    Uso: @include('partials.modal-categoria') nas telas de novo/editar produto.
--}}
<div class="modal fade" id="modalCategoria" tabindex="-1" aria-labelledby="modalCategoriaTitulo" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form class="modal-content" id="formCategoriaRapida" action="{{ route('categorias.rapida') }}" method="POST" novalidate data-cadastro-rapido>
            @csrf
            <div class="modal-header">
                <h2 class="modal-title h5" id="modalCategoriaTitulo">Nova categoria</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-danger" data-erro-geral hidden></div>
                <label for="rapida_nome" class="form-label">Nome <span class="text-danger">*</span></label>
                <input type="text" name="nome" id="rapida_nome" class="form-control" autocomplete="off" placeholder="Ex.: Bebidas"
                       aria-describedby="rapida_nome_erro" required>
                <div class="invalid-feedback" id="rapida_nome_erro" data-erro="nome"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                <button type="submit" class="btn btn-primary">Cadastrar categoria</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script src="{{ asset('js/cadastro-rapido.js') }}"></script>
<script>
    // Categoria cadastrada no modal: entra na lista do produto e já fica escolhida
    document.getElementById('formCategoriaRapida').addEventListener('cadastro-rapido:salvo', (e) => {
        const campo = document.getElementById('categoria_id');
        campo.add(new Option(e.detail.nome, e.detail.id, true, true));
        campo.classList.remove('is-invalid');
        document.getElementById('semCategoria')?.remove();
        campo.focus();
    });
</script>
@endpush

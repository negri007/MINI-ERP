// Cadastro rápido dentro de um modal (ex.: "+ Novo cliente" na Nova venda).
// Uso no HTML: <form data-cadastro-rapido action="..."> dentro de um .modal do Bootstrap.
// Envia o formulário sem recarregar a página: a tela de trás (ex.: itens da venda) não se perde.
// Quando salva, dispara o evento "cadastro-rapido:salvo" no <form> com os dados devolvidos.
// A validação de verdade continua no servidor (Form Request); aqui só mostramos os erros.
document.querySelectorAll('form[data-cadastro-rapido]').forEach((form) => {
    const modalEl = form.closest('.modal');
    const botao = form.querySelector('[type="submit"]');
    const textoBotao = botao.textContent;
    const erroGeral = form.querySelector('[data-erro-geral]');

    // Mostra ou limpa o erro de um campo (texto abaixo dele + aria-invalid para o leitor de tela)
    function marcarErro(nome, mensagem) {
        const campo = form.elements[nome];
        const caixa = form.querySelector(`[data-erro="${nome}"]`);
        if (!campo || !caixa) return;
        campo.classList.toggle('is-invalid', Boolean(mensagem));
        campo.toggleAttribute('aria-invalid', Boolean(mensagem));
        caixa.textContent = mensagem || '';
    }

    function limparErros() {
        form.querySelectorAll('[data-erro]').forEach((caixa) => marcarErro(caixa.dataset.erro, ''));
        erroGeral.hidden = true;
    }

    // O erro some assim que a pessoa começa a corrigir o campo
    form.addEventListener('input', (e) => { if (e.target.name) marcarErro(e.target.name, ''); });

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        limparErros();
        botao.disabled = true;
        botao.textContent = 'Salvando…';

        try {
            const resposta = await fetch(form.action, {
                method: 'POST',
                body: new FormData(form), // já leva o _token do @csrf
                headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            });
            const dados = await resposta.json().catch(() => ({}));

            if (resposta.status === 422) {
                // Erros de validação: um por campo; o foco vai para o primeiro campo com erro
                const nomes = Object.keys(dados.errors || {});
                nomes.forEach((nome) => marcarErro(nome, dados.errors[nome][0]));
                form.elements[nomes[0]]?.focus();
                return;
            }
            if (!resposta.ok) throw new Error(`HTTP ${resposta.status}`);

            form.dispatchEvent(new CustomEvent('cadastro-rapido:salvo', { detail: dados }));
            form.reset();
            bootstrap.Modal.getOrCreateInstance(modalEl).hide();
        } catch {
            erroGeral.textContent = 'Não foi possível salvar agora. Confira a conexão e tente de novo.';
            erroGeral.hidden = false;
        } finally {
            botao.disabled = false;
            botao.textContent = textoBotao;
        }
    });

    // Ao abrir, o foco vai para o primeiro campo; ao fechar, os erros antigos somem
    modalEl.addEventListener('shown.bs.modal', () => form.querySelector('input:not([type="hidden"])')?.focus());
    modalEl.addEventListener('hidden.bs.modal', limparErros);
});

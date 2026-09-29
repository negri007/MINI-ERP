// Cadastro rápido dentro de um modal (ex.: "+ Novo cliente" na Nova venda).
// Uso no HTML: <form data-cadastro-rapido action="..."> dentro de um .modal do Bootstrap.
// Envia o formulário sem recarregar a página: a tela de trás (ex.: itens da venda) não se perde.
// Quando salva, dispara o evento "cadastro-rapido:salvo" no <form> com os dados devolvidos.
// A validação de verdade continua no servidor (Form Request); aqui só mostramos os erros.
// Se o servidor responder 409 com "parecidos" (ex.: cliente com nome parecido), o modal
// pergunta "Já existe X. É ele?": [Usar X] escolhe o existente (evento com existente: true)
// e [Cadastrar novo mesmo assim] envia de novo com confirmar_novo=1.
document.querySelectorAll('form[data-cadastro-rapido]').forEach((form) => {
    const modalEl = form.closest('.modal');
    const botao = form.querySelector('[type="submit"]');
    const textoBotao = botao.textContent;
    const erroGeral = form.querySelector('[data-erro-geral]');

    // Caixa da pergunta "É ele?" (criada aqui, dentro do corpo do modal)
    const pergunta = document.createElement('div');
    pergunta.className = 'parecidos';
    pergunta.hidden = true;
    pergunta.setAttribute('aria-live', 'polite');
    form.querySelector('.modal-body').prepend(pergunta);
    let confirmarNovo = false;

    function esconderPergunta() {
        pergunta.hidden = true;
        pergunta.innerHTML = '';
        confirmarNovo = false;
    }

    function perguntar(parecidos) {
        pergunta.innerHTML = '';
        const texto = document.createElement('p');
        texto.textContent = parecidos.length === 1
            ? `Já existe ${parecidos[0].nome}. É ele?`
            : 'Já existem clientes com nome parecido. É algum deles?';
        pergunta.appendChild(texto);

        const botoes = document.createElement('div');
        botoes.className = 'parecidos-botoes';
        parecidos.forEach((c) => {
            const usar = document.createElement('button');
            usar.type = 'button';
            usar.className = 'btn btn-sm btn-outline-primary';
            usar.textContent = `Usar ${c.nome}${parecidos.length > 1 && c.documento ? ` (${c.documento})` : ''}`;
            usar.addEventListener('click', () => {
                form.dispatchEvent(new CustomEvent('cadastro-rapido:salvo', { detail: { ...c, existente: true } }));
                bootstrap.Modal.getOrCreateInstance(modalEl).hide();
            });
            botoes.appendChild(usar);
        });
        const novo = document.createElement('button');
        novo.type = 'button';
        novo.className = 'btn btn-sm btn-secondary';
        novo.textContent = 'Cadastrar novo mesmo assim';
        novo.addEventListener('click', () => { confirmarNovo = true; form.requestSubmit(); });
        botoes.appendChild(novo);

        pergunta.appendChild(botoes);
        pergunta.hidden = false;
        botoes.querySelector('button').focus();
    }

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
    // Mudou o nome depois da pergunta "É ele?": a pergunta some (vale para o nome novo)
    form.addEventListener('input', (e) => {
        if (e.target.name) marcarErro(e.target.name, '');
        if (e.target.name === 'nome') esconderPergunta();
    });

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        limparErros();
        botao.disabled = true;
        botao.textContent = 'Salvando…';

        try {
            const resposta = await fetch(form.action, {
                method: 'POST',
                body: (() => {
                    const corpo = new FormData(form); // já leva o _token do @csrf
                    if (confirmarNovo) corpo.append('confirmar_novo', '1');
                    return corpo;
                })(),
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
            if (resposta.status === 409 && dados.parecidos?.length) {
                perguntar(dados.parecidos);
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

    // Ao abrir, o foco vai para o primeiro campo.
    // Ao fechar, o formulário é limpo: da próxima vez o modal abre vazio.
    modalEl.addEventListener('shown.bs.modal', () => form.querySelector('input:not([type="hidden"])')?.focus());
    modalEl.addEventListener('hidden.bs.modal', () => { limparErros(); esconderPergunta(); form.reset(); });
});

// Campo "Cliente" da Nova venda: busca por nome ou CPF/CNPJ enquanto digita.
// Segue o padrão "combobox" do ARIA: setas escolhem, Enter confirma, Esc fecha,
// e o leitor de tela ouve quantos clientes apareceram e qual foi escolhido.
// O <select name="cliente_id"> continua na página (escondido) e é ele que envia o valor:
// se este arquivo não carregar, o formulário funciona com o <select> comum.
(() => {
    const select = document.getElementById('cliente_id');
    const combo = document.getElementById('comboCliente');
    if (!select || !combo) return;

    const campo = document.getElementById('clienteBusca');
    const lista = document.getElementById('clienteOpcoes');
    const vazio = document.getElementById('clienteVazio');
    const erro = document.getElementById('clienteErro');
    const aviso = document.getElementById('clienteAviso');
    const modalEl = document.getElementById('modalCliente');
    const formRapido = document.getElementById('formClienteRapido');

    let opcoes = [];
    let ativo = -1;
    let temporizador = null;
    let pedido = 0;

    // Troca o <select> pela busca; o rótulo "Cliente" passa a apontar para o campo de busca
    select.hidden = true;
    combo.hidden = false;
    document.querySelector('label[for="cliente_id"]').htmlFor = 'clienteBusca';

    const normalizar = (texto) => texto.normalize('NFD').replace(/[̀-ͯ]/g, '').toLowerCase();
    const textoDoCliente = (c) => (c.documento ? `${c.nome} · ${c.documento}` : c.nome);

    // Volta da validação com um cliente já escolhido: mostra o nome no campo
    if (select.value) {
        const opcao = select.selectedOptions[0];
        campo.value = textoDoCliente({ nome: opcao.dataset.nome, documento: opcao.dataset.documento });
    }

    // Reserva: se a busca no servidor falhar, procura nas opções do <select>
    function buscarNaPagina(termo) {
        const alvo = normalizar(termo);
        const digitos = termo.replace(/\D/g, '');
        return [...select.options]
            .filter((o) => o.value)
            .map((o) => ({
                id: Number(o.value),
                nome: o.dataset.nome,
                documento: o.dataset.documento || null,
                consumidor_final: o.dataset.consumidorFinal === '1',
            }))
            .filter((c) => !termo
                || normalizar(c.nome).includes(alvo)
                || (digitos.length >= 3 && (c.documento || '').replace(/\D/g, '').includes(digitos)))
            .slice(0, 10);
    }

    async function buscar(termo) {
        const meuPedido = ++pedido;
        let resultado;
        try {
            const resposta = await fetch(`${combo.dataset.url}?q=${encodeURIComponent(termo)}`, { headers: { Accept: 'application/json' } });
            if (!resposta.ok) throw new Error(`HTTP ${resposta.status}`);
            resultado = await resposta.json();
        } catch {
            resultado = buscarNaPagina(termo);
        }
        if (meuPedido !== pedido) return; // já chegou uma busca mais nova
        opcoes = resultado;
        ativo = opcoes.length ? 0 : -1;
        desenhar(termo);
    }

    function desenhar(termo) {
        lista.innerHTML = '';
        opcoes.forEach((c, i) => {
            const item = document.createElement('li');
            item.id = `cliente-opcao-${c.id}`;
            item.setAttribute('role', 'option');
            item.setAttribute('aria-selected', String(i === ativo));
            item.className = i === ativo ? 'ativo' : '';

            const nome = document.createElement('span');
            nome.textContent = c.nome;
            const detalhe = document.createElement('small');
            detalhe.textContent = c.consumidor_final ? 'venda sem identificar o cliente' : (c.documento || '');
            item.append(nome, detalhe);

            // mousedown com preventDefault: o campo não perde o foco antes do clique
            item.addEventListener('mousedown', (e) => e.preventDefault());
            item.addEventListener('click', () => escolher(c));
            lista.appendChild(item);
        });

        const abriu = opcoes.length > 0;
        lista.hidden = !abriu;
        campo.setAttribute('aria-expanded', String(abriu));
        atualizarAtivo();

        // Nada encontrado: oferece cadastrar com o nome já preenchido
        vazio.hidden = abriu || !termo;
        if (!abriu && termo) {
            vazio.querySelector('span').textContent = `Nenhum cliente com "${termo}".`;
            vazio.querySelector('button').textContent = `Cadastrar "${termo}"`;
        }
        aviso.textContent = abriu
            ? `${opcoes.length} ${opcoes.length === 1 ? 'cliente encontrado' : 'clientes encontrados'}. Use as setas para escolher.`
            : (termo ? 'Nenhum cliente encontrado. Use o botão Cadastrar.' : '');
    }

    function atualizarAtivo() {
        [...lista.children].forEach((item, i) => {
            item.classList.toggle('ativo', i === ativo);
            item.setAttribute('aria-selected', String(i === ativo));
        });
        const atual = lista.children[ativo];
        if (atual) {
            campo.setAttribute('aria-activedescendant', atual.id);
            atual.scrollIntoView({ block: 'nearest' });
        } else {
            campo.removeAttribute('aria-activedescendant');
        }
    }

    function fechar() {
        lista.hidden = true;
        vazio.hidden = true;
        campo.setAttribute('aria-expanded', 'false');
        campo.removeAttribute('aria-activedescendant');
    }

    function mostrarErro(mensagem) {
        campo.classList.toggle('is-invalid', Boolean(mensagem));
        campo.toggleAttribute('aria-invalid', Boolean(mensagem));
        erro.textContent = mensagem;
    }

    // Escolhe o cliente: grava no <select> (que é o que vai para o servidor)
    function escolher(c) {
        let opcao = select.querySelector(`option[value="${c.id}"]`);
        if (!opcao) {
            // cliente acabou de ser cadastrado: entra também no <select>
            opcao = new Option(textoDoCliente(c), c.id);
            opcao.dataset.nome = c.nome;
            opcao.dataset.documento = c.documento || '';
            select.add(opcao);
        }
        select.value = String(c.id);
        campo.value = textoDoCliente(c);
        mostrarErro('');
        fechar();
        aviso.textContent = `Cliente escolhido: ${c.nome}.`;
    }

    // Digitar apaga a escolha anterior e busca de novo (espera 200 ms entre as teclas)
    campo.addEventListener('input', () => {
        select.value = '';
        mostrarErro('');
        clearTimeout(temporizador);
        temporizador = setTimeout(() => buscar(campo.value.trim()), 200);
    });

    // Ao entrar no campo vazio, já mostra a lista (com o Consumidor final primeiro)
    campo.addEventListener('focus', () => { if (!select.value) buscar(campo.value.trim()); });

    campo.addEventListener('keydown', (e) => {
        const aberto = !lista.hidden;
        if (e.key === 'ArrowDown') {
            e.preventDefault();
            if (!aberto) { buscar(campo.value.trim()); return; }
            ativo = Math.min(ativo + 1, opcoes.length - 1);
            atualizarAtivo();
        } else if (e.key === 'ArrowUp' && aberto) {
            e.preventDefault();
            ativo = Math.max(ativo - 1, 0);
            atualizarAtivo();
        } else if (e.key === 'Enter' && aberto && opcoes[ativo]) {
            e.preventDefault(); // Enter escolhe o cliente em vez de enviar a venda
            escolher(opcoes[ativo]);
        } else if (e.key === 'Escape' && aberto) {
            e.preventDefault();
            fechar();
        }
    });

    // Ao sair do campo com texto que não virou cliente, explica o que fazer
    campo.addEventListener('blur', () => {
        setTimeout(() => {
            if (combo.contains(document.activeElement)) return; // foi para o botão "Cadastrar"
            fechar();
            if (campo.value.trim() && !select.value) {
                mostrarErro('Escolha um cliente da lista ou cadastre em "+ Novo cliente".');
            }
        }, 120);
    });

    // "Cadastrar "fulano"": abre o modal (o nome digitado já vai preenchido)
    vazio.querySelector('button').addEventListener('click', () => {
        bootstrap.Modal.getOrCreateInstance(modalEl).show(document.getElementById('btnNovoCliente'));
    });
    modalEl.addEventListener('show.bs.modal', () => {
        const nome = formRapido.elements.nome;
        if (!nome.value && !select.value) nome.value = campo.value.trim();
    });

    // Ao fechar o modal, se o foco ficou perdido, volta para o campo Cliente
    modalEl.addEventListener('hidden.bs.modal', () => {
        setTimeout(() => { if (document.activeElement === document.body) campo.focus(); }, 0);
    });

    // Erro que veio do servidor (ex.: venda enviada sem cliente)
    if (campo.classList.contains('is-invalid')) campo.setAttribute('aria-invalid', 'true');

    // Cliente cadastrado no modal: já fica escolhido na venda
    formRapido.addEventListener('cadastro-rapido:salvo', (e) => {
        escolher(e.detail);
        aviso.textContent = `Cliente ${e.detail.nome} cadastrado e escolhido.`;
    });
})();

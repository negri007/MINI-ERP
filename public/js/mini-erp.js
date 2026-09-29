// Scripts gerais do Mini ERP (carregados em todas as páginas pelo layout)

// ---------- Máscaras de digitação ----------
// Uso no HTML: <input data-mascara="cpfcnpj">, "cnpj" ou "telefone"
const mascaras = {
    cpf: (d) => d.slice(0, 11)
        .replace(/(\d{3})(\d)/, '$1.$2')
        .replace(/(\d{3})(\d)/, '$1.$2')
        .replace(/(\d{3})(\d{1,2})$/, '$1-$2'),
    cnpj: (d) => d.slice(0, 14)
        .replace(/(\d{2})(\d)/, '$1.$2')
        .replace(/(\d{3})(\d)/, '$1.$2')
        .replace(/(\d{3})(\d)/, '$1/$2')
        .replace(/(\d{4})(\d{1,2})$/, '$1-$2'),
    cpfcnpj: (d) => (d.length <= 11 ? mascaras.cpf(d) : mascaras.cnpj(d)),
    telefone: (d) => d.slice(0, 11)
        .replace(/(\d{2})(\d)/, '($1) $2')
        .replace(/(\d{4,5})(\d{4})$/, '$1-$2'),
};

document.querySelectorAll('[data-mascara]').forEach((campo) => {
    const aplicar = () => {
        const digitos = campo.value.replace(/\D/g, '');
        campo.value = mascaras[campo.dataset.mascara](digitos);
    };
    campo.addEventListener('input', aplicar);
    if (campo.value) aplicar();
});

// ---------- Confirmação com modal ----------
// Uso no HTML: <form data-confirmar="Texto da pergunta"> ... </form>
const modalEl = document.getElementById('modalConfirmar');
if (modalEl) {
    const modal = new bootstrap.Modal(modalEl);
    let formularioPendente = null;

    // "Delegação": um único ouvinte no documento, que vale também para as
    // listas recarregadas pela busca instantânea
    document.addEventListener('submit', (evento) => {
        const form = evento.target.closest('form[data-confirmar]');
        if (!form) return;
        evento.preventDefault();
        formularioPendente = form;
        document.getElementById('modalConfirmarTexto').textContent = form.dataset.confirmar;
        modal.show();
    });

    document.getElementById('modalConfirmarBotao').addEventListener('click', () => {
        if (formularioPendente) {
            formularioPendente.submit(); // submit() direto não dispara o evento de novo
        }
    });
}

// ---------- Balcão rápido (Ctrl + K) ----------
// Uma caixa de busca que leva a qualquer tela ou ação digitando.
const balcao = document.getElementById('balcaoRapido');
if (balcao) {
    const busca = document.getElementById('brBusca');
    const lista = document.getElementById('brLista');
    let itens = [];
    let posicao = 0;

    // Tira acentos e deixa minúsculo: "Relatório" -> "relatorio"
    const normalizar = (texto) => texto.normalize('NFD').replace(/[̀-ͯ]/g, '').toLowerCase();

    // Monta a lista conforme o que foi digitado
    function montar() {
        const termo = busca.value.trim();
        const alvo = normalizar(termo);

        // Todas as palavras digitadas precisam aparecer no título
        itens = window.COMANDOS
            .filter(([, titulo]) => alvo.split(/\s+/).every((p) => normalizar(titulo).includes(p)))
            .map(([grupo, titulo, url, icone]) => ({ grupo, titulo, url, icone }));

        // Com texto digitado, oferece buscar esse texto nos cadastros
        if (termo) {
            Object.entries(window.ROTAS_BUSCA).forEach(([nome, url]) => {
                itens.push({ grupo: 'Buscar', titulo: `“${termo}” em ${nome}`, url: `${url}?busca=${encodeURIComponent(termo)}`, icone: '?' });
            });
        }

        posicao = 0;
        desenhar();
    }

    const status = document.getElementById('brStatus');
    let focoAntes = null; // quem tinha o foco antes de abrir (recebe o foco de volta ao fechar)

    // Desenha os itens agrupados, destacando o escolhido.
    // Para o leitor de tela: cada item é uma "opção" (role="option") e o campo aponta
    // para a escolhida com aria-activedescendant, então as setas anunciam o item.
    function desenhar() {
        lista.innerHTML = '';
        if (!itens.length) {
            lista.innerHTML = '<li class="br-vazio" role="presentation">Nada encontrado.</li>';
            busca.removeAttribute('aria-activedescendant');
            return;
        }
        let grupoAtual = null;
        itens.forEach((item, i) => {
            if (item.grupo !== grupoAtual) {
                grupoAtual = item.grupo;
                const titulo = document.createElement('li');
                titulo.className = 'br-grupo';
                titulo.setAttribute('role', 'presentation');
                titulo.textContent = grupoAtual;
                lista.appendChild(titulo);
            }
            const li = document.createElement('li');
            li.id = `br-opcao-${i}`;
            li.setAttribute('role', 'option');
            li.setAttribute('aria-selected', String(i === posicao));
            const link = document.createElement('a');
            link.href = item.url;
            link.tabIndex = -1; // o foco fica sempre no campo; o link é só para o clique do mouse
            link.className = i === posicao ? 'foco' : '';
            link.innerHTML = '<span class="icone" aria-hidden="true"></span><span class="texto"></span><span class="dica" aria-hidden="true">Enter</span>';
            link.querySelector('.icone').textContent = item.icone;
            link.querySelector('.texto').textContent = item.titulo;
            link.querySelector('.dica').style.visibility = i === posicao ? 'visible' : 'hidden';
            link.addEventListener('mousemove', () => { if (posicao !== i) { posicao = i; desenhar(); } });
            li.appendChild(link);
            lista.appendChild(li);
        });
        busca.setAttribute('aria-activedescendant', `br-opcao-${posicao}`);
        lista.querySelector('a.foco')?.scrollIntoView({ block: 'nearest' });
    }

    // Avisa quantos resultados há (espera um pouco para não falar a cada letra)
    let esperaStatus;
    function anunciar() {
        clearTimeout(esperaStatus);
        esperaStatus = setTimeout(() => {
            status.textContent = itens.length ? `${itens.length} resultados. Use as setas para escolher.` : 'Nada encontrado.';
        }, 400);
    }

    function abrir() {
        focoAntes = document.activeElement;
        balcao.hidden = false;
        busca.value = '';
        montar();
        busca.focus();
    }

    function fechar() {
        balcao.hidden = true;
        focoAntes?.focus?.();
    }

    document.querySelectorAll('[data-abrir-rapido]').forEach((b) => b.addEventListener('click', abrir));
    balcao.addEventListener('click', (e) => { if (e.target === balcao) fechar(); });
    busca.addEventListener('input', () => { montar(); anunciar(); });

    // Setas escolhem, Enter abre, Esc fecha
    busca.addEventListener('keydown', (e) => {
        if (e.key === 'ArrowDown') { posicao = Math.min(posicao + 1, itens.length - 1); desenhar(); e.preventDefault(); }
        if (e.key === 'ArrowUp') { posicao = Math.max(posicao - 1, 0); desenhar(); e.preventDefault(); }
        if (e.key === 'Enter' && itens[posicao]) { window.location = itens[posicao].url; }
    });

    // Dentro da caixa: Esc fecha e o Tab não sai para a página que está atrás
    balcao.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') { e.preventDefault(); fechar(); }
        if (e.key === 'Tab') { e.preventDefault(); busca.focus(); }
    });

    // Atalho global: Ctrl + K (ou Cmd + K no Mac).
    // Não usamos atalho de uma tecla só (como "/"): ele dispara sem querer
    // para quem usa comando de voz ou teclado adaptado (WCAG 2.1.4).
    document.addEventListener('keydown', (e) => {
        if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'k') { e.preventDefault(); balcao.hidden ? abrir() : fechar(); }
    });
}

// ---------- Menu lateral no celular ----------
// O botão ☰ abre o menu; clicar fora dele ou apertar Esc fecha.
// aria-expanded diz ao leitor de tela se o menu está aberto.
const lateral = document.getElementById('lateral');
const abrirMenu = document.getElementById('abrirMenu');
if (lateral && abrirMenu) {
    const menuCelular = (abrir, devolverFoco = false) => {
        lateral.classList.toggle('aberta', abrir);
        abrirMenu.setAttribute('aria-expanded', String(abrir));
        if (abrir) lateral.querySelector('.menu a')?.focus(); // teclado já cai no primeiro item
        if (!abrir && devolverFoco) abrirMenu.focus();
    };
    abrirMenu.addEventListener('click', (e) => { e.stopPropagation(); menuCelular(!lateral.classList.contains('aberta')); });
    document.addEventListener('click', (e) => {
        if (lateral.classList.contains('aberta') && !lateral.contains(e.target)) menuCelular(false);
    });
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && lateral.classList.contains('aberta')) menuCelular(false, true);
    });
}

// ---------- Seções do menu (abrir/fechar) e menu recolhido ----------
// As escolhas ficam salvas no navegador (localStorage) para a próxima página.
const guardado = {
    ler(chave, padrao) { try { return JSON.parse(localStorage.getItem(chave)) ?? padrao; } catch { return padrao; } },
    gravar(chave, valor) { try { localStorage.setItem(chave, JSON.stringify(valor)); } catch { /* navegador sem armazenamento: só não lembra */ } },
};

const fechadas = guardado.ler('menu-secoes-fechadas', []);
document.querySelectorAll('.secao').forEach((secao) => {
    const nome = secao.dataset.secao;
    const botao = secao.querySelector('.secao-titulo');

    // Aplica o estado salvo, mas nunca esconde a seção da página aberta
    const fechar = (sim) => {
        secao.classList.toggle('fechada', sim);
        botao.setAttribute('aria-expanded', String(!sim));
    };
    fechar(fechadas.includes(nome) && !secao.classList.contains('tem-ativo'));

    botao.addEventListener('click', () => {
        const agoraFechada = !secao.classList.contains('fechada');
        fechar(agoraFechada);
        const lista = guardado.ler('menu-secoes-fechadas', []).filter((n) => n !== nome);
        if (agoraFechada) lista.push(nome);
        guardado.gravar('menu-secoes-fechadas', lista);
    });
});

const recolherMenu = document.getElementById('recolherMenu');
if (recolherMenu) {
    const aplicar = (compacto) => {
        document.documentElement.classList.toggle('menu-compacto', compacto);
        recolherMenu.title = compacto ? 'Expandir menu' : 'Recolher menu';
    };
    aplicar(guardado.ler('menu-compacto', false));
    recolherMenu.addEventListener('click', () => {
        const compacto = !document.documentElement.classList.contains('menu-compacto');
        aplicar(compacto);
        guardado.gravar('menu-compacto', compacto);
    });
}


// ---------- Listas: busca instantânea, filtros e ordenação sem recarregar ----------
// A página busca a mesma URL com os novos filtros e troca só os pedaços marcados
// com data-atualiza="..." (a lista, as pílulas, o resumo). O campo de busca fica
// intacto, então o cursor não sai do lugar enquanto você digita.
async function carregarLista(url) {
    const conteudo = document.querySelector('[data-lista-conteudo]');
    conteudo?.classList.add('carregando');
    try {
        const resposta = await fetch(url, { headers: { 'X-Requested-With': 'fetch' } });
        const nova = new DOMParser().parseFromString(await resposta.text(), 'text/html');
        document.querySelectorAll('[data-atualiza]').forEach((pedaco) => {
            const substituto = nova.querySelector(`[data-atualiza="${pedaco.dataset.atualiza}"]`);
            if (substituto) pedaco.replaceWith(substituto);
        });
        history.replaceState(null, '', url);
    } catch {
        window.location = url; // se algo der errado, carrega a página normalmente
    } finally {
        document.querySelector('[data-lista-conteudo]')?.classList.remove('carregando');
    }
}

// Digitar na busca: espera 300 ms sem digitar e então filtra
document.querySelectorAll('form[data-busca-viva]').forEach((form) => {
    let espera;
    const filtrar = () => {
        const params = new URLSearchParams(new FormData(form));
        if (!params.get('busca')) params.delete('busca');
        carregarLista(`${location.pathname}?${params}`);
    };
    form.addEventListener('input', () => { clearTimeout(espera); espera = setTimeout(filtrar, 300); });
    form.addEventListener('submit', (e) => { e.preventDefault(); clearTimeout(espera); filtrar(); });
});

// Pílulas de filtro, cabeçalhos que ordenam e paginação: carregam só a lista
document.addEventListener('click', (e) => {
    const link = e.target.closest('a[data-link-lista], [data-lista-conteudo] .pagination a');
    if (!link || e.ctrlKey || e.metaKey || e.shiftKey) return;
    e.preventDefault();
    carregarLista(link.href);
});

// ---------- Linhas que abrem ao clicar (detalhes logo abaixo) ----------
// Com o mouse, clicar em qualquer parte da linha abre. Pelo teclado, quem abre é o
// botão da seta (›): um <button> de verdade, que o leitor de tela anuncia como
// "botão, recolhido/expandido" (aria-expanded) e que já responde a Enter e Espaço.
function alternarLinha(linha) {
    const detalhe = linha.nextElementSibling;
    if (!detalhe?.classList.contains('detalhe')) return;
    const abrir = !linha.classList.contains('aberta');
    linha.classList.toggle('aberta', abrir);
    detalhe.classList.toggle('aberto', abrir);
    linha.querySelector('.seta-abrir')?.setAttribute('aria-expanded', String(abrir));
}
document.addEventListener('click', (e) => {
    const linha = e.target.closest('tr[data-expande]');
    if (!linha) return;
    // O botão da seta abre/fecha; os outros botões e links da linha fazem só o trabalho deles
    if (e.target.closest('.seta-abrir')) { alternarLinha(linha); return; }
    if (e.target.closest('a, button, form, input, select, label')) return;
    alternarLinha(linha);
});

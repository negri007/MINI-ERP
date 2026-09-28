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

    document.querySelectorAll('form[data-confirmar]').forEach((form) => {
        form.addEventListener('submit', (evento) => {
            evento.preventDefault();
            formularioPendente = form;
            document.getElementById('modalConfirmarTexto').textContent = form.dataset.confirmar;
            modal.show();
        });
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
    let focoAnterior = null; // quem tinha o foco antes de abrir, para devolver ao fechar

    // No Mac o atalho é Cmd (⌘), não Ctrl
    if (/Mac|iPhone|iPad/.test(navigator.platform)) {
        document.querySelectorAll('[data-tecla-ctrl]').forEach((k) => { k.textContent = '⌘'; });
    }

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

    // Desenha os itens agrupados
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
            li.setAttribute('role', 'presentation');
            const link = document.createElement('a');
            link.href = item.url;
            link.id = `br-item-${i}`;
            link.tabIndex = -1; // o foco fica na caixa de busca; as setas escolhem
            link.setAttribute('role', 'option');
            link.innerHTML = '<span class="icone" aria-hidden="true"></span><span class="texto"></span><span class="dica" aria-hidden="true">Enter</span>';
            link.querySelector('.icone').textContent = item.icone;
            link.querySelector('.texto').textContent = item.titulo;
            link.addEventListener('mousemove', () => { if (posicao !== i) escolher(i); });
            li.appendChild(link);
            lista.appendChild(li);
        });
        escolher(posicao);
    }

    // Destaca o item escolhido (sem redesenhar a lista)
    function escolher(i) {
        if (!itens.length) return;
        posicao = (i + itens.length) % itens.length; // passa do fim volta ao começo
        lista.querySelectorAll('[role="option"]').forEach((link, n) => {
            const ativo = n === posicao;
            link.classList.toggle('foco', ativo);
            link.setAttribute('aria-selected', ativo);
            link.querySelector('.dica').style.visibility = ativo ? 'visible' : 'hidden';
        });
        const atual = document.getElementById(`br-item-${posicao}`);
        busca.setAttribute('aria-activedescendant', atual.id);
        atual.scrollIntoView({ block: 'nearest' });
    }

    function abrir() {
        focoAnterior = document.activeElement;
        balcao.hidden = false;
        document.body.style.overflow = 'hidden'; // a página de trás não rola
        busca.value = '';
        montar();
        busca.focus();
    }

    function fechar() {
        balcao.hidden = true;
        document.body.style.overflow = '';
        focoAnterior?.focus?.();
    }

    document.querySelectorAll('[data-abrir-rapido]').forEach((b) => b.addEventListener('click', abrir));
    balcao.addEventListener('click', (e) => { if (e.target === balcao) fechar(); });
    busca.addEventListener('input', montar);

    // Setas (ou Tab) escolhem, Enter abre, Esc fecha
    busca.addEventListener('keydown', (e) => {
        const descer = e.key === 'ArrowDown' || (e.key === 'Tab' && !e.shiftKey);
        const subir = e.key === 'ArrowUp' || (e.key === 'Tab' && e.shiftKey);
        if (descer || subir) { e.preventDefault(); escolher(posicao + (descer ? 1 : -1)); }
        if (e.key === 'Enter' && itens[posicao]) { e.preventDefault(); window.location = itens[posicao].url; }
        if (e.key === 'Escape') { e.preventDefault(); fechar(); }
    });

    // Atalhos globais: Ctrl + K (ou Cmd + K no Mac) e "/" fora de campos de texto
    document.addEventListener('keydown', (e) => {
        const ativo = document.activeElement;
        const digitando = ['INPUT', 'TEXTAREA', 'SELECT'].includes(ativo.tagName) || ativo.isContentEditable;
        const modalAberto = document.querySelector('.modal.show');
        if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'k') { e.preventDefault(); balcao.hidden ? abrir() : fechar(); }
        if (e.key === '/' && !digitando && !modalAberto && balcao.hidden) { e.preventDefault(); abrir(); }
    });
}

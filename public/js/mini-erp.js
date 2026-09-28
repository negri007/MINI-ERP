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

    // Desenha os itens agrupados, destacando o escolhido
    function desenhar() {
        lista.innerHTML = '';
        if (!itens.length) {
            lista.innerHTML = '<li class="br-vazio">Nada encontrado.</li>';
            return;
        }
        let grupoAtual = null;
        itens.forEach((item, i) => {
            if (item.grupo !== grupoAtual) {
                grupoAtual = item.grupo;
                const titulo = document.createElement('li');
                titulo.className = 'br-grupo';
                titulo.textContent = grupoAtual;
                lista.appendChild(titulo);
            }
            const li = document.createElement('li');
            const link = document.createElement('a');
            link.href = item.url;
            link.className = i === posicao ? 'foco' : '';
            link.innerHTML = '<span class="icone"></span><span class="texto"></span><span class="dica">Enter</span>';
            link.querySelector('.icone').textContent = item.icone;
            link.querySelector('.texto').textContent = item.titulo;
            link.querySelector('.dica').style.visibility = i === posicao ? 'visible' : 'hidden';
            link.addEventListener('mousemove', () => { if (posicao !== i) { posicao = i; desenhar(); } });
            li.appendChild(link);
            lista.appendChild(li);
        });
        lista.querySelector('a.foco')?.scrollIntoView({ block: 'nearest' });
    }

    function abrir() {
        balcao.hidden = false;
        busca.value = '';
        montar();
        busca.focus();
    }

    function fechar() {
        balcao.hidden = true;
    }

    document.querySelectorAll('[data-abrir-rapido]').forEach((b) => b.addEventListener('click', abrir));
    balcao.addEventListener('click', (e) => { if (e.target === balcao) fechar(); });
    busca.addEventListener('input', montar);

    // Setas escolhem, Enter abre, Esc fecha
    busca.addEventListener('keydown', (e) => {
        if (e.key === 'ArrowDown') { posicao = Math.min(posicao + 1, itens.length - 1); desenhar(); e.preventDefault(); }
        if (e.key === 'ArrowUp') { posicao = Math.max(posicao - 1, 0); desenhar(); e.preventDefault(); }
        if (e.key === 'Enter' && itens[posicao]) { window.location = itens[posicao].url; }
        if (e.key === 'Escape') fechar();
    });

    // Atalhos globais: Ctrl + K (ou Cmd + K no Mac) e "/" fora de campos de texto
    document.addEventListener('keydown', (e) => {
        const digitando = ['INPUT', 'TEXTAREA', 'SELECT'].includes(document.activeElement.tagName);
        if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'k') { e.preventDefault(); balcao.hidden ? abrir() : fechar(); }
        if (e.key === '/' && !digitando && balcao.hidden) { e.preventDefault(); abrir(); }
    });
}

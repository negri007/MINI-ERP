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

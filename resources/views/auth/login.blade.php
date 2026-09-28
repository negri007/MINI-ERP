<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Entrar - Mini ERP</title>
    <link href="{{ asset('vendor/bootstrap/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('css/balcao.css') }}" rel="stylesheet">
    <style>
        /* Tela de login: à esquerda o convite, à direita a "ficha de ponto" */
        .abertura { min-height: 100vh; display: grid; grid-template-columns: 1.1fr 1fr; align-items: center; gap: 3rem; padding: 3rem clamp(1.5rem, 6vw, 6rem); }
        .abertura h1 { font-size: clamp(3rem, 7vw, 5.6rem); line-height: .95; letter-spacing: -.03em; margin: 1.2rem 0; }
        .abertura h1 em { color: var(--carimbo); font-weight: 400; }
        .abertura p.lead { max-width: 30rem; color: var(--tinta-2); }
        .ficha { position: relative; background: var(--folha); border: 1px solid var(--linha); padding: 2.2rem 2.2rem 2.4rem; box-shadow: 0 30px 60px -30px rgba(60, 40, 10, .6); transform: rotate(1.2deg); }
        .ficha::before { content: ''; position: absolute; top: -14px; left: 50%; width: 90px; height: 26px; transform: translateX(-50%) rotate(-2deg); background: rgba(212, 154, 42, .55); }
        .ficha .relogio { font-family: var(--fonte-numero); font-size: 2.2rem; font-weight: 500; letter-spacing: -.02em; }
        @media (max-width: 900px) { .abertura { grid-template-columns: 1fr; } .ficha { transform: none; } }
    </style>
</head>
<body>
    <main class="abertura">

        {{-- Lado esquerdo: nome do sistema e chamada --}}
        <section>
            <a class="marca" href="{{ url('/') }}"><span class="selo">ME</span><span>Mini <em>ERP</em></span></a>
            <h1 class="titulo">{{ now()->hour < 12 ? 'Bom dia' : (now()->hour < 18 ? 'Boa tarde' : 'Boa noite') }}.<br>Vamos <em>abrir</em><br>o caixa?</h1>
            <p class="lead">Cadastros, vendas, estoque e relatórios da sua loja, numa mesa só.</p>
        </section>

        {{-- Lado direito: formulário de login como uma ficha de ponto --}}
        <section class="ficha">
            <div class="d-flex justify-content-between align-items-start mb-4">
                <div>
                    <div class="rotulo">Ficha de entrada</div>
                    <div class="relogio" id="relogio">{{ now()->format('H:i') }}</div>
                </div>
                <div class="rotulo text-end">{{ now()->locale('pt_BR')->translatedFormat('d \\d\\e F') }}</div>
            </div>

            <form action="{{ url('/login') }}" method="POST">
                @csrf

                {{-- Campo: E-mail --}}
                <div class="mb-3">
                    <label for="email" class="form-label">E-mail</label>
                    <input type="email" name="email" id="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" autofocus>
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Campo: Senha --}}
                <div class="mb-3">
                    <label for="password" class="form-label">Senha</label>
                    <input type="password" name="password" id="password" class="form-control @error('password') is-invalid @enderror">
                    @error('password')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-check mb-4">
                    <input type="checkbox" name="lembrar" id="lembrar" class="form-check-input" value="1">
                    <label for="lembrar" class="form-check-label">Manter conectado</label>
                </div>

                <button type="submit" class="btn btn-primary w-100">Entrar</button>
            </form>

            <p class="rotulo mt-4 mb-0">Teste: admin@minierp.com / admin123</p>
        </section>
    </main>

    <script>
        // Relógio da ficha, atualizado a cada segundo
        const relogio = document.getElementById('relogio');
        setInterval(() => {
            relogio.textContent = new Date().toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' });
        }, 1000);
    </script>
</body>
</html>

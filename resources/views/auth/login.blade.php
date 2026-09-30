<!DOCTYPE html>
<html lang="pt-BR" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Entrar - Mini ERP</title>
    <link href="{{ asset('vendor/bootstrap/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('css/tema.css') }}" rel="stylesheet">
    <style>
        /* Tela de login: painel verde-mata à esquerda, formulário à direita */
        .entrada { min-height: 100vh; display: grid; grid-template-columns: 1.1fr 1fr; }
        .vitrine {
            position: relative;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            gap: 2rem;
            padding: 3rem clamp(2rem, 5vw, 4.5rem);
            overflow: hidden;
            background:
                radial-gradient(90% 70% at 10% 0%, rgba(245, 197, 24, .22), transparent 60%),
                radial-gradient(80% 80% at 100% 100%, rgba(67, 148, 232, .2), transparent 60%),
                linear-gradient(160deg, #125237, #0b2a1e);
        }
        .vitrine::after { content: ''; position: absolute; left: 0; right: 0; bottom: 0; height: 6px; background: linear-gradient(90deg, var(--sol) 0 62%, var(--ceu) 62% 84%, #e8efe9 84%); }
        .vitrine h1 { font-size: clamp(2.6rem, 5vw, 4.2rem); line-height: 1.02; font-weight: 700; letter-spacing: -.03em; margin: 0; }
        .vitrine h1 span { color: var(--sol); }
        .vitrine p { color: #b7d6c6; max-width: 28rem; font-size: 1.05rem; margin-top: 1.2rem; }
        .vitrine .numeros { display: flex; gap: 2.5rem; color: #b7d6c6; font-size: .85rem; }
        .vitrine .numeros strong { display: block; color: #fff; font-family: var(--fonte-titulo); font-size: 1.5rem; }
        .formulario { display: flex; align-items: center; justify-content: center; padding: 2rem; }
        .formulario .caixa { width: 100%; max-width: 380px; }
        /* Celular: o painel verde vira uma faixa curta (logo + saudação) e o formulário aparece logo abaixo,
           sem precisar rolar a tela */
        @media (max-width: 900px) {
            .entrada { grid-template-columns: 1fr; grid-template-rows: auto 1fr; }
            .vitrine { gap: 1.25rem; padding: 1.5rem 1.25rem 1.75rem; }
            .vitrine h1 { font-size: 2rem; }
            .vitrine p, .vitrine .numeros { display: none; }
            .formulario { align-items: flex-start; padding: 2rem 1.25rem; }
        }
    </style>
</head>
<body>
    <main class="entrada">

        {{-- Lado esquerdo: apresentação do sistema --}}
        <section class="vitrine">
            <div class="logo p-0"><span class="marca">ME</span> Mini ERP</div>
            <div>
                <h1>{{ now()->hour < 12 ? 'Bom dia' : (now()->hour < 18 ? 'Boa tarde' : 'Boa noite') }}.<br>Vamos <span>abrir</span><br>o caixa?</h1>
                <p>Cadastros, vendas, estoque e relatórios da sua loja em um só lugar.</p>
            </div>
            <div class="numeros">
                <div><strong>4</strong>cadastros</div>
                <div><strong>Ctrl K</strong>busca rápida</div>
                <div><strong>100%</strong>offline</div>
            </div>
        </section>

        {{-- Lado direito: formulário de login --}}
        <section class="formulario">
            <div class="caixa">
                <h2 class="h3 mb-1">Entrar</h2>
                <p class="text-muted mb-4">Use seu e-mail e senha para acessar.</p>

                <form action="{{ url('/login') }}" method="POST">
                    @csrf

                    {{-- Campo: E-mail --}}
                    <div class="mb-3">
                        <label for="email" class="form-label">E-mail</label>
                        <input type="email" name="email" id="email" autocomplete="email" class="form-control @error('email') is-invalid @enderror" @error('email') aria-invalid="true" aria-describedby="email-erro" @enderror value="{{ old('email') }}" autofocus>
                        @error('email')
                            <div class="invalid-feedback" id="email-erro">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Campo: Senha --}}
                    <div class="mb-3">
                        <label for="password" class="form-label">Senha</label>
                        <input type="password" name="password" id="password" autocomplete="current-password" class="form-control @error('password') is-invalid @enderror" @error('password') aria-invalid="true" aria-describedby="password-erro" @enderror>
                        @error('password')
                            <div class="invalid-feedback" id="password-erro">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-check mb-4">
                        <input type="checkbox" name="lembrar" id="lembrar" class="form-check-input" value="1">
                        <label for="lembrar" class="form-check-label">Manter conectado</label>
                    </div>

                    <button type="submit" class="btn btn-primary w-100">Entrar</button>
                </form>

                <p class="text-muted small mt-4 mb-0">Usuário de teste: admin@minierp.com / admin123</p>
            </div>
        </section>
    </main>
</body>
</html>

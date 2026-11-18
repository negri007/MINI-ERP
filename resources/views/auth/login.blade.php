<!DOCTYPE html>
<html lang="pt-BR" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Entrar - Mini ERP</title>
    <link href="{{ asset('vendor/bootstrap/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('css/tema.css') }}" rel="stylesheet">
    <link href="{{ asset('css/entrada.css') }}" rel="stylesheet">
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
                <a href="{{ route('sobre') }}" class="voltar">← Conhecer o Mini ERP</a>
                <h2 class="h3 mb-1">Entrar</h2>
                <p class="text-muted mb-4">Use seu e-mail e senha para acessar.</p>

                {{-- Aviso depois de criar a conta --}}
                @if (session('aviso'))
                    <div class="alert alert-success" role="status">{{ session('aviso') }}</div>
                @endif

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

                <p class="troca mb-0">Ainda não tem conta? <a href="{{ route('cadastro') }}">Criar conta</a></p>
            </div>
        </section>
    </main>
</body>
</html>

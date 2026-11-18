<!DOCTYPE html>
<html lang="pt-BR" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Criar conta - Mini ERP</title>
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
                <p>Peça seu acesso: cadastros, vendas, estoque e relatórios da loja em um só lugar.</p>
            </div>
            <div class="numeros">
                <div><strong>4</strong>cadastros</div>
                <div><strong>Ctrl K</strong>busca rápida</div>
                <div><strong>100%</strong>offline</div>
            </div>
        </section>

        {{-- Lado direito: formulário de cadastro --}}
        <section class="formulario">
            <div class="caixa">
                <a href="{{ route('sobre') }}" class="voltar">← Conhecer o Mini ERP</a>
                <h2 class="h3 mb-1">Criar conta</h2>
                <p class="text-muted mb-4">Depois de enviar, um usuário do sistema aprova seu acesso. Aí é só entrar.</p>

                <form action="{{ url('/cadastro') }}" method="POST">
                    @csrf

                    @foreach ([
                        ['name', 'Nome', 'text', 'name'],
                        ['email', 'E-mail', 'email', 'email'],
                        ['password', 'Senha (mínimo 8 caracteres)', 'password', 'new-password'],
                        ['password_confirmation', 'Repita a senha', 'password', 'new-password'],
                    ] as [$campo, $rotulo, $tipo, $auto])
                        <div class="mb-3">
                            <label for="{{ $campo }}" class="form-label">{{ $rotulo }}</label>
                            <input type="{{ $tipo }}" name="{{ $campo }}" id="{{ $campo }}" autocomplete="{{ $auto }}" required
                                   class="form-control @error($campo) is-invalid @enderror" @error($campo) aria-invalid="true" aria-describedby="{{ $campo }}-erro" @enderror
                                   @if ($tipo !== 'password') value="{{ old($campo) }}" @endif @if ($loop->first) autofocus @endif>
                            @error($campo)
                                <div class="invalid-feedback" id="{{ $campo }}-erro">{{ $message }}</div>
                            @enderror
                        </div>
                    @endforeach

                    <button type="submit" class="btn btn-primary w-100 mt-2">Enviar cadastro</button>
                </form>

                <p class="troca mb-0">Já tem conta? <a href="{{ route('login') }}">Entrar</a></p>
            </div>
        </section>
    </main>
</body>
</html>

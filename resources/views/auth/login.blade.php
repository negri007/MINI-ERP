<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Entrar - Mini ERP</title>
    <link href="{{ asset('vendor/bootstrap/bootstrap.min.css') }}" rel="stylesheet">
</head>
<body class="bg-dark d-flex align-items-center min-vh-100">

    {{-- Tela de login (não usa o layout, pois não tem menu) --}}
    <div class="container" style="max-width: 400px">
        <div class="card shadow">
            <div class="card-body p-4">
                <h1 class="h3 text-center mb-4">Mini ERP</h1>

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

                    <div class="form-check mb-3">
                        <input type="checkbox" name="lembrar" id="lembrar" class="form-check-input" value="1">
                        <label for="lembrar" class="form-check-label">Manter conectado</label>
                    </div>

                    <button type="submit" class="btn btn-primary w-100">Entrar</button>
                </form>
            </div>
        </div>
        <p class="text-center text-white-50 small mt-3">Usuário de teste: admin@minierp.com / admin123</p>
    </div>
</body>
</html>

<!DOCTYPE html>
<html lang="pt-BR" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Mini ERP - estoque e vendas para o balcão</title>
    <meta name="description" content="Controle de estoque e vendas para mercadinho, loja pequena e MEI.">
    {{-- Tudo salvo dentro do projeto: a página funciona sem internet --}}
    <link href="{{ asset('vendor/bootstrap/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('css/tema.css') }}" rel="stylesheet">
    <link href="{{ asset('css/apresentacao.css') }}" rel="stylesheet">
</head>
<body class="apresentacao">
    @php
        // Logado: o botão leva ao Dashboard; visitante: Entrar e Criar conta
        $logado = auth()->check();
    @endphp

    {{-- Topo: marca e acesso --}}
    <header class="ap-topo">
        <a href="{{ route('sobre') }}" class="logo p-0"><span class="marca">ME</span> Mini ERP</a>
        <nav class="ap-acesso" aria-label="Acesso">
            @if ($logado)
                <a href="{{ route('dashboard') }}" class="btn btn-primary">Ir para o Dashboard</a>
            @else
                <a href="{{ route('cadastro') }}" class="btn btn-secondary">Criar conta</a>
                <a href="{{ route('login') }}" class="btn btn-primary">Entrar</a>
            @endif
        </nav>
    </header>

    {{-- Em tela de computador tudo cabe sem rolar: abertura em cima e três blocos embaixo --}}
    <main class="ap-pagina">
        {{-- Abertura: o que é, em uma frase, e uma amostra real do sistema --}}
        <section class="ap-abertura" aria-labelledby="ap-titulo">
            <div>
                <h1 id="ap-titulo">Controle de estoque e vendas para quem toca o balcão.</h1>
                <p class="ap-lead">Cadastre o que vende, registre as vendas e veja o mês num painel. O estoque baixa sozinho.</p>
                <p class="ap-chamada">Pronto para abrir o caixa?</p>
                <div class="ap-botoes">
                    @if ($logado)
                        <a href="{{ route('dashboard') }}" class="btn btn-primary btn-lg">Ir para o Dashboard</a>
                    @else
                        <a href="{{ route('login') }}" class="btn btn-primary btn-lg">Entrar</a>
                        <a href="{{ route('cadastro') }}" class="btn btn-outline-primary btn-lg">Criar conta</a>
                    @endif
                </div>
                @unless ($logado)
                    <p class="ap-nota">A conta nova é liberada por um usuário do sistema.</p>
                @endunless
            </div>

            {{-- Amostra feita com os mesmos componentes do sistema (não é imagem) --}}
            <figure class="ap-amostra">
                <div class="card">
                    <div class="card-header">Para repor <span class="ap-contagem">{{ $exemplos->count() }} produtos</span></div>
                    <div class="card-body p-0">
                        @foreach ($exemplos as $produto)
                            <div class="estoque-item">
                                <div class="nome text-truncate">{{ $produto->nome }} <small>{{ $produto->categoria }}</small></div>
                                @include('partials.medidor', ['produto' => $produto])
                                <span class="btn btn-sm btn-outline-primary" aria-hidden="true">Repor</span>
                            </div>
                        @endforeach
                    </div>
                </div>
                <figcaption>Tela do sistema com dados de exemplo.</figcaption>
            </figure>
        </section>

        {{-- Três destaques curtos (o resto está no sistema, com dica em cada tela) --}}
        <div class="ap-blocos">
            <section class="ap-bloco" aria-labelledby="ap-estoque">
                <h2 id="ap-estoque">Estoque que avisa</h2>
                <p>Quando um produto chega no mínimo, ele aparece em "Para repor".</p>
            </section>
            <section class="ap-bloco" aria-labelledby="ap-venda">
                <h2 id="ap-venda">Venda e devolução</h2>
                <p>A venda baixa o estoque. A devolução volta ao estoque, vira perda ou vai ao fornecedor.</p>
            </section>
            <section class="ap-bloco" aria-labelledby="ap-painel">
                <h2 id="ap-painel">Painel do mês</h2>
                <p>Faturamento, lucro e os últimos 14 dias num gráfico.</p>
            </section>
        </div>

        {{-- Rodapé: para quem é e limites do sistema, em uma linha discreta --}}
        <p class="ap-rodape">
            Para mercadinho, loja pequena e MEI. Hoje todo usuário aprovado acessa tudo. Separar dono e caixa está nos planos.
            Não emite nota fiscal eletrônica. Use o emissor que seu contador indicar.
        </p>
    </main>
</body>
</html>

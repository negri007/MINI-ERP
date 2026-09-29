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
        // Logado: os botões levam ao Dashboard; visitante: ao login
        $logado = auth()->check();
        $destino = $logado ? route('dashboard') : route('login');
        $rotuloBotao = $logado ? 'Ir para o Dashboard' : 'Entrar';
    @endphp

    <header class="ap-topo">
        <a href="{{ route('sobre') }}" class="logo p-0"><span class="marca">ME</span> Mini ERP</a>
        <a href="{{ $destino }}" class="btn btn-outline-primary">{{ $rotuloBotao }}</a>
    </header>

    <main>
        {{-- Abertura: o que é, em uma frase, e uma amostra real do sistema --}}
        <section class="ap-abertura" aria-labelledby="ap-titulo">
            <div>
                <h1 id="ap-titulo">Controle de estoque e vendas para quem toca o balcão.</h1>
                <p class="ap-lead">Cadastre o que vende, registre as vendas e veja o mês num painel. O estoque baixa sozinho.</p>
                <a href="{{ $destino }}" class="btn btn-primary btn-lg">{{ $rotuloBotao }}</a>
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

        <div class="ap-corpo">
            <section aria-labelledby="ap-quem">
                <h2 id="ap-quem">Para quem é</h2>
                <p>
                    Mercadinho, loja pequena e MEI: quem vende no balcão e precisa saber o que tem, o que vendeu e o que acabou.
                    O Sebrae aponta a falta de planejamento entre os sinais de alerta para pequenos negócios
                    (<a href="https://agenciasebrae.com.br/?p=41140" rel="noopener" target="_blank">Agência Sebrae, 07/07/2026<span class="visually-hidden">, abre em nova aba</span></a>).
                </p>
            </section>

            <section aria-labelledby="ap-resolve">
                <h2 id="ap-resolve">O que ele resolve</h2>
                <dl class="ap-lista">
                    <div>
                        <dt>Estoque que avisa</dt>
                        <dd>Quando um produto chega no mínimo, ele aparece em "Para repor".</dd>
                    </div>
                    <div>
                        <dt>Venda que baixa o estoque</dt>
                        <dd>Registrou a venda, o estoque desce. Cancelou, ele volta.</dd>
                    </div>
                    <div>
                        <dt>Painel do mês</dt>
                        <dd>Faturamento, ticket médio e os últimos 14 dias num gráfico.</dd>
                    </div>
                    <div>
                        <dt>Cliente na hora</dt>
                        <dd>Cadastre o cliente sem sair da venda, ou venda como Consumidor final.</dd>
                    </div>
                    <div>
                        <dt>Ajuda em cada tela</dt>
                        <dd>Uma dica curta abaixo do título explica para que a tela serve.</dd>
                    </div>
                </dl>
            </section>

            <section aria-labelledby="ap-como">
                <h2 id="ap-como">Como funciona</h2>
                <ol class="ap-passos">
                    <li>
                        <h3>Cadastre os produtos</h3>
                        <p>Nome, preço, categoria e o estoque mínimo que acende o alerta.</p>
                    </li>
                    <li>
                        <h3>Registre as vendas</h3>
                        <p>Escolha o cliente e os produtos. O total e o estoque se ajustam sozinhos.</p>
                    </li>
                    <li>
                        <h3>Acompanhe o painel</h3>
                        <p>O Dashboard mostra o mês, o que vendeu e o que precisa repor.</p>
                    </li>
                </ol>
            </section>

            <section aria-labelledby="ap-usa">
                <h2 id="ap-usa">Quem usa</h2>
                <p>Hoje todo usuário acessa tudo. Separar dono e caixa está nos planos.</p>
            </section>

            <section aria-labelledby="ap-nao">
                <h2 id="ap-nao">O que ele não faz</h2>
                <p>Não emite nota fiscal eletrônica. Use o emissor que seu contador indicar.</p>
            </section>

            <section class="ap-fim" aria-labelledby="ap-fim">
                <h2 id="ap-fim">Pronto para abrir o caixa?</h2>
                <a href="{{ $destino }}" class="btn btn-outline-primary btn-lg">{{ $rotuloBotao }}</a>
            </section>
        </div>
    </main>
</body>
</html>

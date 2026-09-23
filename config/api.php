<?php

return [
    /*
    |--------------------------------------------------------------------------
    | API Configuration
    |--------------------------------------------------------------------------
    |
    | Configuração para integração com n8n e outras plataformas externas
    |
    */

    'key' => env('API_KEY', 'default-api-key'),

    'timeout' => env('API_TIMEOUT', 30),

    /*
    |--------------------------------------------------------------------------
    | Endpoints Configuration
    |--------------------------------------------------------------------------
    |
    | Define quais endpoints estão disponíveis e sua documentação
    |
    */

    'endpoints' => [
        'contratos' => [
            'index' => '/api/contratos',
            'show' => '/api/contratos/{id}',
            'by-cliente' => '/api/contratos/cliente/{clienteId}',
            'search' => '/api/contratos/search/{termo}',
        ],
        'clientes' => [
            'index' => '/api/clientes',
            'show' => '/api/clientes/{id}',
            'by-telefone' => '/api/clientes/telefone/{telefone}',
            'search' => '/api/clientes/search/{termo}',
        ],
        'pagamentos' => [
            'by-contrato' => '/api/pagamentos/{contratoId}',
            'store' => '/api/pagamentos',
        ],
    ],
];

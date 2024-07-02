# api-php

API de Cadastro e Controle de Usuários

Esta API permite o cadastro, controle de usuários e seus endereços. Ela utiliza JWT para autenticação e oferece operações CRUD para usuários e endereços.

## Como Rodar o Projeto

1. Clone o repositório.
2. Configure as variáveis de ambiente (.env).
3. Inicie o Docker:
    ```bash
    docker compose up -d
    ```
4. Instale as dependências:
    ```bash
    composer install
    ```

## Rotas

## Tecnologias Utilizadas

- PHP 8
- MySQL
- JWT para autenticação (expiração de 1 hora)
- Docker para gerenciamento de ambiente

### Ferramentas e Bibliotecas

- Pré-commits e GitHub Actions configurados para verificação ao realizar push para `develop` ou `master`.
- `fast-route` para gerenciamento de rotas.
- `php-di` para injeção de dependência.

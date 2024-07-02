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
- Documentação da API no Postman: [Link para a documentação do Postman](https://documenter.getpostman.com/view/19438074/2sA3dxCWGF)
- 
  (Nota: A documentação está disponível no Postman; atualmente, não está disponível em formato Swagger.)
  
## Tecnologias Utilizadas

- PHP 8
- MySQL
- JWT para autenticação (expiração de 1 hora)
- Docker para gerenciamento de ambiente

### Ferramentas e Bibliotecas

- Pré-commits e GitHub Actions configurados para verificação ao realizar push para `develop` ou `master`.
- `fast-route` para gerenciamento de rotas.
- `php-di` para injeção de dependência.

- Kanban utilizado para separação das tarefas: [Link para o Kanban](https://github.com/users/lucasframoon/projects/2)
- Qualquer dúvida pode me enviar uma mensagem em lucasframoon@gmail.com.

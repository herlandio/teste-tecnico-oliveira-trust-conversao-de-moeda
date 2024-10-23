## [Teste técnico oliveira trust] Conversão entre moedas

### Descrição do Projeto

Este projeto implementa um sistema de conversão de moedas utilizando a API [AwesomeAPI](https://economia.awesomeapi.com.br/json/last/BRL-USD). O sistema permite conversões entre reais (BRL) e dólares americanos (USD), com a aplicação de diferentes taxas de acordo com o método de pagamento e o valor da transação.

### Taxas Aplicáveis

- Pagamentos via Boleto: 1,45% sobre o valor da transação.
- Pagamentos com Cartão de Crédito: 7,63% sobre o valor da transação.
- Conversão de Valores:
    - Para valores abaixo de R$ 3.000, uma taxa de 2% é aplicada.
    - Para valores iguais ou superiores a R$ 3.000, a taxa é reduzida para 1%.
- Limites de Cotação: Todas as cotações devem estar entre R$ 1.000 e R$ 100.000.
- Histórico de Cotações: Cada cotação realizada gera um histórico por usuário, armazenado no localStorage.

### Tecnologias utilizadas
- Backend: Laravel 11
- Linguagem de Programação: PHP 8.2
- Frontend: Bootstrap 4.6, HTML, CSS, JavaScript

### Instruções para Execução do Projeto
1. Clone o Repositório:

    ```
    git clone https://github.com/herlandio/teste-tecnico-oliveira-trust-conversao-de-moeda.git
    ```
2. Para subir a aplicação:

    ```
    docker compose up
    ```
3. Acesse a aplicação:

    ```
    http://127.0.0.1:8000/conversion
    ```
### Testes

```
docker-compose exec app php artisan test 
```
### Conclusão
Este sistema proporciona uma interface intuitiva para a conversão de moedas, com transparência nas taxas e armazenamento eficiente do histórico de transações. O projeto é facilmente extensível e pode ser adaptado para incluir novas funcionalidades conforme necessário.
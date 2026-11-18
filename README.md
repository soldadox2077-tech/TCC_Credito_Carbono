# 🌱 Simulador de Crédito de Carbono

Projeto de **TCC** do curso Técnico em Desenvolvimento de Sistemas (Colégio Pedro II).

Sistema web que permite ao usuário simular suas emissões de CO₂ com base em hábitos de transporte, consumo de energia e outros padrões de consumo, ajudando a conscientizar sobre o impacto ambiental individual.

## ✨ Funcionalidades

- Cadastro e autenticação de usuários
- Dashboard personalizado
- Simulação de emissões de carbono
  - Transporte
  - Consumo de energia
  - Outros consumos
- Histórico de simulações
- Interface simples e responsiva

## 🛠️ Tecnologias utilizadas

- **Backend:** PHP
- **Banco de dados:** MySQL
- **Frontend:** HTML5, CSS3 e JavaScript
- **Arquitetura:** Organização em camadas (Controllers, Models e Repositories)

## 📁 Estrutura do projeto

```text
TCC_Credito_Carbono/
├── database/               # Script SQL do banco de dados
├── public/                 # Páginas e assets
│   ├── assets/             # CSS e JavaScript
│   └── simulacao/          # Telas de simulação
├── src/
│   ├── Controllers/        # Lógica de controle
│   ├── models/             # Modelos
│   ├── repositories/       # Acesso a dados
│   └── config/             # Configurações
└── README.md
```

## Instalação e configuração

1. Faça backup do banco antes de substituir um esquema existente.
2. Execute `database/banco_SCC.sql` em uma instalação vazia do MySQL/MariaDB. O script cria as tabelas usadas pela aplicação; ele não migra dados de uma versão anterior.
3. Configure as variáveis de ambiente `DB_HOST`, `DB_USER`, `DB_PASSWORD` e `DB_NAME` para o usuário de banco da aplicação. Os valores padrão destinam-se somente ao XAMPP local.
4. Aponte o servidor web para a pasta `public/`. A página de teste de transporte está desativada.

Cadastro exige senha com pelo menos 8 caracteres. Os formulários de escrita usam token CSRF e consultas parametrizadas. O usuário da conexão deve ter apenas permissões necessárias para leitura e escrita nas tabelas do sistema.

## Cálculo e limitações

O total de cada simulação é atualizado junto com a inclusão do item, dentro de transação. Os fatores de transporte ficam no catálogo `tipos_transportes`; o script de inicialização inclui referências médias brasileiras do BNDES para carro, ônibus urbano a diesel e elétrico e metrô, além de bicicleta e caminhada com emissões operacionais diretas iguais a zero. São médias de referência e não representam uma linha ou veículo específico. O catálogo do banco já existente não é alterado automaticamente: qualquer atualização deve ser planejada e aplicada separadamente, com backup. Para bases legadas, consulte `database/migracao_transportes.sql` antes de migrar. Eletricidade usa o fator definido no controller. O módulo de alimentação usa médias globais de Poore e Nemecek (2018), processadas por Our World in Data, em kg CO₂e por kg de alimento, com ano-base 2010. Esses fatores não são específicos do Brasil. Os valores são estimativas de emissões, não créditos de carbono certificados.

O histórico mostra somente simulações do usuário autenticado. Para instalações com dados anteriores, planeje uma migração específica após conferir o esquema e fazer backup; o script de criação não altera tabelas existentes.

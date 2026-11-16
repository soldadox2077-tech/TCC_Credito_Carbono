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
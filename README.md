# SECAMB - Sistema de Requisição de Licenciamento Ambiental

Plataforma desenvolvida no âmbito do IFBA – Campus Seabra para a Secretaria Municipal de Desenvolvimento, Turismo e Meio Ambiente (Seabra-BA), com o objetivo de modernizar e facilitar a gestão de empreendimentos e processos de licenciamento ambiental.

<div align="center">

![Laravel](https://img.shields.io/badge/Laravel-13.x-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)
![PHP](https://img.shields.io/badge/PHP-8.3+-777BB4?style=for-the-badge&logo=php&logoColor=white)
![Node.js](https://img.shields.io/badge/Node.js-20+-339933?style=for-the-badge&logo=node.js&logoColor=white)
![Vite](https://img.shields.io/badge/Vite-6.x-646CFF?style=for-the-badge&logo=vite&logoColor=white)
![Status](https://img.shields.io/badge/Status-Em%20Desenvolvimento-orange?style=for-the-badge)

<p align="center">
  <b>Sistema de Requisição de Licenciamento Ambiental</b><br>
  <i>Instituto Federal de Educação, Ciência e Tecnologia da Bahia (IFBA) — Campus Seabra</i><br>
  <i>Secretaria Municipal de Desenvolvimento, Turismo e Meio Ambiente (Seabra-BA)</i>
</p>

</div>

## O que o sistema faz

Conforme detalhado no **Manual do Usuário**:

- **Cadastro e Autenticação:**
  - Formulário dinâmico para **Pessoa Física** (Nome, CPF, Endereço, etc.) e **Pessoa Jurídica** (Razão Social, CNPJ, etc.).
  - Controle de perfis de acesso: **Usuário Comum** e **Administrador** (servidores/gestores municipais).
- **Módulo de Empreendimentos:**
  - Consulta e listagem de empreendimentos vinculados.
  - Busca de empreendimento por CNPJ.
  - Solicitação de representação legal com aceite do Termo de Declaração.
  - Cadastro de novos empreendimentos (Bacia Hidrográfica, Recurso Hídrico, Fase de Operação) e seus responsáveis legais.
- **Módulo de Processos e Licenciamento:**
  - Abertura de novos processos de licenciamento vinculados a um empreendimento.
  - Geração automática de número de protocolo.
  - Acompanhamento do status dos processos em tempo real (*Novo*, *Em Atendimento*, *Finalizado*, *Devolvido*, *Indeferido*, *Expirado*).
  - Consulta rápida de processos através do número de protocolo.
- **Área Administrativa:**
  - Gestão e triagem de todos os empreendimentos e processos cadastrados no município.
  - Atualização de status e emissão de pareceres/relatórios pela equipe da prefeitura.

## Fluxo principal

```text
Cadastro / Login -> Gestão de Empreendimento (Cadastro / Vínculo)
                 -> Abertura de Processo de Licenciamento
                 -> Geração Automática de Protocolo
                 -> Análise e Atualização de Status pela Secretaria
```

## Requisitos

- PHP 8.3 ou superior
- Composer
- Node.js 20 ou superior e npm
- MySQL/MariaDB ou SQLite

## Instalação rápida

Na raiz do projeto, execute:

```bash
composer run setup
```

Esse comando instala as dependências, cria o arquivo `.env`, gera a chave da aplicação, executa as migrations e compila os assets.

Para iniciar o ambiente de desenvolvimento:

```bash
composer run dev
```

O comando inicia o servidor Laravel, o worker de filas e o Vite.

## Configuração

Copie `.env.example` para `.env` caso o arquivo ainda não exista:

```bash
cp .env.example .env
php artisan key:generate
```

Configure principalmente:

```dotenv
APP_NAME="SECAMB -  Seabra"

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=secamb
DB_USERNAME=root
DB_PASSWORD=

MAIL_MAILER=log
MAIL_FROM_ADDRESS="[EMAIL_ADDRESS]"
MAIL_FROM_NAME="SECAMB - Seabra"
```

Para envio real de e-mails, altere `MAIL_MAILER` e informe os dados do servidor SMTP.

## Comandos úteis

```bash
# Rodar migrations
php artisan migrate

# Executar os testes
composer run test

# Gerar os assets para produção
npm run build
```


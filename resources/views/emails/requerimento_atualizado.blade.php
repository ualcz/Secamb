<x-mail::message>
@if($autor === 'aluno')
# Correção Enviada no Requerimento #{{ $requerimento->numero_protocolo }}

Olá,

O aluno **{{ $requerimento->usuario?->nome ?? 'Aluno' }}** enviou novas informações / documentos para o requerimento de **{{ $requerimento->objetoDoRequerimento }}**.

@if(!empty(trim($mensagem)))
<x-mail::panel>
**Mensagem do Aluno:**<br>
{!! nl2br(e($mensagem)) !!}
</x-mail::panel>
@endif

**Ação necessária:** Acesse o painel do setor para conferir as alterações e dar prosseguimento ao atendimento.

@php
    $urlDestino = $requerimento->setor_id
        ? route('setor.requerimentos.show', [$requerimento->setor_id, $requerimento->id])
        : config('app.url');
@endphp

<x-mail::button :url="$urlDestino">
Analisar no Painel
</x-mail::button>

@else
{{-- Enviado pelo setor para o aluno --}}
@if($requerimento->status === 'Indeferido')
# Requerimento Indeferido #{{ $requerimento->numero_protocolo }}

Olá, **{{ $requerimento->usuario?->nome ?? 'Aluno(a)' }}**.

O seu requerimento referente a **{{ $requerimento->objetoDoRequerimento }}** foi **indeferido**.

@if(!empty(trim($mensagem)) && !in_array($mensagem, ['Status atualizado pelo setor.', 'Despacho registrado pelo setor.']))
<x-mail::panel>
**Motivo apontado pelo setor:**<br>
{!! nl2br(e($mensagem)) !!}
</x-mail::panel>
@endif

@if($solicitaNovoDocumento)
**Ação necessária:** Acesse o sistema para corrigir as informações ou reenviar a documentação solicitada.

<x-mail::button :url="route('requerimentos.cidadao.visualizar', $requerimento->id)">
Corrigir e Reenviar Documento
</x-mail::button>
@else
**Ação necessária:** Nenhuma ação necessária no momento. Aguarde orientações do setor caso necessário.
@endif

@elseif($requerimento->status === 'Concluído')
# Requerimento Concluído #{{ $requerimento->numero_protocolo }}

Olá, **{{ $requerimento->usuario?->nome ?? 'Aluno(a)' }}**.

O seu requerimento referente a **{{ $requerimento->objetoDoRequerimento }}** foi **concluído com sucesso**.

@if(!empty(trim($mensagem)) && !in_array($mensagem, ['Status atualizado pelo setor.', 'Despacho registrado pelo setor.']))
<x-mail::panel>
**Despacho / Parecer do setor:**<br>
{!! nl2br(e($mensagem)) !!}
</x-mail::panel>
@endif

**Ação necessária:** Nenhuma ação pendente. Você já pode visualizar o resultado final ou documentos emitidos no sistema.

<x-mail::button :url="route('requerimentos.cidadao.visualizar', $requerimento->id)">
Visualizar Requerimento
</x-mail::button>

@else
# Requerimento em Análise #{{ $requerimento->numero_protocolo }}

Olá, **{{ $requerimento->usuario?->nome ?? 'Aluno(a)' }}**.

O seu requerimento referente a **{{ $requerimento->objetoDoRequerimento }}** agora está **em análise** pelo setor responsável.

@if(!empty(trim($mensagem)) && !in_array($mensagem, ['Status atualizado pelo setor.', 'Despacho registrado pelo setor.']))
<x-mail::panel>
**Observações do setor:**<br>
{!! nl2br(e($mensagem)) !!}
</x-mail::panel>
@endif

**Ação necessária:** Nenhuma ação necessária no momento. Você será avisado assim que houver uma nova atualização.

<x-mail::button :url="route('requerimentos.cidadao.visualizar', $requerimento->id)">
Acompanhar Requerimento
</x-mail::button>
@endif

@endif

@if(!empty($arquivos) && count($arquivos) > 0)
---
*📎 Este e-mail possui documento(s) em anexo.*
@endif

Atenciosamente,<br>
**{{ config('app.name') }}**
</x-mail::message>

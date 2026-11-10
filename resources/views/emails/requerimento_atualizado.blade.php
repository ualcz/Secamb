<x-mail::message>
@if($autor === 'aluno')
# Correção Enviada no Requerimento #{{ $requerimento->numero_protocolo }}

Olá,

O cidadão **{{ $requerimento->usuario?->nome ?? 'Cidadão' }}** enviou novas informações / documentos para o requerimento de **{{ $requerimento->objetoDoRequerimento }}**.

@if(!empty(trim($mensagem)))
<x-mail::panel>
**Mensagem do Cidadão:**<br>
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
{{-- Enviado pelo setor para o cidadão --}}
@if($requerimento->status === 'Indeferido')
# Requerimento Indeferido #{{ $requerimento->numero_protocolo }}

Olá, **{{ $requerimento->usuario?->nome ?? 'Cidadão(ã)' }}**.

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

@elseif($requerimento->status === \App\Models\Requerimento::STATUS_AGUARDANDO_COMPLEMENTACAO)
# Complementação necessária no requerimento #{{ $requerimento->numero_protocolo }}

Olá, **{{ $requerimento->usuario?->nome ?? 'Cidadão(ã)' }}**.

O setor solicitou complementações para o requerimento referente a **{{ $requerimento->objetoDoRequerimento }}**. Envie as informações e documentos solicitados em até 90 dias.

@if(!empty(trim($mensagem)) && !in_array($mensagem, ['Status atualizado pelo setor.', 'Despacho registrado pelo setor.']))
<x-mail::panel>
**Orientação do setor:**<br>
{!! nl2br(e($mensagem)) !!}
</x-mail::panel>
@endif

<x-mail::button :url="route('requerimentos.cidadao.visualizar', $requerimento->id)">
Enviar complementação
</x-mail::button>

@elseif($requerimento->status === \App\Models\Requerimento::STATUS_DEFERIDO)
# Requerimento deferido #{{ $requerimento->numero_protocolo }}

O pedido foi deferido. A licença ainda não foi registrada como emitida; acompanhe o processo para a próxima atualização.

@elseif($requerimento->status === \App\Models\Requerimento::STATUS_LICENCA_EMITIDA)
# Licença emitida #{{ $requerimento->numero_protocolo }}

A licença do requerimento **{{ $requerimento->objetoDoRequerimento }}** foi emitida. Consulte os documentos no sistema.

<x-mail::button :url="route('requerimentos.cidadao.visualizar', $requerimento->id)">
Visualizar licença e requerimento
</x-mail::button>

@elseif(in_array($requerimento->status, [
    \App\Models\Requerimento::STATUS_ENCERRADO,
    \App\Models\Requerimento::STATUS_EXPIRADO,
], true))
# Requerimento encerrado #{{ $requerimento->numero_protocolo }}

O processo foi encerrado com o status **{{ $requerimento->status }}**.

@else
# Atualização do requerimento #{{ $requerimento->numero_protocolo }}

Olá, **{{ $requerimento->usuario?->nome ?? 'Cidadão(ã)' }}**.

O seu requerimento referente a **{{ $requerimento->objetoDoRequerimento }}** está **{{ $requerimento->status }}**.

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

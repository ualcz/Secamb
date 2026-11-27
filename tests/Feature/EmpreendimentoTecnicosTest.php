<?php

use App\Models\Empreendimento;
use App\Models\Requerimento;
use App\Models\Setor;
use App\Models\Usuario;
use App\Mail\TecnicoVinculadoEmpreendimentoMail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;

uses(RefreshDatabase::class);

function dadosCaracterizacaoTecnicoTeste(): array
{
    return [
        'unidade_conservacao_resposta' => 'nao',
        'licenca_anterior' => 'nao',
        'processo_semma' => 'nao',
        'pendencia_ambiental' => 'nao',
    ];
}

function criarUsuarioCidadao(string $nome, string $email): Usuario
{
    return Usuario::create([
        'tipo_registro' => 'fisica',
        'nome' => $nome,
        'email' => $email,
        'password' => bcrypt('password'),
        'role' => 'cidadao',
        'ativo' => true,
    ]);
}

test('responsável pode vincular mais de um técnico pelo e-mail e revogar o acesso', function () {
    Mail::fake();

    $responsavel = criarUsuarioCidadao('Responsável', 'responsavel@example.com');
    $tecnicoUm = criarUsuarioCidadao('Técnica Um', 'tecnica1@example.com');
    $tecnicoDois = criarUsuarioCidadao('Técnico Dois', 'tecnico2@example.com');
    $outroCidadao = criarUsuarioCidadao('Outro cidadão', 'outro@example.com');
    $empreendimento = Empreendimento::create([
        'usuario_id' => $responsavel->id,
        'nome' => 'Fazenda de teste',
    ]);

    $this->actingAs($outroCidadao)
        ->post(route('empreendimentos.tecnicos.adicionar', $empreendimento), ['email' => $tecnicoUm->email])
        ->assertForbidden();

    $this->actingAs($responsavel)
        ->post(route('empreendimentos.tecnicos.adicionar', $empreendimento), ['email' => $tecnicoUm->email])
        ->assertRedirect(route('empreendimentos.edit', $empreendimento));

    $this->post(route('empreendimentos.tecnicos.adicionar', $empreendimento), ['email' => $tecnicoDois->email])
        ->assertRedirect(route('empreendimentos.edit', $empreendimento));

    expect($empreendimento->tecnicos()->count())->toBe(2)
        ->and($tecnicoUm->fresh()->todosEmpreendimentos()->whereKey($empreendimento->id)->exists())->toBeTrue();
    Mail::assertSent(TecnicoVinculadoEmpreendimentoMail::class, 2);

    $this->delete(route('empreendimentos.tecnicos.remover', [$empreendimento, $tecnicoUm->id]))
        ->assertRedirect(route('empreendimentos.edit', $empreendimento));

    expect($empreendimento->tecnicos()->whereKey($tecnicoUm->id)->exists())->toBeFalse()
        ->and($empreendimento->tecnicos()->whereKey($tecnicoDois->id)->exists())->toBeTrue();
});

test('cadastro do empreendimento pode vincular e notificar o técnico por e-mail', function () {
    Mail::fake();

    $responsavel = criarUsuarioCidadao('Responsável', 'responsavel@example.com');
    $tecnico = criarUsuarioCidadao('Técnico Ambiental', 'tecnico@example.com');

    $this->actingAs($responsavel)
        ->post(route('empreendimentos.store'), [
            'nome' => 'Fazenda São João',
            'tecnico_email' => $tecnico->email,
            'termo_aceito' => '1',
        ])
        ->assertRedirect(route('empreendimentos.index'));

    $empreendimento = Empreendimento::where('nome', 'Fazenda São João')->firstOrFail();
    expect($empreendimento->tecnicos()->whereKey($tecnico->id)->exists())->toBeTrue();

    Mail::assertSent(TecnicoVinculadoEmpreendimentoMail::class, function ($mail) use (
        $tecnico,
        $responsavel,
        $empreendimento
    ) {
        $conteudo = $mail->render();

        return $mail->hasTo($tecnico->email)
            && str_contains($conteudo, $empreendimento->nome)
            && str_contains($conteudo, $responsavel->nome)
            && str_contains($conteudo, $responsavel->email);
    });
});

test('cadastro não é criado quando o e-mail informado não corresponde a um técnico cadastrado', function () {
    $responsavel = criarUsuarioCidadao('Responsável', 'responsavel@example.com');

    $this->actingAs($responsavel)
        ->from(route('empreendimentos.create'))
        ->post(route('empreendimentos.store'), [
            'nome' => 'Fazenda sem técnico',
            'tecnico_email' => 'nao-cadastrado@example.com',
            'termo_aceito' => '1',
        ])
        ->assertRedirect(route('empreendimentos.create'))
        ->assertSessionHasErrors('tecnico_email');

    expect(Empreendimento::count())->toBe(0);
});

test('técnico vinculado pode acompanhar processos do empreendimento, mas não de outro', function () {
    $responsavel = criarUsuarioCidadao('Responsável', 'responsavel@example.com');
    $tecnico = criarUsuarioCidadao('Técnico Ambiental', 'tecnico@example.com');
    $outroUsuario = criarUsuarioCidadao('Outro usuário', 'outro@example.com');
    $empreendimento = Empreendimento::create([
        'usuario_id' => $responsavel->id,
        'nome' => 'Fazenda vinculada',
    ]);
    $outroEmpreendimento = Empreendimento::create([
        'usuario_id' => $outroUsuario->id,
        'nome' => 'Fazenda particular',
    ]);
    $empreendimento->tecnicos()->attach($tecnico->id);
    $setor = Setor::create([
        'setor_sigla' => 'SEMA',
        'setor_nome' => 'Secretaria de Meio Ambiente',
        'ativo' => true,
        'is_interno' => false,
    ]);
    $processoVinculado = Requerimento::create([
        'usuario_id' => $responsavel->id,
        'empreendimento_id' => $empreendimento->id,
        'setor_id' => $setor->id,
        'objetoDoRequerimento' => 'Licença de Operação',
    ]);
    $processoNaoVinculado = Requerimento::create([
        'usuario_id' => $outroUsuario->id,
        'empreendimento_id' => $outroEmpreendimento->id,
        'setor_id' => $setor->id,
        'objetoDoRequerimento' => 'Autorização Ambiental',
    ]);

    $this->actingAs($tecnico)
        ->get(route('requerimentos.cidadao.meusRequerimentos'))
        ->assertOk()
        ->assertSee($processoVinculado->objetoDoRequerimento)
        ->assertDontSee($processoNaoVinculado->objetoDoRequerimento);

    $this->get(route('requerimentos.cidadao.visualizar', $processoVinculado))
        ->assertOk();

    $this->get(route('requerimentos.cidadao.visualizar', $processoNaoVinculado))
        ->assertForbidden();
});

test('setor pode consultar dados dos técnicos vinculados ao empreendimento do requerimento', function () {
    $responsavel = criarUsuarioCidadao('Responsável', 'responsavel@example.com');
    $tecnico = criarUsuarioCidadao('Técnico Ambiental', 'tecnico@example.com');
    $naoVinculado = criarUsuarioCidadao('Outro técnico', 'outro-tecnico@example.com');
    $tecnico->update([
        'cpf' => '529.982.247-25',
        'celular' => '(75) 99999-1111',
    ]);
    $empreendimento = Empreendimento::create([
        'usuario_id' => $responsavel->id,
        'nome' => 'Fazenda vinculada',
    ]);
    $empreendimento->tecnicos()->attach($tecnico->id);

    $setor = Setor::create([
        'setor_sigla' => 'SEMA',
        'setor_nome' => 'Secretaria de Meio Ambiente',
        'ativo' => true,
        'is_interno' => false,
    ]);
    $setor->responsaveis()->attach($responsavel->id);
    $requerimento = Requerimento::create([
        'usuario_id' => $responsavel->id,
        'empreendimento_id' => $empreendimento->id,
        'setor_id' => $setor->id,
        'objetoDoRequerimento' => 'Licença de Operação',
    ]);

    $this->actingAs($responsavel)
        ->get(route('setor.requerimentos.show', [$setor->id, $requerimento->id]))
        ->assertOk()
        ->assertSee('Ver técnicos responsáveis (1)')
        ->assertSee('Técnico Ambiental')
        ->assertSee('529.982.247-25')
        ->assertSee('tecnico@example.com')
        ->assertSee('(75) 99999-1111')
        ->assertDontSee('outro-tecnico@example.com');
});

test('não é possível protocolar requerimento sem empreendimento', function () {
    $cidadao = criarUsuarioCidadao('Pessoa Física', 'pessoa@example.com');
    $outroCidadao = criarUsuarioCidadao('Outro cidadão', 'outro@example.com');
    $setor = Setor::create([
        'setor_sigla' => 'SEMA',
        'setor_nome' => 'Secretaria de Meio Ambiente',
        'ativo' => true,
        'is_interno' => false,
    ]);

    $this->actingAs($cidadao)
        ->from(route('requerimentos.cidadao.novo'))
        ->post(route('cidadao.enviar-email'), [
            'setor_id' => $setor->id,
            'objetoDoRequerimento' => 'Licença de Operação',
            'motivo' => 'Solicito licença para o empreendimento.',
            'aceite_declaracao' => '1',
        ])
        ->assertRedirect(route('requerimentos.cidadao.novo'))
        ->assertSessionHasErrors('empreendimento_id');

    expect(Requerimento::count())->toBe(0);

    $empreendimentoNaoVinculado = Empreendimento::create([
        'usuario_id' => $outroCidadao->id,
        'nome' => 'Empreendimento de outra pessoa',
    ]);

    $this->actingAs($cidadao)
        ->from(route('requerimentos.cidadao.novo'))
        ->post(route('cidadao.enviar-email'), [
            'setor_id' => $setor->id,
            'empreendimento_id' => $empreendimentoNaoVinculado->id,
            'objetoDoRequerimento' => 'Licença de Operação',
            'motivo' => 'Solicito licença para o empreendimento.',
            'aceite_declaracao' => '1',
            ...dadosCaracterizacaoTecnicoTeste(),
        ])
        ->assertRedirect(route('requerimentos.cidadao.novo'))
        ->assertSessionHasErrors('empreendimento_id');

    expect(Requerimento::count())->toBe(0);
});

test('cadastro continua oferecendo Pessoa Física e Pessoa Jurídica', function () {
    $this->get(route('register'))
        ->assertOk()
        ->assertSee('value="fisica" id="radioFisica" checked', false)
        ->assertSee('value="juridica" id="radioJuridica"', false);

    $this->post(route('register.submit'), [
        'tipo_registro' => 'fisica',
        'nome' => 'Pessoa Física de Teste',
        'cpf' => '529.982.247-25',
        'email' => 'pessoafisica@example.com',
        'endereco' => 'Rua de Teste',
        'bairro' => 'Centro',
        'cep' => '46900-000',
        'password' => 'password',
        'password_confirmation' => 'password',
    ])->assertRedirect(route('login'));

    $this->assertDatabaseHas('usuarios', [
        'email' => 'pessoafisica@example.com',
        'tipo_registro' => 'fisica',
        'cpf' => '529.982.247-25',
    ]);
});

test('caracterização ambiental não é mais editada no cadastro do empreendimento', function () {
    $cidadao = criarUsuarioCidadao('Pessoa Física', 'pessoa@example.com');
    $empreendimento = Empreendimento::create([
        'usuario_id' => $cidadao->id,
        'nome' => 'Empreendimento de teste',
    ]);

    $this->actingAs($cidadao)
        ->get(route('empreendimentos.create'))
        ->assertOk()
        ->assertDontSee('name="bacia_hidrografica"', false)
        ->assertDontSee('name="licenca_anterior"', false);

    $this->get(route('empreendimentos.edit', $empreendimento))
        ->assertOk()
        ->assertDontSee('name="bacia_hidrografica"', false)
        ->assertDontSee('name="licenca_anterior"', false);
});

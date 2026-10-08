<?php

use App\Http\Controllers\AdminDashboardController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\SocialiteController;
use App\Http\Controllers\EnvioEmailController;
use App\Http\Controllers\RequerimentoController;
use App\Http\Controllers\RequerimentoPdfController;
use App\Http\Controllers\AdminConsultaController;
use App\Http\Controllers\AdminSetorController;
use App\Http\Controllers\ResponsavelSetorController;
use App\Http\Controllers\UsersController;
use App\Http\Controllers\DocumentoRequerimentoController;
use App\Http\Controllers\EmpreendimentoController;
use App\Http\Controllers\PerfilController;

/*
|--------------------------------------------------------------------------
| REDIRECIONAMENTO INICIAL
|--------------------------------------------------------------------------
*/
// Route::redirect('/', '/login');
Route::get('/', function () {
    return view('home');
})->name('home');
/*
|--------------------------------------------------------------------------
| LOGIN & AUTENTICAÇÃO — SECAMB (Prefeitura Municipal de Seabra)
|--------------------------------------------------------------------------
| Todos os perfis (cidadão, servidor, admin) fazem login com e-mail e senha.
*/
Route::get('/login', function () {
    return view('auth.login');
})->name('login');

Route::post('/login', [LoginController::class, 'login']);

Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
Route::post('/register', [RegisterController::class, 'register'])->name('register.submit');

Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

/*
|--------------------------------------------------------------------------
| LOGIN SOCIAL — GOOGLE (somente cidadãos)
|--------------------------------------------------------------------------
*/
Route::get('/auth/google', [SocialiteController::class, 'redirectToGoogle'])->name('auth.google');
Route::get('/auth/google/callback', [SocialiteController::class, 'handleGoogleCallback'])->name('auth.google.callback');

/*
|--------------------------------------------------------------------------
| COMPLETAR PERFIL — cidadãos com login social
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:cidadao'])->group(function () {
    Route::get('/perfil/completar', [PerfilController::class, 'completar'])->name('perfil.completar');
    Route::post('/perfil/completar', [PerfilController::class, 'salvar'])->name('perfil.completar.salvar');
});

/*
|--------------------------------------------------------------------------
| PAINEL ADMINISTRATIVO
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/admin', function () {
        return redirect()->route('admin.dashboard');
    });

    Route::get('/admin/profile', [AdminDashboardController::class, 'adminProfile'])->name('admin.adminProfile');

    Route::get('/admin/dashboard', [AdminDashboardController::class, 'index'])
        ->name('admin.dashboard');

    // Gerenciamento de Setores e Assuntos de Requerimentos
    Route::get('/admin/setores/criar', [AdminSetorController::class, 'create'])->name('admin.setores.create');
    Route::post('/admin/setores', [AdminSetorController::class, 'store'])->name('admin.setores.store');
    Route::get('/admin/modelos', [AdminSetorController::class, 'index'])->name('admin.modelos.index');

    // Gerenciamento de usuários do sistema;
    Route::get('/admin/usuarios', [UsersController::class, 'index'])->name('admin.users.index');
    Route::get('/admin/usuarios/registrar', [UsersController::class, 'create'])->name('admin.users.register');
    Route::post('/admin/usuarios/registrar', [UsersController::class, 'store'])->name('admin.users.criar-usuario');

});

Route::middleware(['auth', 'role:admin,servidor'])->group(function () {
    Route::get('/admin/consultar-requerimentos', [AdminConsultaController::class, 'index'])
        ->name('admin.consultar-requerimentos');
    Route::get('/admin/historico/{id}', [RequerimentoController::class, 'showHistorico'])
        ->name('admin.historico');
    Route::post('admin/assunto-requerimento/{id}/reordenar/{direcao}',[AdminSetorController::class,'reordenarAssuntos'])
        ->name('admin.reordenarAssunto');
});

// Administradores e responsáveis podem editar apenas os dados do próprio setor.
Route::middleware(['auth', 'setor.config'])->group(function () {
    Route::get('/admin/setores', [AdminSetorController::class, 'index'])->name('admin.setores.index');
    Route::get('/admin/setores/{id}/editar', [AdminSetorController::class, 'edit'])->name('admin.setores.edit');
    Route::put('/admin/setores/{id}', [AdminSetorController::class, 'update'])->name('admin.setores.update');
    Route::get('/admin/setores/{id}/assuntos/criar', [AdminSetorController::class, 'createAssunto'])->name('admin.setores.assuntos.create');
    Route::post('/admin/setores/{id}/assuntos', [AdminSetorController::class, 'storeAssunto'])->name('admin.setores.assuntos.store');
    Route::put('/admin/assuntos/{id}', [AdminSetorController::class, 'updateAssunto'])->name('admin.assuntos.update');
    Route::delete('/admin/assuntos/{id}', [AdminSetorController::class, 'destroyAssunto'])->name('admin.assuntos.destroy');
    Route::post('/admin/assuntos/{assuntoId}/documentos', [AdminSetorController::class, 'storeDocumento'])->name('admin.documentos.store');
    Route::put('/admin/documentos/{id}', [AdminSetorController::class, 'updateDocumento'])->name('admin.documentos.update');
    Route::delete('/admin/documentos/{id}', [AdminSetorController::class, 'destroyDocumento'])->name('admin.documentos.destroy');
    Route::get('/admin/modelos/{id}/editar', [AdminSetorController::class, 'edit'])->name('admin.modelos.edit');
    Route::put('/admin/modelos/{id}', [AdminSetorController::class, 'update'])->name('admin.modelos.update');
    Route::post('/admin/modelos/{id}/assuntos', [AdminSetorController::class, 'storeAssunto'])->name('admin.modelos.assuntos.store');
});

Route::middleware(['auth', 'role:servidor'])->group(function () {
    Route::get('/servidor/dashboard', [AdminDashboardController::class, 'index'])
        ->name('servidor.dashboard');
});

/*
|--------------------------------------------------------------------------
| PAINEL / SETOR - RESPONSÁVEL
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'responsavel'])->group(function () {
    Route::get('/setor/{setor_id}/dashboard', [ResponsavelSetorController::class, 'index'])
        ->name('setor.responsavel.dashboard');
    Route::get('setor/{setor}/requerimentos/{requerimento}', [ResponsavelSetorController::class, 'show'])
    ->whereNumber('requerimento')
    ->name('setor.requerimentos.show');
    Route::patch('/setor/{setor}/requerimentos/{requerimento}/atualizarStatus', [ResponsavelSetorController::class, 'atualizarStatus'])
    ->name('setor.requerimentos.atualizarStatus');
    Route::post('/setor/{setor}/requerimentos/{requerimento}/encaminhar', [ResponsavelSetorController::class, 'encaminhar'])
    ->name('setor.requerimentos.encaminhar');
    Route::post('/setor/{setor}/requerimentos/{requerimento}/responder-encaminhamento', [ResponsavelSetorController::class, 'responderEncaminhamento'])
    ->name('setor.requerimentos.responderEncaminhamento');
});

/*
|--------------------------------------------------------------------------
| PAINEL / PROCESSOS - CIDADÃO
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:cidadao', 'perfil.completo'])->group(function () {
    Route::get('/requerimentos/cidadao', function () {
        $setores = \App\Models\Setor::publicos()->get();
        return view('requerimentos.aluno', compact('setores'));
    })->name('requerimentos.aluno');

    Route::post('/requerimentos/aluno/enviar-email', [EnvioEmailController::class, 'enviar'])->name('aluno.enviar-email');
    Route::get('/requerimentos/aluno/novo', [RequerimentoController::class, 'create'])->name('requerimentos.aluno.novo');
    Route::get('/requerimentos/aluno/meusRequerimentos', [RequerimentoController::class, 'index'])->name('requerimentos.aluno.meusRequerimentos');
    Route::get('/requerimentos/aluno/visualizar/{requerimento}', [RequerimentoController::class, 'show'])
        ->name('requerimentos.aluno.visualizar');
    Route::post('/requerimentos/{requerimento}/reenviar', [RequerimentoController::class, 'reenviarRequerimento'])->name('requerimentos.reenviar');
});

/*
|--------------------------------------------------------------------------
| EMPREENDIMENTOS - GESTÃO E REPRESENTAÇÃO
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->group(function () {
    Route::get('/empreendimentos', [EmpreendimentoController::class, 'index'])->name('empreendimentos.index');
    Route::get('/empreendimentos/novo', [EmpreendimentoController::class, 'create'])->name('empreendimentos.create');
    Route::post('/empreendimentos', [EmpreendimentoController::class, 'store'])->name('empreendimentos.store');
    Route::get('/empreendimentos/{empreendimento}/editar', [EmpreendimentoController::class, 'edit'])->name('empreendimentos.edit');
    Route::put('/empreendimentos/{empreendimento}', [EmpreendimentoController::class, 'update'])->name('empreendimentos.update');
    Route::post('/empreendimentos/{empreendimento}/vincular', [EmpreendimentoController::class, 'solicitarRepresentacao'])->name('empreendimentos.vincular');
});

/*
|--------------------------------------------------------------------------
| PAINEL / PROCESSOS - SERVIDOR (TÉCNICO MUNICIPAL)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:servidor'])->group(function () {
    Route::get('/requerimentos/servidor', function () {
        return view('requerimentos.servidor');
    })->name('requerimentos.servidor');
});

/*
|--------------------------------------------------------------------------
| VISUALIZAÇÃO DE BLADE & GERAÇÃO DE PDF
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->group(function () {
    Route::get('/requerimentos/visualizar-blade', [RequerimentoPdfController::class, 'visualizarBlade'])->name('requerimentos.visualizar-blade');
    Route::get('/requerimentos/gerar-pdf', [RequerimentoPdfController::class, 'gerarPdf'])->name('requerimentos.gerar-pdf');
    Route::get('/requerimentos/{id}/gerar-comprovante', [RequerimentoPdfController::class, 'gerarComprovante'])->name('requerimentos.gerar-comprovante');

    /*
    |--------------------------------------------------------------------------
    | GESTÃO DE DOCUMENTOS DO HISTÓRICO & TRAMITAÇÃO
    |--------------------------------------------------------------------------
    */
    Route::get('/documentos/{documento}/visualizar', [DocumentoRequerimentoController::class, 'visualizar'])
        ->name('documentos.preview');
    Route::get('/documentos/{documento}/baixar', [DocumentoRequerimentoController::class, 'baixar'])
        ->name('documentos.download');
});


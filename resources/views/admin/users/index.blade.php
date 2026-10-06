@extends('layouts.app')

@section('title', 'Dashboard - SDP')
@section('tag', 'Administração')

@section('content')
    <style>
        {!! file_get_contents(public_path('css/users-table.css')) !!}
    </style>

@if (session()->has('success'))
    @include('components.users-success-validation')
@endif

<style>
    .modal {
  position: fixed;
  top: 0; left: 0;
  width: 100%; height: 100%;
  background: rgba(0,0,0,0.4);
  backdrop-filter: blur(4px); /* desfoca o fundo */
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 999;
}

.modal.hidden {
  display: none;
}

.modal-content {
  background: #fff;
  padding: 20px;
  border-radius: 8px;
  max-width: 600px;
  width: 100%;
}

</style>
@if ($errors->any())
    @include('components.users-error-validation')
@endif
<section class="dash-section">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
        <div>
            <h2 style="font-size: 1.5rem; font-weight: 700; margin: 0; color: #111827;">Usuários</h2>
        </div>
        <div style="display: flex; gap: 10px; align-items: center;">
            <button type="button" class="btn-acao" style="cursor:pointer;" onclick="openModalUser()">
                ＋ Novo usuário
            </button>
        </div>
    </div>
        <div style="overflow-x: auto;">
            <table class="dash-table">
                <thead>
                    <tr>
                        <th>Nome</th>
                        <th>Email</th>
                        <th>Tipo</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($usuarios as $user)
                        <tr>
                            <td>{{ $user->nome }}</td>
                            <td>{{ $user->email }}</td>
                            @if ($user->role == 'servidor')
                                <td>Servidor Municipal</td>
                            @elseif ($user->role == 'admin')
                                <td>Administrador</td>
                            @else
                                <td>{{ ucfirst($user->role) }}</td>
                            @endif
                    @endforeach
                </tbody>
            </table>
        </div>
</section>

<script>
    function openModalUser() {
        document.getElementById('modalUser').classList.remove('hidden');
    }

    function closeModalUser() {
        document.getElementById('modalUser').classList.add('hidden');
    }
</script>
@include('admin.users.register')
@endsection
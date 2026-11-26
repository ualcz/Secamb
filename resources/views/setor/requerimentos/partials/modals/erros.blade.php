@if($errors->any())
    <div class="acao-erros" role="alert">
        <strong>Não foi possível concluir a ação:</strong>
        <ul>
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

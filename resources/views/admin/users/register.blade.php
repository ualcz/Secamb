@vite('resources/css/admin/setor.css')
<style>
    main.container {
        max-width: 95% !important;
        width: 95% !important;
    }
</style>
<div id="modalUser" class="modal hidden">
    <div class="modal-content">
        <h2 style="font-size: 1.35rem; font-weight: 700; margin: 2px 0 0; color: #111827;">Registrar novo usuário</h2>
        <form method="POST" action="{{ route('admin.users.criar-usuario') }}">
            @csrf
            <div class="grid-dados-gerais">           
                <div class="form-group col-span-12">
                    <label for="nome" style="font-size:15px;">Nome:*</label>
                    <input type="text" id="nome" name="nome" placeholder="Digite o nome do usuário" required>
                </div>

                <div class="form-group col-span-12">
                    <label for="email" style="font-size:15px;">E-mail:*</label>
                    <input type="email" id="email" name="email" placeholder="Digite o email do usuário" required>
                </div>

                <div class="form-group col-span-12">
                    <label for="password" style="font-size:15px;">Senha:*</label>
                    <input type="password" id="password" name="password" placeholder="Digite a senha do usuário" required>
                </div>

                <div class="form-group col-span-12">
                    <label for="role" style="font-size:15px;">Chave de acesso:*</label>
                    <div class="col-md-6">
                        <select id="role" name="role">
                            <option value="admin">Administrador</option>
                            <option value="servidor">Servidor</option>
                        </select>
                    </div>
                </div>
            </div>

            <div style="display: flex; justify-content: flex-end; gap:10px; align-items: center;">
                <button type="button" onclick="closeModalUser()" class="btn-voltar" style="cursor:pointer;">Cancelar</button>
                <button type="submit" class="btn-salvar">Salvar</button>
            </div>
        </form>
    </div>
</div>
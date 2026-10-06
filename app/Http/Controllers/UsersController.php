<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Usuario;

class UsersController extends Controller
{
    public function index(){
        $usuarios = Usuario::where('role', '!=', 'cidadao')->get();
        return view('admin.users.index', compact('usuarios'));
    }

    public function create(){
        return view('admin.users.register');
    }

    public function store(Request $request){
        $request->validate([
            'nome' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:usuarios,email'],
            'password' => ['required', 'string', 'min:8'],
            'role' => ['required', 'string'],
            ]);

        Usuario::create([
            'nome' => $request->nome,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'role' => $request->role,
        ]);
        
        return redirect()->route('admin.users.index')->with('success', 'Usuário cadastrado com sucesso!');
    }

}

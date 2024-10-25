<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class CinestarController extends Controller
{
    public function acceso()
    {
        return view('acceso');
    }

    public function procesarAcceso(Request $request)
    {
        $resultado = DB::connection('mysql_second')->select('CALL sp_Usuario_Login(?, ?)', [
            $request->input('email'),
            $request->input('password')
        ]);
        if (!empty($resultado)) {
            session(['user_name' => $resultado[0]->Nombres]);
            return redirect()->route('home');
        }
        return redirect()->route('acceso')->with('error', 'Credenciales incorrectas.');
    }

    public function registrarUsuario(Request $request)
    {
        $nombres = $request->input('username');
        $correo = $request->input('email');
        $password = $request->input('password');
        $usuarioExistente = DB::connection('mysql_second')->select('SELECT * FROM Usuario WHERE Correo = ?', [$correo]);
        if (!empty($usuarioExistente)) {
            return redirect()->route('acceso')->with('error', 'El correo ya está registrado.');
        }
        DB::connection('mysql_second')->statement('CALL sp_Usuario_Guardar(?, ?, ?)', [$nombres, $correo, $password]);
        session(['user_name' => $nombres]);
        return redirect()->route('home');
    }

    public function cerrarSesion(Request $request)
    {
        $request->session()->forget(['email', 'nombre']);
        return redirect()->route('acceso');
    }

    public function home()
    {
        return view('home');
    }

    public function cines()
    {
        $cines = DB::select('call sp_getCines()');
        return view('cines', ['cines' => $cines]);
    }

    public function cine($id)
    {
        $cine = DB::select('call sp_getCine(?)', [$id])[0];
        $peliculas = DB::select('call sp_getCinePeliculas(?)', [$id]);
        $tarifas = DB::select('call sp_getCineTarifas(?)', [$id]);
        $cine->peliculas = $peliculas;
        $cine->tarifas = $tarifas;
        return view('cine', ['cine' => $cine]);
    }

    public function peliculas($id)
    {
        $numericId = $id === 'cartelera' ? 1 : ($id === 'estrenos' ? 2 : 0);
        $peliculas = DB::select('call sp_getPeliculas(?)', [$numericId]);
        return view('peliculas', ['peliculas' => $peliculas]);
    }

    public function pelicula($id)
    {
        $pelicula = DB::select('CALL sp_getPelicula(?)', [$id]);
        return view('pelicula', ['pelicula' => $pelicula]);
    }
}
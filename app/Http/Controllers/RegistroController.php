<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Registro;

class RegistroController extends ApiController
{
    public function index()
    {
        $Registro = Registro::select('id', 'balance')
            ->get();

        return $this->sendResponse($Registro, "Registro obtenidas correctamente");
        // return $this->sendError("Error Conocido", "Error controlado", 200);
    }

    public function store(Request $request)
    {
        try {
            $Registro = new Registro();
            $Registro->id = $request->input('id');
            $Registro->balance = $request->input('balance');
            $Registro->save();
            return $this->sendResponse($Registro, "Registro ingresada correctamente");
        } catch (\Exception $e) {
            return $this->sendError("Error Conocido", "Error al crear la Registro", 201);
        }
    }

    public function show($id)
    {

        $Registro = Registro::where('id', $id)
            ->select('id', 'balance')
            ->get();
        if (strlen($Registro) > 2) {
            return $this->sendResponse($Registro, "Registro obtenido correctamente");
        } else {
            return $this->sendError("Error Conocido", "Error al buscar el registro", 404);
        }
    }

    public function update(Request $request, $id) {
        $Registro = Registro::find($id);
        $Registro->balance = -100;
        $Registro->save();
        // ->$Registro -> "balance" = -100;

        return $this->sendResponse($Registro, "Registro obtenida correctamente");
    }


}

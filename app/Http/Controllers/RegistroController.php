<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Registro;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use Validator;
use App\Models\Token;

class RegistroController extends ApiController
{

    public function eventPost(Request $request)
    {
        //validaciones
        $validator = Validator::make($request->all(), [
            //'csvFile' => 'required|mimes:csv,xls,xlsx|max:15360',
            'event' => 'required'

        ]);
        if ($validator->fails()) {
            return $this->sendError("Error de validación", $validator->errors(), 422);
        }

        //resto de la logica
        switch ($request->event) {
            case 'deposito':
                return $this->deposito($request);
                break;
            case 'crear':
                return $this->crear($request);;
                break;
            case 'retiro':
                return $this->retiro($request);
                break;
            case 'transferencia':
                return $this->transferencia($request);
                break;
            case 'reset':
                return $this->reset($request);
                break;
        }
    }
    public function deposito(Request $request)
    {
        //validaciones
        $validator = Validator::make($request->all(), [
            //'csvFile' => 'required|mimes:csv,xls,xlsx|max:15360',
            'destino' => 'required',
            'monto' => 'required'

        ]);
        if ($validator->fails()) {
            return $this->sendError("Error de validación", $validator->errors(), 422);
        }

        // variables
        $destino = $request->input('destino');
        $monto = $request->input('monto');

        //resto de la logica
        $Registro = Registro::find($destino);
        if ($Registro) {
            $Registro->id = $destino;
            $Registro->balance = $Registro->balance + $monto;
            $Registro->save();
            return $this->sendResponse($Registro, "El deposito se a hecho corectamente ", 200);
        } else {
            return $this->sendError("Error Conocido", "Error. La cuenta solicitada no existe", 404);
        }
    }

    public function crear(Request $request)
    {
        //validaciones
        $validator = Validator::make($request->all(), [
            //'csvFile' => 'required|mimes:csv,xls,xlsx|max:15360',
            'id' => 'required',
            'mail' => 'required|email'
        ]);
        if ($validator->fails()) {
            return $this->sendError("Error de validación", $validator->errors(), 422);
        }
        // variables
        $id = $request->input('id');
        $mail = $request->input('mail');

        //resto de la logica
        $Registro = Registro::find($id);
        if (!$Registro) {
            $Registro2 = Registro::where('email', $mail)
                ->select('id', 'balance', 'email')
                ->get();
            if (strlen($Registro2) < 3) {
                $Registro = new Registro();
                $Registro->id = $id;
                $Registro->email = $mail;
                $Registro->balance = 0;
                $Registro->save();
                return $this->sendResponse($Registro, "Registro creado corectamente", 201);
            } else {
                return $this->sendError("Error Conocido", "Error este mail ya existe", 404);
            }
        } else {
            return $this->sendError("Error Conocido", "Error. El id de la cuenta ya existe", 404);
        }
    }

    public function balance($id)
    {
        $Registro = Registro::where('id', $id)
            ->select('id', 'balance', 'email')
            ->get();
        if (strlen($Registro) > 2) {
            return $this->sendResponse($Registro, "Registro obtenido correctamente");
        } else {
            return $this->sendError("Error Conocido", "Error al buscar el registro", 404);
        }
    }

    public function retiro(Request $request)
    {
        //validaciones
        $validator = Validator::make($request->all(), [
            //'csvFile' => 'required|mimes:csv,xls,xlsx|max:15360',
            'origen' => 'required',
            'monto' => 'required'

        ]);
        if ($validator->fails()) {
            return $this->sendError("Error de validación", $validator->errors(), 422);
        }

        // variables
        $retiro = $request->monto;

        $Registro = Registro::find($request->origen);
        switch ($retiro) {
            case $retiro > $Registro->balance:
                return $this->sendError("Error Conocido", "El retiro supera el monto del usuario", 404);
                break;
            case $retiro >= 1000:
                $tokenValid = $this->token1($request, $Registro, "retiro");
               
                if ($tokenValid === true) {
                    $Registro->balance = $Registro->balance - $retiro;
                    $Registro->save();
                    return $this->sendResponse($Registro, "Retiro realizado corectamente usando token");
                } else {
                    return $this->sendError("Error Conocido", "Problema con el token", 404);
                }
                // return $this->sendError("Error mensje", "El retiro supera los 1000$ es necesario un token", 404);
                break;
            default;
                $Registro->balance = $Registro->balance - $retiro;
                $Registro->save();
                return $this->sendResponse($Registro, "Retiro realizado corectamente");
                break;
        }
    }

    public function transferencia(Request $request)
    {
        //variables
        $idOrigen = $request->origen;
        $idDestino = $request->destino;
        $montoR = $request->monto;
        $RegistroOtrigrn = Registro::find($idOrigen);
        $RegistroDestno = Registro::find($idDestino);
        if (strlen($RegistroOtrigrn) > 2 && strlen($RegistroDestno) > 2) {
            if ($montoR < 1000 && $montoR < $RegistroOtrigrn->balance) {

                $RegistroOtrigrn->balance = $RegistroOtrigrn->balance - $montoR;
                $RegistroDestno->balance = $RegistroDestno->balance + $montoR;
                $RegistroOtrigrn->save();
                $RegistroDestno->save();

                return $this->sendResponse([$RegistroOtrigrn, $RegistroDestno], "Transferencia realizado corectamente");
            } else {
                if ($montoR >= 1000) {
                    $this->token1($request, $RegistroOtrigrn, "transferencia");
                } else {
                    return $this->sendError("Error Conocido", [$idOrigen, $idDestino, $montoR], 404);
                }
            }
        } else {
            return $this->sendError("Error Conocido", "Error: No se pudo encontrar la cuenta de horigen o destino", 404);
        }
    }

    public function reset(Request $Request)
    {
        switch ($Request->reset) {
            case 'token':
                Token::truncate();
                return $this->sendResponse("!!!!!!!", "Todos los tokens an sido borados", 200);
                break;
            case 'usuarios':
                Registro::truncate();
                return $this->sendResponse("!!!!!!!", "Todos los registros an sido borados", 200);
                break;
            default:
                Token::truncate();
                Registro::truncate();
                return $this->sendResponse("!!!!!!!", "Todos los registros y tokens an sido borados", 200);
                break;
        }
    }

    public function token1($request, $Registro, $tipo)
    {
        $origen = $request->input('origen');
        $monto = $request->input('monto');
        if ($request->token) {
            $token = token::where('idUsuario', '=', $origen)
                ->where('token', '=', $request->input('token'))
                ->get();
            if (strlen($token) > 3) {
                $token = $token[0];
                $timestop = $token->timestop;
                
                $date = Carbon::now();
                $date = $date->addMinute(5);
                
                if (Carbon::now() > $timestop) {
                    return false;
                } else {
                    return true;
                }
            } else {
                return $this->sendError("Error Conocido", "No se enconto el token", 404);
            }
        } else {
            //creacion de la fecha 
            $date = Carbon::now();
            $date = $date->addMinute(5);

            //Guardar/Crea el token con una fecha asociado a una id de usuario
            $token = new Token();
            $token->idUsuario = $Registro->id;
            $token->token = random_int(100000, 500000);
            $token->timestop = $date;
            $token->save();

            //envio del mail
            $data = array();
            $data['token'] =  $token->token;
            $data['email'] = $Registro->email;
            Mail::send('emails.mailCodigo', $data, function ($msj) use ($data) {
                $msj->subject('Envio de TOKEN');
                $msj->to($data['email']);
            });
            return $this->sendResponse("Token enviado", "revise su mail :)", 200);
        }
    }

    public function pruebas()
    {
    }
}

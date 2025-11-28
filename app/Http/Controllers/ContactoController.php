<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class ContactoController extends Controller
{
    public function enviarMensaje(Request $request)
    {
        $validacion = Validator::make($request->all(), [
            'nombre' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'telefono' => 'nullable|string|max:20',
            'mensaje' => 'required|string|max:1000',
        ], [
            'nombre.required' => 'El nombre es obligatorio.',
            'email.required' => 'El email es obligatorio.',
            'email.email' => 'El email debe ser válido.',
            'mensaje.required' => 'El mensaje es obligatorio.',
        ]);

        if ($validacion->fails()) {
            return back()->withErrors($validacion)->withInput();
        }

        // Aquí puedes agregar lógica para enviar el email o guardar en base de datos
        // Por ahora, solo retornamos éxito
        // TODO: Implementar envío de email o guardado en base de datos
        
        return back()->with('success', 'Mensaje enviado exitosamente. Nos pondremos en contacto contigo pronto.');
    }
}


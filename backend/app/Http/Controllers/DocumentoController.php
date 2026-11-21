<?php

namespace App\Http\Controllers;

use App\Models\Documento;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DocumentoController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $documentos = Documento::with('usuario:id,name')
            ->when($request->filled('buscar'), fn ($q) => $q->where('nombre', 'like', '%'.$request->buscar.'%'))
            ->latest()
            ->paginate(10);

        return response()->json($documentos);
    }

    public function store(Request $request): JsonResponse
    {
        $datos = $request->validate([
            'nombre' => ['required', 'string', 'max:150'],
            'archivo' => ['required', 'file', 'max:5120', 'mimes:pdf,doc,docx,xls,xlsx,csv,png,jpg,jpeg,txt'],
        ], [
            'nombre.required' => 'El nombre del documento es obligatorio.',
            'archivo.required' => 'Selecciona un archivo.',
            'archivo.max' => 'El archivo no puede superar 5 MB.',
            'archivo.mimes' => 'Formato no permitido. Usa PDF, Word, Excel, CSV, imagen o TXT.',
        ]);

        $archivo = $request->file('archivo');
        $ruta = $archivo->store('documentos');

        $documento = Documento::create([
            'nombre' => $datos['nombre'],
            'ruta' => $ruta,
            'tipo' => $archivo->getClientOriginalExtension(),
            'user_id' => $request->user()->id,
        ]);

        return response()->json([
            'message' => 'Documento cargado correctamente.',
            'documento' => $documento->load('usuario:id,name'),
        ], 201);
    }

    public function descargar(Documento $documento): StreamedResponse|JsonResponse
    {
        if (! Storage::exists($documento->ruta)) {
            return response()->json(['message' => 'El archivo ya no existe en el servidor.'], 404);
        }

        $nombre = str($documento->nombre)->slug().'.'.$documento->tipo;

        return Storage::download($documento->ruta, $nombre);
    }

    public function destroy(Documento $documento): JsonResponse
    {
        Storage::delete($documento->ruta);
        $documento->delete();

        return response()->json(['message' => 'Documento eliminado correctamente.']);
    }
}

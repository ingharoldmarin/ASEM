<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\Ficha;
use App\Models\Folder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class FolderController extends Controller
{
    public function show(Ficha $ficha, Folder $folder)
    {
        abort_if($folder->ficha_id !== $ficha->id, 404);
        $folder->load('documents.uploader');
        return view('folders.show', compact('ficha', 'folder'));
    }

    public function rename(Request $request, Folder $folder)
    {
        $data = $request->validate(['name' => 'required|string|max:255']);
        $folder->update(['name' => $data['name']]);
        return back()->with('success', 'Carpeta renombrada.');
    }

    public function uploadDocument(Request $request, Folder $folder)
    {
        $request->validate([
            'files'   => 'required',
            'files.*' => 'max:20480',
        ]);

        $allowedExtensions = ['pdf','doc','docx','xls','xlsx','ppt','pptx','jpg','jpeg','png','gif','bmp','webp'];
        $storePath = 'documents/' . $folder->ficha_id . '/' . $folder->id;
        $count     = 0;
        $skipped   = [];

        $files = $request->file('files');
        if (!is_array($files)) {
            $files = [$files];
        }

        foreach ($files as $file) {
            if (!$file || !$file->isValid()) continue;

            $ext = strtolower($file->getClientOriginalExtension());
            if (!in_array($ext, $allowedExtensions)) {
                $skipped[] = $file->getClientOriginalName();
                continue;
            }

            $path = $file->store($storePath, 'public');
            Document::create([
                'folder_id'     => $folder->id,
                'original_name' => $file->getClientOriginalName(),
                'file_path'     => $path,
                'file_type'     => $file->getMimeType(),
                'uploaded_by'   => auth()->id(),
            ]);
            $count++;
        }

        if ($count > 0 && in_array($folder->status, ['sin_subir', 'rechazado'])) {
            $folder->update(['status' => 'en_revision', 'rejection_comment' => null]);
        }

        $msg = "{$count} documento(s) subido(s) correctamente.";
        if ($skipped) {
            $msg .= ' Omitidos (formato no permitido): ' . implode(', ', $skipped);
        }

        return back()->with('success', $msg);
    }

    public function deleteDocument(Document $document)
    {
        Storage::disk('public')->delete($document->file_path);
        $folder = $document->folder;
        $document->delete();

        // If no more documents, reset to sin_subir
        if ($folder->documents()->count() === 0) {
            $folder->update(['status' => 'sin_subir']);
        }

        return back()->with('success', 'Documento eliminado.');
    }

    public function approve(Request $request, Folder $folder)
    {
        $folder->update([
            'status'      => 'aprobado',
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
            'rejection_comment' => null,
        ]);
        return back()->with('success', 'Carpeta aprobada.');
    }

    public function reject(Request $request, Folder $folder)
    {
        $data = $request->validate(['rejection_comment' => 'required|string|max:500']);
        $folder->update([
            'status'             => 'rechazado',
            'reviewed_by'        => auth()->id(),
            'reviewed_at'        => now(),
            'rejection_comment'  => $data['rejection_comment'],
        ]);
        return back()->with('success', 'Carpeta rechazada.');
    }
}

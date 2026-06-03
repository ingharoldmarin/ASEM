<?php

namespace App\Http\Controllers;

use App\Models\MonthlyReport;
use App\Models\MonthlyReportFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MonthlyReportController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        $query = MonthlyReport::with(['instructor', 'files'])
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc');

        if ($user->isInstructor()) {
            $query->where('instructor_id', $user->id);
        }

        $reports = $query->paginate(12);
        return view('reports.index', compact('reports'));
    }

    public function create()
    {
        return view('reports.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'month'   => 'required|integer|between:1,12',
            'year'    => 'required|integer|min:2020|max:2099',
            'files'   => 'required|array|min:1|max:4',
            'files.*' => 'required|file|mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,jpg,jpeg,png|max:20480',
        ]);

        // Un informe por instructor por mes
        $exists = MonthlyReport::where('instructor_id', auth()->id())
            ->where('month', $data['month'])
            ->where('year', $data['year'])
            ->exists();

        if ($exists) {
            return back()->withErrors(['month' => 'Ya existe un informe para ese mes y año.'])->withInput();
        }

        $report = MonthlyReport::create([
            'instructor_id' => auth()->id(),
            'ficha_id'      => null,
            'month'         => $data['month'],
            'year'          => $data['year'],
            'status'        => 'pendiente',
        ]);

        foreach ($request->file('files') as $file) {
            $path = $file->store('reports/' . auth()->id() . '/' . $data['year'] . '_' . $data['month'], 'public');
            MonthlyReportFile::create([
                'report_id'     => $report->id,
                'original_name' => $file->getClientOriginalName(),
                'file_path'     => $path,
                'file_type'     => $file->getClientMimeType(),
            ]);
        }

        return redirect()->route('reports.index')->with('success', 'Informe subido correctamente.');
    }

    public function addFiles(Request $request, MonthlyReport $report)
    {
        abort_if($report->instructor_id !== auth()->id() && !auth()->user()->canManage(), 403);

        $request->validate([
            'files'   => 'required|array|min:1|max:4',
            'files.*' => 'required|file|mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,jpg,jpeg,png|max:20480',
        ]);

        foreach ($request->file('files') as $file) {
            $path = $file->store('reports/' . $report->instructor_id . '/' . $report->year . '_' . $report->month, 'public');
            MonthlyReportFile::create([
                'report_id'     => $report->id,
                'original_name' => $file->getClientOriginalName(),
                'file_path'     => $path,
                'file_type'     => $file->getClientMimeType(),
            ]);
        }

        return back()->with('success', 'Archivos agregados.');
    }

    public function updateStatus(Request $request, MonthlyReport $report)
    {
        $data = $request->validate([
            'status'  => 'required|in:revisado,aprobado,rechazado',
            'comment' => 'nullable|string|max:500',
        ]);

        $report->update($data);
        return back()->with('success', 'Estado actualizado.');
    }

    public function destroyFile(MonthlyReportFile $file)
    {
        abort_if($file->report->instructor_id !== auth()->id() && !auth()->user()->canManage(), 403);
        Storage::disk('public')->delete($file->file_path);
        $file->delete();
        return back()->with('success', 'Archivo eliminado.');
    }

    public function destroy(MonthlyReport $report)
    {
        abort_if($report->instructor_id !== auth()->id() && !auth()->user()->canManage(), 403);
        foreach ($report->files as $file) {
            Storage::disk('public')->delete($file->file_path);
        }
        $report->delete();
        return back()->with('success', 'Informe eliminado.');
    }
}

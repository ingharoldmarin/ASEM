<?php

namespace App\Http\Controllers;

use App\Models\AttendanceRecord;
use App\Models\AttendanceSession;
use App\Models\Ficha;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    public function index(Ficha $ficha)
    {
        $user = auth()->user();
        $query = AttendanceSession::with('records.aprendiz')->where('ficha_id', $ficha->id);

        if ($user->isInstructor()) {
            $query->where('instructor_id', $user->id);
        }

        $sessions = $query->orderBy('date', 'desc')->paginate(15);
        return view('attendance.index', compact('ficha', 'sessions'));
    }

    public function create(Ficha $ficha)
    {
        $aprendices = $ficha->aprendices()->orderBy('name')->get();
        return view('attendance.create', compact('ficha', 'aprendices'));
    }

    public function store(Request $request, Ficha $ficha)
    {
        $data = $request->validate([
            'date'              => 'required|date',
            'topic'             => 'nullable|string|max:255',
            'attendance'        => 'required|array',
            'attendance.*'      => 'required|in:presente,ausente,excusa',
        ]);

        $session = AttendanceSession::firstOrCreate([
            'instructor_id' => auth()->id(),
            'ficha_id'      => $ficha->id,
            'date'          => $data['date'],
        ], ['topic' => $data['topic']]);

        foreach ($data['attendance'] as $aprendizId => $status) {
            AttendanceRecord::updateOrCreate(
                ['session_id' => $session->id, 'aprendiz_id' => $aprendizId],
                ['status' => $status]
            );
        }

        return redirect()->route('attendance.index', $ficha)->with('success', 'Asistencia registrada.');
    }

    public function show(Ficha $ficha, AttendanceSession $session)
    {
        abort_if($session->ficha_id !== $ficha->id, 404);
        $session->load('records.aprendiz');
        return view('attendance.show', compact('ficha', 'session'));
    }
}

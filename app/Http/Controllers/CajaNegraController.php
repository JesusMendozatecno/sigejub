<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\User;
use Illuminate\Http\Request;

class CajaNegraController extends Controller
{
    private function verifyAdmin(): void
    {
        if (auth()->user()?->role !== 'admin') {
            abort(403, 'Acceso no autorizado');
        }
    }

    public function index(Request $request)
    {
        $this->verifyAdmin();
        $query = Activity::with('user');

        if ($userId = $request->get('user_id')) {
            $query->where('user_id', $userId);
        }

        if ($action = $request->get('action')) {
            $query->where('action', $action);
        }

        if ($subjectType = $request->get('subject_type')) {
            $query->where('subject_type', $subjectType);
        }

        if ($search = $request->get('search')) {
            $query->where('description', 'like', "%{$search}%");
        }

        if ($from = $request->get('from')) {
            $query->where('created_at', '>=', $from);
        }

        if ($to = $request->get('to')) {
            $query->where('created_at', '<=', $to . ' 23:59:59');
        }

        $activities = $query->latest()->paginate($request->get('per_page', 50));

        return response()->json($activities);
    }

    public function show($id)
    {
        $this->verifyAdmin();
        $activity = Activity::with('user')->findOrFail($id);
        return response()->json($activity);
    }

    public function stats()
    {
        $this->verifyAdmin();
        $total = Activity::count();
        $today = Activity::whereDate('created_at', today())->count();
        $byAction = Activity::selectRaw('action, COUNT(*) as total')
            ->groupBy('action')->orderByDesc('total')->get();
        $byType = Activity::selectRaw('subject_type, COUNT(*) as total')
            ->groupBy('subject_type')->orderByDesc('total')->get();
        $byUser = Activity::selectRaw('user_id, COUNT(*) as total')
            ->groupBy('user_id')->orderByDesc('total')->take(10)->with('user')->get();
        $lastWeek = Activity::where('created_at', '>=', now()->subDays(7))->count();

        return response()->json(compact('total', 'today', 'byAction', 'byType', 'byUser', 'lastWeek'));
    }

    public function exportar(Request $request)
    {
        $this->verifyAdmin();
        $query = Activity::with('user');

        if ($from = $request->get('from')) {
            $query->where('created_at', '>=', $from);
        }
        if ($to = $request->get('to')) {
            $query->where('created_at', '<=', $to . ' 23:59:59');
        }

        $activities = $query->latest()->get();

        return view('pdf.caja-negra', compact('activities'));
    }

    public function usuarios()
    {
        $this->verifyAdmin();
        return response()->json(User::select('id', 'name', 'email')->orderBy('name')->get());
    }
}

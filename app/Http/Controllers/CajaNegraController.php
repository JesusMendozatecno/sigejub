<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\User;
use Illuminate\Http\Request;

class CajaNegraController extends Controller
{
    private function verifyAdmin(): void
    {
        if (auth()->user()?->rol !== 'admin') {
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

        if ($accion = $request->get('accion')) {
            $query->where('accion', $accion);
        }

        if ($tipoEntidad = $request->get('tipo_entidad')) {
            $query->where('tipo_entidad', $tipoEntidad);
        }

        if ($search = $request->get('search')) {
            $query->where('descripcion', 'like', "%{$search}%");
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
        $byAction = Activity::selectRaw('accion, COUNT(*) as total')
            ->groupBy('accion')->orderByDesc('total')->get();
        $byType = Activity::selectRaw('tipo_entidad, COUNT(*) as total')
            ->groupBy('tipo_entidad')->orderByDesc('total')->get();
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
        return response()->json(User::select('id', 'nombre', 'correo')->orderBy('nombre')->get());
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Changelog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class ChangelogController extends Controller
{
    public function index()
    {
        $logs = Changelog::latest('created_at')->get();
        return response()->json($logs);
    }

    public function view()
    {
        Artisan::call('changelog:generate', ['--silent' => true]);
        $logs = Changelog::latest('created_at')->take(100)->get();

        $grouped = $logs->groupBy(fn($l) => $l->created_at->format('Y-m-d'));

        return view('usuarios.documentacion', compact('grouped'));
    }

    public function generate()
    {
        Artisan::call('changelog:generate');
        $output = Artisan::output();

        return response()->json([
            'message' => 'Changelog generado',
            'output' => $output,
        ]);
    }
}

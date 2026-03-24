<?php

namespace App\Http\Controllers;

use App\Models\DesignProject;
use App\Models\ProductModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class DesignProjectController extends Controller
{
    // ─── Dashboard / Index ────────────────────────────────────────────────────

    public function index()
    {
        $user = Auth::user();

        $projects = $user->designProjects()
            ->with(['productModel', 'latestRenderJob'])
            ->latest()
            ->paginate(12);

        return view('projects.index', compact('projects', 'user'));
    }

    // ─── Create: Pilih Model Produk ───────────────────────────────────────────

    public function selectModel()
    {
        $user = Auth::user();

        $models = ProductModel::active()
            ->accessibleBy($user)
            ->get()
            ->groupBy('category');

        // Model premium tapi user free: tampilkan tapi lock
        $premiumModels = $user->isFree()
            ? ProductModel::active()->where('is_premium', true)->get()->groupBy('category')
            : collect();

        return view('projects.select-model', compact('models', 'premiumModels', 'user'));
    }

    // ─── Create: Inisialisasi Proyek ─────────────────────────────────────────

    public function create(Request $request)
    {
        $request->validate([
            'product_model_id' => ['required', 'exists:product_models,id'],
            'name'             => ['required', 'string', 'max:100'],
        ]);

        $user         = Auth::user();
        $productModel = ProductModel::findOrFail($request->product_model_id);

        // Gate: model premium hanya untuk premium user
        if ($productModel->is_premium && $user->isFree()) {
            return back()->with('error', 'Model ini hanya tersedia untuk pengguna premium.');
        }

        $project = DesignProject::create([
            'user_id'          => $user->id,
            'product_model_id' => $productModel->id,
            'name'             => $request->name,
            'status'           => 'draft',
        ]);

        return redirect()->route('projects.editor', $project->slug)
            ->with('success', 'Proyek baru dibuat. Mulai desain!');
    }

    // ─── Editor ───────────────────────────────────────────────────────────────

    public function editor(string $slug)
    {
        $user    = Auth::user();
        $project = DesignProject::where('slug', $slug)
            ->forUser($user->id)
            ->with(['productModel', 'latestAiJob', 'latestRenderJob'])
            ->firstOrFail();

        return view('projects.editor', compact('project', 'user'));
    }

    // ─── Upload Desain Sendiri ────────────────────────────────────────────────

    public function uploadDesign(Request $request, string $slug)
    {
        $request->validate([
            'design_file' => ['required', 'file', 'mimes:png,jpg,jpeg,svg,pdf', 'max:10240'], // max 10MB
        ]);

        $user    = Auth::user();
        $project = DesignProject::where('slug', $slug)->forUser($user->id)->firstOrFail();

        // Hapus file lama jika ada
        if ($project->design_file_path) {
            Storage::disk('public')->delete($project->design_file_path);
        }

        $path = $request->file('design_file')->store(
            'designs/' . $user->id,
            'public'
        );

        $project->update([
            'design_file_path' => $path,
            'design_source'    => 'upload',
        ]);

        return response()->json([
            'success'    => true,
            'design_url' => asset('storage/' . $path),
            'message'    => 'Desain berhasil diupload.',
        ]);
    }

    // ─── Simpan Canvas State ──────────────────────────────────────────────────

    public function saveCanvas(Request $request, string $slug)
    {
        $request->validate([
            'canvas_data' => ['required', 'string'], // JSON dari editor
        ]);

        $user    = Auth::user();
        $project = DesignProject::where('slug', $slug)->forUser($user->id)->firstOrFail();

        $project->update([
            'canvas_data' => $request->canvas_data,
        ]);

        return response()->json(['success' => true, 'message' => 'Desain tersimpan.']);
    }

    // ─── Delete Proyek ────────────────────────────────────────────────────────

    public function destroy(string $slug)
    {
        $user    = Auth::user();
        $project = DesignProject::where('slug', $slug)->forUser($user->id)->firstOrFail();

        // Hapus file terkait
        collect([$project->design_file_path, $project->render_output_path, $project->render_preview_path])
            ->filter()
            ->each(fn($path) => Storage::disk('public')->delete($path));

        $project->delete();

        return redirect()->route('projects.index')->with('success', 'Proyek dihapus.');
    }
}
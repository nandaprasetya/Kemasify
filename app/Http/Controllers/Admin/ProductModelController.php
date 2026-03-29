<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProductModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductModelController extends Controller
{
    // ─── Index ────────────────────────────────────────────────────────────────
    public function index(Request $request)
    {
        $query = ProductModel::withCount('designProjects');

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }
        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        $models     = $query->orderBy('sort_order')->paginate(15)->withQueryString();
        $categories = ProductModel::distinct()->pluck('category');

        return view('admin.product-models.index', compact('models', 'categories'));
    }

    // ─── Create ───────────────────────────────────────────────────────────────
    public function create()
    {
        $categories = ['box', 'bottle', 'pouch', 'can', 'tube', 'bag', 'jar', 'other'];
        return view('admin.product-models.create', compact('categories'));
    }

    // ─── Store ────────────────────────────────────────────────────────────────
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'        => ['required', 'string', 'max:100'],
            'category'    => ['required', 'string', 'max:50'],
            'description' => ['nullable', 'string', 'max:500'],
            'dimensions'  => ['nullable', 'string'],
            'is_active'   => ['boolean'],
            'is_premium'  => ['boolean'],
            'sort_order'  => ['integer', 'min:0'],
            'thumbnail'   => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp', 'max:2048'],
            'model_3d'    => ['nullable', 'file', 'mimes:glb,gltf,obj', 'max:51200'],
        ]);

        $data = [
            'name'        => $validated['name'],
            'slug'        => Str::slug($validated['name']) . '-' . Str::random(4),
            'category'    => $validated['category'],
            'description' => $validated['description'] ?? null,
            'is_active'   => $request->boolean('is_active', true),
            'is_premium'  => $request->boolean('is_premium'),
            'sort_order'  => $request->input('sort_order', 0),
            'dimensions'  => $this->parseDimensions($request->input('dimensions')),
        ];

        // Upload thumbnail
        if ($request->hasFile('thumbnail')) {
            $data['thumbnail_path'] = $request->file('thumbnail')
                ->store('models/thumbnails', 'public');
        } else {
            $data['thumbnail_path'] = 'models/thumbnails/default.png';
        }

        // Upload 3D model
        if ($request->hasFile('model_3d')) {
            $data['model_3d_path'] = $request->file('model_3d')
                ->store('models/3d', 'public');
        }

        ProductModel::create($data);

        return redirect()->route('admin.product-models.index')
            ->with('success', 'Model produk "' . $data['name'] . '" berhasil ditambahkan.');
    }

    // ─── Edit ─────────────────────────────────────────────────────────────────
    public function edit(ProductModel $productModel)
    {
        $categories = ['box', 'bottle', 'pouch', 'can', 'tube', 'bag', 'jar', 'other'];
        return view('admin.product-models.edit', compact('productModel', 'categories'));
    }

    // ─── Update ───────────────────────────────────────────────────────────────
    public function update(Request $request, ProductModel $productModel)
    {
        $validated = $request->validate([
            'name'        => ['required', 'string', 'max:100'],
            'category'    => ['required', 'string', 'max:50'],
            'description' => ['nullable', 'string', 'max:500'],
            'dimensions'  => ['nullable', 'string'],
            'is_active'   => ['boolean'],
            'is_premium'  => ['boolean'],
            'sort_order'  => ['integer', 'min:0'],
            'thumbnail'   => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp', 'max:2048'],
            'model_3d'    => ['nullable', 'file', 'mimes:glb,gltf,obj', 'max:51200'],
        ]);

        $data = [
            'name'        => $validated['name'],
            'category'    => $validated['category'],
            'description' => $validated['description'] ?? null,
            'is_active'   => $request->boolean('is_active'),
            'is_premium'  => $request->boolean('is_premium'),
            'sort_order'  => $request->input('sort_order', 0),
            'dimensions'  => $this->parseDimensions($request->input('dimensions')),
        ];

        // Update thumbnail
        if ($request->hasFile('thumbnail')) {
            if ($productModel->thumbnail_path && $productModel->thumbnail_path !== 'models/thumbnails/default.png') {
                Storage::disk('public')->delete($productModel->thumbnail_path);
            }
            $data['thumbnail_path'] = $request->file('thumbnail')
                ->store('models/thumbnails', 'public');
        }

        // Update 3D model
        if ($request->hasFile('model_3d')) {
            if ($productModel->model_3d_path) {
                Storage::disk('public')->delete($productModel->model_3d_path);
            }
            $data['model_3d_path'] = $request->file('model_3d')
                ->store('models/3d', 'public');
        }

        $productModel->update($data);

        return redirect()->route('admin.product-models.index')
            ->with('success', 'Model produk "' . $productModel->name . '" berhasil diupdate.');
    }

    // ─── Destroy ──────────────────────────────────────────────────────────────
    public function destroy(ProductModel $productModel)
    {
        // Cek apakah ada project yang menggunakan model ini
        if ($productModel->designProjects()->exists()) {
            return back()->with('error', 'Model tidak bisa dihapus karena masih digunakan oleh ' . $productModel->design_projects_count . ' proyek.');
        }

        // Hapus file
        if ($productModel->thumbnail_path && $productModel->thumbnail_path !== 'models/thumbnails/default.png') {
            Storage::disk('public')->delete($productModel->thumbnail_path);
        }
        if ($productModel->model_3d_path) {
            Storage::disk('public')->delete($productModel->model_3d_path);
        }

        $name = $productModel->name;
        $productModel->delete();

        return redirect()->route('admin.product-models.index')
            ->with('success', 'Model produk "' . $name . '" berhasil dihapus.');
    }

    // ─── Toggle Active ────────────────────────────────────────────────────────
    public function toggleActive(ProductModel $productModel)
    {
        $productModel->update(['is_active' => !$productModel->is_active]);
        $status = $productModel->fresh()->is_active ? 'diaktifkan' : 'dinonaktifkan';
        return back()->with('success', '"' . $productModel->name . '" berhasil ' . $status . '.');
    }

    // ─── Private Helpers ──────────────────────────────────────────────────────
    private function parseDimensions(?string $input): ?array
    {
        if (!$input) return null;
        // Format: "10x15x5" atau JSON
        if (str_contains($input, 'x')) {
            $parts = explode('x', strtolower($input));
            if (count($parts) === 3) {
                return [
                    'width'  => (float) trim($parts[0]),
                    'height' => (float) trim($parts[1]),
                    'depth'  => (float) trim($parts[2]),
                ];
            }
        }
        return null;
    }
}
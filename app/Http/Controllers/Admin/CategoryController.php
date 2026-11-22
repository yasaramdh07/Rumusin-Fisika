<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function index(): View
    {
        $categories = Category::query()
            ->withCount('lessons')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->paginate(15);

        return view('admin.categories.index', [
            'categories' => $categories,
        ]);
    }

    public function create(): View
    {
        $nextOrder = ((int) Category::max('sort_order')) + 1;

        return view('admin.categories.form', [
            'category' => new Category(['sort_order' => $nextOrder]),
            'isEdit' => false,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateCategory($request);

        Category::create([
            'name' => trim($validated['name']),
            'slug' => $this->uniqueSlug($validated['name']),
            'description' => isset($validated['description']) ? trim((string) $validated['description']) : null,
            'sort_order' => (int) $validated['sort_order'],
        ]);

        return redirect()->route('admin.categories.index')->with('status', 'Kategori ditambahkan.');
    }

    public function edit(Category $category): View
    {
        return view('admin.categories.form', [
            'category' => $category,
            'isEdit' => true,
        ]);
    }

    public function update(Request $request, Category $category): RedirectResponse
    {
        $validated = $this->validateCategory($request);

        $name = trim($validated['name']);
        $slug = $category->name === $name
            ? $category->slug
            : $this->uniqueSlug($name, $category->id);

        $category->update([
            'name' => $name,
            'slug' => $slug,
            'description' => isset($validated['description']) ? trim((string) $validated['description']) : null,
            'sort_order' => (int) $validated['sort_order'],
        ]);

        return redirect()->route('admin.categories.index')->with('status', 'Perubahan kategori disimpan.');
    }

    public function destroy(Category $category): RedirectResponse
    {
        $category->delete();

        return redirect()->route('admin.categories.index')->with('status', 'Kategori dihapus.');
    }

    public function moveUp(Category $category): RedirectResponse
    {
        $previous = Category::query()
            ->where(function ($q) use ($category): void {
                $q->where('sort_order', '<', $category->sort_order)
                    ->orWhere(function ($q2) use ($category): void {
                        $q2->where('sort_order', $category->sort_order)
                            ->where('id', '<', $category->id);
                    });
            })
            ->orderByDesc('sort_order')
            ->orderByDesc('id')
            ->first();

        if ($previous) {
            $this->swapOrder($category, $previous);
        }

        return redirect()->route('admin.categories.index')->with('status', 'Urutan kategori diperbarui.');
    }

    public function moveDown(Category $category): RedirectResponse
    {
        $next = Category::query()
            ->where(function ($q) use ($category): void {
                $q->where('sort_order', '>', $category->sort_order)
                    ->orWhere(function ($q2) use ($category): void {
                        $q2->where('sort_order', $category->sort_order)
                            ->where('id', '>', $category->id);
                    });
            })
            ->orderBy('sort_order')
            ->orderBy('id')
            ->first();

        if ($next) {
            $this->swapOrder($category, $next);
        }

        return redirect()->route('admin.categories.index')->with('status', 'Urutan kategori diperbarui.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validateCategory(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:500'],
            'sort_order' => ['required', 'integer', 'min:0', 'max:9999'],
        ], [
            'name.required' => 'Nama kategori wajib diisi.',
            'name.max' => 'Nama kategori maksimal 100 karakter.',
            'description.max' => 'Deskripsi kategori maksimal 500 karakter.',
            'sort_order.required' => 'Urutan wajib diisi.',
            'sort_order.integer' => 'Urutan harus berupa angka bulat.',
            'sort_order.min' => 'Urutan minimal 0.',
        ]);
    }

    private function uniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($name);
        if ($base === '') {
            $base = 'kategori';
        }

        $slug = $base;
        $suffix = 2;

        while (
            Category::query()
                ->where('slug', $slug)
                ->when($ignoreId !== null, fn ($q) => $q->where('id', '!=', $ignoreId))
                ->exists()
        ) {
            $slug = $base.'-'.$suffix;
            $suffix++;
        }

        return $slug;
    }

    private function swapOrder(Category $first, Category $second): void
    {
        $firstOrder = $first->sort_order;
        $secondOrder = $second->sort_order;

        if ($firstOrder === $secondOrder) {
            $secondOrder = $firstOrder + 1;
        }

        $first->update(['sort_order' => $secondOrder]);
        $second->update(['sort_order' => $firstOrder]);
    }
}

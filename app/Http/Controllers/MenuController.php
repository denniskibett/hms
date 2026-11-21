<?php

namespace App\Http\Controllers;

use App\Models\MenuCategory;
use App\Models\MenuItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * The restaurant menu: categories, dishes and their priced portions.
 */
class MenuController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'category_id' => ['nullable', 'integer'],
            'search' => ['nullable', 'string', 'max:100'],
        ]);

        $categories = MenuCategory::ordered()->withCount('items')->get();

        $items = MenuItem::with(['category', 'variants' => fn ($q) => $q->orderBy('price')])
            ->when($filters['category_id'] ?? null, fn ($q, $id) => $q->where('category_id', $id))
            ->when($filters['search'] ?? null, fn ($q, $term) => $q->where('name', 'like', "%{$term}%"))
            ->orderByDesc('is_active')
            ->orderBy('name')
            ->get()
            ->groupBy('category_id');

        return view('kitchen.menu', compact('categories', 'items', 'filters'));
    }

    // ---- Categories --------------------------------------------------------

    public function storeCategory(Request $request): JsonResponse
    {
        $category = MenuCategory::create($this->validatedCategory($request));

        return response()->json(['success' => true, 'message' => "{$category->name} added."], 201);
    }

    public function updateCategory(Request $request, MenuCategory $category): JsonResponse
    {
        $category->update($this->validatedCategory($request, $category));

        return response()->json(['success' => true, 'message' => "{$category->name} updated."]);
    }

    public function destroyCategory(MenuCategory $category): JsonResponse
    {
        if ($category->items()->exists()) {
            return response()->json([
                'success' => false,
                'message' => "{$category->name} still has dishes. Move or delete them first, or hide the category.",
            ], 422);
        }

        $category->delete();

        return response()->json(['success' => true, 'message' => "{$category->name} deleted."]);
    }

    // ---- Dishes ------------------------------------------------------------

    public function storeItem(Request $request): JsonResponse
    {
        $data = $this->validatedItem($request);

        $item = DB::transaction(function () use ($data) {
            $item = MenuItem::create($data['item']);
            $item->variants()->createMany($data['variants']);

            return $item;
        });

        return response()->json(['success' => true, 'message' => "{$item->name} added."], 201);
    }

    public function updateItem(Request $request, MenuItem $item): JsonResponse
    {
        $data = $this->validatedItem($request, $item);

        DB::transaction(function () use ($item, $data) {
            $item->update($data['item']);

            $keep = [];
            foreach ($data['variants'] as $variant) {
                $keep[] = isset($variant['id'])
                    ? tap($item->variants()->findOrFail($variant['id']))->update($variant)->id
                    : $item->variants()->create($variant)->id;
            }

            // Portions that were ordered before are hidden, not deleted, so order history keeps its names
            $removed = $item->variants()->whereNotIn('id', $keep);
            (clone $removed)->whereHas('kitchenOrderItems')->update(['is_active' => false]);
            $removed->whereDoesntHave('kitchenOrderItems')->delete();
        });

        return response()->json(['success' => true, 'message' => "{$item->name} updated."]);
    }

    public function destroyItem(MenuItem $item): JsonResponse
    {
        if ($item->variants()->whereHas('kitchenOrderItems')->exists()) {
            return response()->json([
                'success' => false,
                'message' => "{$item->name} has been ordered before and can't be deleted. Hide it instead.",
            ], 422);
        }

        $item->delete();

        return response()->json(['success' => true, 'message' => "{$item->name} deleted."]);
    }

    private function validatedCategory(Request $request, ?MenuCategory $category = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:100', Rule::unique('menu_categories', 'name')->ignore($category?->id)],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:1000'],
            'is_active' => ['boolean'],
        ]) + ['sort_order' => 0];
    }

    /**
     * @return array{item: array, variants: array}
     */
    private function validatedItem(Request $request, ?MenuItem $item = null): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'category_id' => ['required', 'exists:menu_categories,id'],
            'description' => ['nullable', 'string', 'max:1000'],
            'is_active' => ['boolean'],
            'variants' => ['required', 'array', 'min:1', 'max:10'],
            'variants.*.id' => ['nullable', 'integer', Rule::exists('menu_item_variants', 'id')->where('menu_item_id', $item?->id ?? 0)],
            'variants.*.name' => ['required', 'string', 'max:50', 'distinct'],
            'variants.*.price' => ['required', 'numeric', 'min:0', 'max:9999999'],
            'variants.*.preparation_time' => ['nullable', 'integer', 'min:0', 'max:600'],
            'variants.*.is_active' => ['boolean'],
        ], [
            'variants.required' => 'Give the dish at least one price.',
            'variants.*.name.distinct' => 'Each portion needs a different name.',
        ]);

        $variants = array_map(fn ($v) => array_filter(['id' => $v['id'] ?? null]) + [
            'name' => $v['name'],
            'price' => $v['price'],
            'preparation_time' => $v['preparation_time'] ?? null,
            'is_active' => $v['is_active'] ?? true,
        ], $data['variants']);

        unset($data['variants']);
        $data['is_active'] ??= true;

        return ['item' => $data, 'variants' => $variants];
    }
}

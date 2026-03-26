<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreProductRequest;
use App\Http\Requests\Admin\UpdateProductRequest;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProductController extends Controller
{
    public function index(Request $request): Response
    {
        $products = Product::with('category')
            ->when($request->input('search'), fn ($q, $search) => $q->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%");
            }))
            ->when($request->input('category'), fn ($q, $cat) => $q->where('category_id', $cat))
            ->when($request->input('stock') === 'low', fn ($q) => $q->whereColumn('stock_quantity', '<=', 'min_stock_level')->where('stock_quantity', '>', 0))
            ->when($request->input('stock') === 'out', fn ($q) => $q->where('stock_quantity', '<=', 0))
            ->latest()
            ->paginate(15)
            ->withQueryString()
            ->through(fn (Product $product) => [
                'id' => $product->id,
                'name' => $product->name,
                'sku' => $product->sku,
                'category' => $product->category->name,
                'cost_price' => $product->cost_price,
                'selling_price' => $product->selling_price,
                'stock_quantity' => $product->stock_quantity,
                'min_stock_level' => $product->min_stock_level,
                'is_low_stock' => $product->is_low_stock,
                'is_active' => $product->is_active,
                'image' => $product->getFirstMediaUrl('images', 'thumb') ?: null,
                'created_at' => $product->created_at->format('Y-m-d H:i'),
            ]);

        return Inertia::render('Admin/Products/Index', [
            'products' => $products,
            'categories' => Category::pluck('name', 'id'),
            'filters' => $request->only(['search', 'category', 'stock']),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Products/Create', [
            'categories' => Category::all(['id', 'name']),
        ]);
    }

    public function store(StoreProductRequest $request): RedirectResponse
    {
        $data = $request->validated();

        if (empty($data['sku'])) {
            $category = Category::findOrFail($data['category_id']);
            $data['sku'] = Product::generateSku($category);
        }

        $product = Product::create($data);

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $product->addMedia($image)->toMediaCollection('images');
            }
        }

        return redirect()->route('admin.products.index')
            ->with('success', 'Product created successfully.');
    }

    public function edit(Product $product): Response
    {
        return Inertia::render('Admin/Products/Edit', [
            'product' => [
                'id' => $product->id,
                'category_id' => $product->category_id,
                'name' => $product->name,
                'description' => $product->description,
                'sku' => $product->sku,
                'barcode' => $product->barcode,
                'cost_price' => $product->cost_price,
                'selling_price' => $product->selling_price,
                'stock_quantity' => $product->stock_quantity,
                'min_stock_level' => $product->min_stock_level,
                'unit' => $product->unit,
                'is_active' => $product->is_active,
                'images' => $product->getMedia('images')->map(fn ($media) => [
                    'id' => $media->id,
                    'url' => $media->getUrl(),
                    'name' => $media->file_name,
                ]),
            ],
            'categories' => Category::all(['id', 'name']),
        ]);
    }

    public function update(UpdateProductRequest $request, Product $product): RedirectResponse
    {
        $product->update($request->validated());

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $product->addMedia($image)->toMediaCollection('images');
            }
        }

        if ($request->has('remove_images')) {
            foreach ($request->input('remove_images') as $mediaId) {
                $product->media()->where('id', $mediaId)->first()?->delete();
            }
        }

        return redirect()->route('admin.products.index')
            ->with('success', 'Product updated successfully.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        if ($product->serialNumbers()->exists()) {
            return back()->with('error', 'Cannot delete product with serial numbers.');
        }

        $product->delete();

        return redirect()->route('admin.products.index')
            ->with('success', 'Product deleted successfully.');
    }
}

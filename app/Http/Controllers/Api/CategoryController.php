
<?php

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
 

class CategoryController extends Controller
{
    // Error response helper
    protected function errorResponse($message, $code = 400, $errors = null)
    {
        $response = [
            'success' => false,
            'message' => $message,
        ];
        if ($errors) {
            $response['errors'] = $errors;
        }
        return response()->json($response, $code);
    }

    // Override failed authorization and validation responses for JSON error format
    protected function failedAuthorization()
    {
        return $this->errorResponse('Accés no permès', 403);
    }

    protected function failedValidation($validator)
    {
        return $this->errorResponse('Dades incorrectes', 422, $validator->errors());
    }
    public function index()
    {
        $perPage = (int) request('per_page', 20);
        $sortDirection = request('sort', 'desc');
        $query = Category::query();

        // Filtre per nom
        if (request('name')) {
            $query->where('name', 'like', '%'.request('name').'%');
        }

        // Filtre per posts publicats (si existeix relació posts)
        if (request('published_posts')) {
            $query->whereHas('posts', function($q) {
                $q->where('published', true);
            });
        }

        // Eager loading de relacions (posts, user)
        $query->with(['posts', 'user']);

        // Exemple de join (si existeix taula users)
        if (request('user_id')) {
            $query->join('users', 'categories.user_id', '=', 'users.id')
                  ->where('users.id', request('user_id'));
        }

        $categories = $query
            ->orderBy('created_at', $sortDirection)
            ->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => CategoryResource::collection($categories),
            'links' => [
                'first' => $categories->url(1),
                'last' => $categories->url($categories->lastPage()),
                'prev' => $categories->previousPageUrl(),
                'next' => $categories->nextPageUrl(),
            ],
            'meta' => [
                'current_page' => $categories->currentPage(),
                'from' => $categories->firstItem(),
                'last_page' => $categories->lastPage(),
                'per_page' => $categories->perPage(),
                'to' => $categories->lastItem(),
                'total' => $categories->total(),
            ]
        ], 200);
    }

    public function store(StoreCategoryRequest $request)
    {
        $this->authorize('create', Category::class);
        $category = Category::create($request->validated());
        if ($request->hasFile('image')) {
            $category->addMediaFromRequest('image')->toMediaCollection('images');
        }
        return response()->json([
            'success' => true,
            'data' => new CategoryResource($category)
        ], 201);
    }

    public function show(Category $category)
    {
        $this->authorize('view', $category);
        if (!$category) {
            return $this->errorResponse('Categoria no trobada', 404);
        }
        return response()->json([
            'success' => true,
            'data' => new CategoryResource($category)
        ], 200);
    }

    public function update(Category $category, UpdateCategoryRequest $request)
    {
        $this->authorize('update', $category);
        $category->update($request->validated());
        if ($request->hasFile('image')) {
            $category->addMediaFromRequest('image')->toMediaCollection('images');
        }
        return response()->json([
            'success' => true,
            'data' => new CategoryResource($category)
        ], 200);
    }

    public function destroy(Category $category)
    {
        $this->authorize('delete', $category);
        $category->delete();
        return response()->json([
            'success' => true,
            'data' => null
        ], 204);
    }

    public function getList()
    {
        return response()->json([
            'success' => true,
            'data' => CategoryResource::collection(Category::all())
        ], 200);
    }
}

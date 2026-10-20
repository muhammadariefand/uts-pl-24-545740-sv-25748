<?php

namespace App\Http\Controllers;

use App\Models\Book;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class BookController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        $books = Book::latest()->get();

        return response()->json([
            'status' => 'success',
            'data' => $books,
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'author' => ['required', 'string', 'max:255'],
            'isbn' => ['required', 'string', 'max:50', 'unique:books,isbn'],
            'category' => ['required', 'string', 'max:100'],
            'published_year' => ['required', 'integer', 'min:1000', 'max:'.(date('Y') + 1)],
            'stock' => ['required', 'integer', 'min:0'],
            'description' => ['nullable', 'string'],
        ]);

        $book = Book::create($validated);

        return response()->json([
            'message' => 'Book created successfully',
            'data' => $book,
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): JsonResponse
    {
        $book = Book::find($id);

        if (! $book) {
            return response()->json([
                'message' => 'Book not found',
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $book,
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $book = Book::find($id);

        if (! $book) {
            return response()->json([
                'message' => 'Book not found',
            ], 404);
        }

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'author' => ['required', 'string', 'max:255'],
            'isbn' => ['required', 'string', 'max:50', Rule::unique('books', 'isbn')->ignore($book->id)],
            'category' => ['required', 'string', 'max:100'],
            'published_year' => ['required', 'integer', 'min:1000', 'max:'.(date('Y') + 1)],
            'stock' => ['required', 'integer', 'min:0'],
            'description' => ['nullable', 'string'],
        ]);

        $book->update($validated);

        return response()->json([
            'message' => 'Book updated successfully',
            'data' => $book,
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): JsonResponse
    {
        $book = Book::find($id);

        if (! $book) {
            return response()->json([
                'message' => 'Book not found',
            ], 404);
        }

        $book->delete();

        return response()->json([
            'message' => 'Book deleted successfully',
        ], 200);
    }
}

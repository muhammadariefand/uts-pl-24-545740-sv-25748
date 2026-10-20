<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_unauthenticated_user_cannot_access_books_endpoints(): void
    {
        $response = $this->getJson('/api/books');
        $response->assertStatus(401);

        $response = $this->postJson('/api/books', [
            'title' => 'Test',
        ]);
        $response->assertStatus(401);
    }

    public function test_authenticated_user_can_list_books(): void
    {
        $user = User::factory()->create();
        Book::factory()->count(3)->create();

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/books');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'data' => [
                    '*' => ['id', 'title', 'author', 'isbn', 'category', 'published_year', 'stock'],
                ],
            ])
            ->assertJsonCount(3, 'data');
    }

    public function test_authenticated_user_can_create_book_with_valid_data(): void
    {
        $user = User::factory()->create();

        $bookPayload = [
            'title' => 'Clean Architecture',
            'author' => 'Robert C. Martin',
            'isbn' => '978-0134494166',
            'category' => 'Software Engineering',
            'published_year' => 2017,
            'stock' => 10,
            'description' => 'A Craftsman\'s Guide to Software Structure and Design.',
        ];

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/books', $bookPayload);

        $response->assertStatus(201)
            ->assertJson([
                'message' => 'Book created successfully',
                'data' => [
                    'title' => 'Clean Architecture',
                    'isbn' => '978-0134494166',
                ],
            ]);

        $this->assertDatabaseHas('books', [
            'isbn' => '978-0134494166',
        ]);
    }

    public function test_book_creation_validation_fails_with_missing_required_fields(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/books', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['title', 'author', 'isbn', 'category', 'published_year', 'stock']);
    }

    public function test_book_creation_fails_with_duplicate_isbn(): void
    {
        $user = User::factory()->create();
        Book::factory()->create(['isbn' => '978-1111111111']);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/books', [
                'title' => 'Duplicate Book',
                'author' => 'Some Author',
                'isbn' => '978-1111111111',
                'category' => 'Science',
                'published_year' => 2022,
                'stock' => 5,
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['isbn']);
    }

    public function test_authenticated_user_can_view_single_book(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/books/{$book->id}");

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'data' => [
                    'id' => $book->id,
                    'title' => $book->title,
                    'isbn' => $book->isbn,
                ],
            ]);
    }

    public function test_viewing_nonexistent_book_returns_404(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/books/99999');

        $response->assertStatus(404)
            ->assertJson(['message' => 'Book not found']);
    }

    public function test_authenticated_user_can_update_book(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create([
            'title' => 'Old Title',
            'stock' => 5,
        ]);

        $updatePayload = [
            'title' => 'Updated Title',
            'author' => $book->author,
            'isbn' => $book->isbn,
            'category' => $book->category,
            'published_year' => $book->published_year,
            'stock' => 15,
            'description' => 'Updated description',
        ];

        $response = $this->actingAs($user, 'sanctum')
            ->putJson("/api/books/{$book->id}", $updatePayload);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Book updated successfully',
                'data' => [
                    'id' => $book->id,
                    'title' => 'Updated Title',
                    'stock' => 15,
                ],
            ]);

        $this->assertDatabaseHas('books', [
            'id' => $book->id,
            'title' => 'Updated Title',
            'stock' => 15,
        ]);
    }

    public function test_authenticated_user_can_delete_book(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->deleteJson("/api/books/{$book->id}");

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Book deleted successfully',
            ]);

        $this->assertDatabaseMissing('books', [
            'id' => $book->id,
        ]);
    }
}

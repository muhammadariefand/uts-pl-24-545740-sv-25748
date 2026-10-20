<?php

namespace Database\Seeders;

use App\Models\Book;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Default admin user for API testing and frontend login
        User::updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin Library',
                'password' => Hash::make('password123'),
            ]
        );

        // Initial sample books
        $sampleBooks = [
            [
                'title' => 'Clean Code: A Handbook of Agile Software Craftsmanship',
                'author' => 'Robert C. Martin',
                'isbn' => '978-0132350884',
                'category' => 'Software Engineering',
                'published_year' => 2008,
                'stock' => 12,
                'description' => 'Even bad code can function. But if code isn\'t clean, it can bring a development organization to its knees.',
            ],
            [
                'title' => 'Design Patterns: Elements of Reusable Object-Oriented Software',
                'author' => 'Erich Gamma, Richard Helm, Ralph Johnson, John Vlissides',
                'isbn' => '978-0201633610',
                'category' => 'Software Engineering',
                'published_year' => 1994,
                'stock' => 8,
                'description' => 'Capturing a wealth of experience about the design of object-oriented software by four top-notch designers.',
            ],
            [
                'title' => 'Refactoring: Improving the Design of Existing Code',
                'author' => 'Martin Fowler',
                'isbn' => '978-0134757599',
                'category' => 'Software Engineering',
                'published_year' => 2018,
                'stock' => 15,
                'description' => 'Fully updated to reflect the new realities of modern programming, with code examples in JavaScript.',
            ],
            [
                'title' => 'The Pragmatic Programmer: Your Journey To Mastery',
                'author' => 'David Thomas, Andrew Hunt',
                'isbn' => '978-0135957059',
                'category' => 'Computer Science',
                'published_year' => 2019,
                'stock' => 20,
                'description' => 'One of the most significant books in computer programming, exploring core craftsmanship topics.',
            ],
            [
                'title' => 'Domain-Driven Design: Tackling Complexity in the Heart of Software',
                'author' => 'Eric Evans',
                'isbn' => '978-0321125217',
                'category' => 'Software Architecture',
                'published_year' => 2003,
                'stock' => 6,
                'description' => 'Provides a systematic approach to domain-driven design, presenting an extensive set of modeling best practices.',
            ],
        ];

        foreach ($sampleBooks as $book) {
            Book::updateOrCreate(
                ['isbn' => $book['isbn']],
                $book
            );
        }
    }
}

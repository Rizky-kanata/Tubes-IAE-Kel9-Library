<?php

namespace Database\Seeders;

use App\Models\Book;
use Illuminate\Database\Seeder;

class BookSeeder extends Seeder
{
    public function run(): void
    {
        $books = [
            // Programming Books
            [
                'title' => 'Laravel: Up & Running',
                'author' => 'Matt Stauffer',
                'isbn' => '978-1492041207',
                'publisher' => "O'Reilly Media",
                'publication_year' => 2019,
                'total_copies' => 5,
                'available_copies' => 5,
                'description' => 'A comprehensive guide to Laravel framework',
                'price' => 450000,
                'language' => 'English',
                'pages' => 550,
                'categories' => [1], // Programming
            ],
            [
                'title' => 'Clean Code',
                'author' => 'Robert C. Martin',
                'isbn' => '978-0132350884',
                'publisher' => 'Prentice Hall',
                'publication_year' => 2008,
                'total_copies' => 10,
                'available_copies' => 10,
                'description' => 'A handbook of agile software craftsmanship',
                'price' => 550000,
                'language' => 'English',
                'pages' => 464,
                'categories' => [1], // Programming
            ],
            [
                'title' => 'JavaScript: The Good Parts',
                'author' => 'Douglas Crockford',
                'isbn' => '978-0596517748',
                'publisher' => "O'Reilly Media",
                'publication_year' => 2008,
                'total_copies' => 7,
                'available_copies' => 7,
                'description' => 'Unearthing the excellence in JavaScript',
                'price' => 350000,
                'language' => 'English',
                'pages' => 176,
                'categories' => [1], // Programming
            ],

            // Fiction Books
            [
                'title' => 'The Great Gatsby',
                'author' => 'F. Scott Fitzgerald',
                'isbn' => '978-0743273565',
                'publisher' => 'Scribner',
                'publication_year' => 1925,
                'total_copies' => 8,
                'available_copies' => 8,
                'description' => 'A classic American novel',
                'price' => 200000,
                'language' => 'English',
                'pages' => 180,
                'categories' => [2], // Fiction
            ],
            [
                'title' => '1984',
                'author' => 'George Orwell',
                'isbn' => '978-0451524935',
                'publisher' => 'Signet Classic',
                'publication_year' => 1949,
                'total_copies' => 6,
                'available_copies' => 6,
                'description' => 'A dystopian social science fiction novel',
                'price' => 220000,
                'language' => 'English',
                'pages' => 328,
                'categories' => [2], // Fiction
            ],

            // Science Books
            [
                'title' => 'A Brief History of Time',
                'author' => 'Stephen Hawking',
                'isbn' => '978-0553380163',
                'publisher' => 'Bantam',
                'publication_year' => 1988,
                'total_copies' => 4,
                'available_copies' => 4,
                'description' => 'From the Big Bang to Black Holes',
                'price' => 300000,
                'language' => 'English',
                'pages' => 256,
                'categories' => [3], // Science
            ],
            [
                'title' => 'Sapiens',
                'author' => 'Yuval Noah Harari',
                'isbn' => '978-0062316097',
                'publisher' => 'Harper',
                'publication_year' => 2015,
                'total_copies' => 9,
                'available_copies' => 9,
                'description' => 'A Brief History of Humankind',
                'price' => 380000,
                'language' => 'English',
                'pages' => 443,
                'categories' => [3, 4], // Science & History
            ],

            // Business Books
            [
                'title' => 'The Lean Startup',
                'author' => 'Eric Ries',
                'isbn' => '978-0307887894',
                'publisher' => 'Crown Business',
                'publication_year' => 2011,
                'total_copies' => 5,
                'available_copies' => 5,
                'description' => 'How Today\'s Entrepreneurs Use Continuous Innovation',
                'price' => 320000,
                'language' => 'English',
                'pages' => 336,
                'categories' => [7], // Business
            ],
            [
                'title' => 'Zero to One',
                'author' => 'Peter Thiel',
                'isbn' => '978-0804139298',
                'publisher' => 'Crown Business',
                'publication_year' => 2014,
                'total_copies' => 6,
                'available_copies' => 6,
                'description' => 'Notes on Startups, or How to Build the Future',
                'price' => 340000,
                'language' => 'English',
                'pages' => 224,
                'categories' => [7], // Business
            ],

            // Self-Help Books
            [
                'title' => 'Atomic Habits',
                'author' => 'James Clear',
                'isbn' => '978-0735211292',
                'publisher' => 'Avery',
                'publication_year' => 2018,
                'total_copies' => 12,
                'available_copies' => 12,
                'description' => 'An Easy & Proven Way to Build Good Habits',
                'price' => 280000,
                'language' => 'English',
                'pages' => 320,
                'categories' => [6], // Self-Help
            ],
        ];

        foreach ($books as $bookData) {
            // Pisahkan categories dari data book
            $categories = $bookData['categories'];
            unset($bookData['categories']);

            // Buat book
            $book = Book::create($bookData);

            // Attach categories ke book (many-to-many relationship)
            $book->categories()->attach($categories);
        }
    }
}

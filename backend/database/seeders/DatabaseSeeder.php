<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Book;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        // Admin User
        User::create([
            'name' => 'Super Admin',
            'email' => 'admin@perpus.com',
            'password' => Hash::make('admin123'),
            'role' => 'admin'
        ]);

        // Member Users
        User::create([
            'name' => 'Adit',
            'email' => 'adit@test.com',
            'password' => Hash::make('adit123'),
            'role' => 'member'
        ]);

        User::create([
            'name' => 'Fikri',
            'email' => 'fikri@test.com',
            'password' => Hash::make('fikri123'),
            'role' => 'member'
        ]);

        User::create([
            'name' => 'Ardyn',
            'email' => 'ardyn@test.com',
            'password' => Hash::make('ardyn123'),
            'role' => 'member'
        ]);

        // Books Data
        $books = [
            ['Laravel 10 Complete Guide', 'Taylor Otwell', '9781234567890', 'Laravel LLC', 2024, 15, 15],
            ['PHP 8 Mastery', 'Rasmus Lerdorf', '9780987654321', 'PHP Foundation', 2023, 10, 10],
            ['Vue.js 3 Essentials', 'Evan You', '9781122334455', 'Vue Press', 2024, 12, 12],
            ['React Native Mobile', 'Meta Team', '9785566778899', 'Meta', 2024, 8, 8],
            ['Docker Containerization', 'Docker Inc', '9789988776655', 'Docker', 2023, 5, 5],
            ['MySQL Database Admin', 'Oracle Corp', '9781231231234', 'Oracle', 2024, 20, 20],
            ['REST API Design Patterns', 'Roy Fielding', '9784564564567', 'O\'Reilly', 2023, 7, 7],
            ['Git Version Control', 'Linus Torvalds', '9787897897890', 'Git SCM', 2024, 10, 10],
            ['JavaScript ES6+', 'Brendan Eich', '9783213213210', 'Mozilla', 2024, 9, 9],
            ['Python Data Science', 'Guido van Rossum', '9786546546549', 'Python.org', 2023, 11, 11],
            ['Java Spring Boot', 'Pivotal Team', '9781357913579', 'Spring', 2024, 6, 6],
            ['Node.js Backend', 'Ryan Dahl', '9782468024680', 'Node Foundation', 2023, 8, 8],
            ['Flutter Mobile Dev', 'Google Team', '9783691258247', 'Google', 2024, 10, 10],
            ['MongoDB NoSQL', 'MongoDB Inc', '9784826048260', 'MongoDB', 2023, 7, 7],
            ['Redis Caching', 'Redis Labs', '9785937159371', 'Redis', 2024, 5, 5],
        ];

        foreach ($books as $book) {
            Book::create([
                'title' => $book[0],
                'author' => $book[1],
                'isbn' => $book[2],
                'publisher' => $book[3],
                'year' => $book[4],
                'total_stock' => $book[5],
                'available_stock' => $book[6]
            ]);
        }
    }
}

<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Programming', 'description' => 'Programming and software development books', 'icon' => '💻'],
            ['name' => 'Fiction', 'description' => 'Fictional novels and stories', 'icon' => '📖'],
            ['name' => 'Science', 'description' => 'Scientific books and research', 'icon' => '🔬'],
            ['name' => 'History', 'description' => 'Historical books and documentation', 'icon' => '📜'],
            ['name' => 'Biography', 'description' => 'Biographies and memoirs', 'icon' => '👤'],
            ['name' => 'Self-Help', 'description' => 'Self-improvement and motivational books', 'icon' => '💪'],
            ['name' => 'Business', 'description' => 'Business and economics books', 'icon' => '💼'],
            ['name' => 'Art & Design', 'description' => 'Art, design, and creativity books', 'icon' => '🎨'],
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }
    }
}

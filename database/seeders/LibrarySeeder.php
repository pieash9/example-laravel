<?php

namespace Database\Seeders;

use App\Models\Author;
use App\Models\Book;
use App\Models\Borrow;
use App\Models\Category;
use App\Models\Member;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class LibrarySeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = collect([
            ['name' => 'Software Engineering', 'description' => 'System design, architecture, and coding craft.'],
            ['name' => 'Data Science', 'description' => 'Analytics, statistics, and machine learning.'],
            ['name' => 'Business', 'description' => 'Management, strategy, and leadership.'],
            ['name' => 'Design', 'description' => 'UX, product design, and visual thinking.'],
            ['name' => 'Fiction', 'description' => 'Curated modern and classic novels.'],
            ['name' => 'Biography', 'description' => 'Lives of innovators and creators.'],
        ])->map(fn (array $data) => Category::query()->updateOrCreate(['name' => $data['name']], $data));

        $authors = collect([
            ['name' => 'Robert C. Martin', 'country' => 'USA'],
            ['name' => 'Martin Fowler', 'country' => 'UK'],
            ['name' => 'Kathy Sierra', 'country' => 'USA'],
            ['name' => 'Cal Newport', 'country' => 'USA'],
            ['name' => 'Yuval Noah Harari', 'country' => 'Israel'],
            ['name' => 'Margaret Atwood', 'country' => 'Canada'],
            ['name' => 'Neil Gaiman', 'country' => 'UK'],
            ['name' => 'J.K. Rowling', 'country' => 'UK'],
            ['name' => 'Seth Godin', 'country' => 'USA'],
            ['name' => 'Donald Norman', 'country' => 'USA'],
        ])->map(fn (array $data) => Author::query()->updateOrCreate(['name' => $data['name']], $data));

        $admin = User::query()->updateOrCreate(
            ['email' => 'admin@library.test'],
            ['name' => 'System Admin', 'password' => Hash::make('password123'), 'role' => User::ROLE_ADMIN]
        );
        $staff = User::query()->updateOrCreate(
            ['email' => 'staff@library.test'],
            ['name' => 'Circulation Staff', 'password' => Hash::make('password123'), 'role' => User::ROLE_STAFF]
        );

        $memberUser = User::query()->updateOrCreate(
            ['email' => 'member@library.test'],
            ['name' => 'Demo Member', 'password' => Hash::make('password123'), 'role' => User::ROLE_MEMBER]
        );

        $this->createMemberProfile($memberUser, 1);
        $this->createMemberProfile($admin, 2, false);
        $this->createMemberProfile($staff, 3, false);

        $extraMembers = User::factory()
            ->count(18)
            ->create(['role' => User::ROLE_MEMBER]);

        $extraMembers->each(function (User $user, int $idx): void {
            $this->createMemberProfile($user, $idx + 4);
        });

        $bookTitles = [
            'Clean Architecture', 'Refactoring Patterns', 'Deep Work', 'The Design of Everyday Things',
            'Atomic Habits', 'The Lean Startup', 'Thinking in Systems', 'Pragmatic Testing',
            'Applied Machine Learning', 'Narrative and Numbers', 'Ocean of Stars', 'Shadow and Light',
            'The Last Algorithm', 'Leading with Clarity', 'Human-Centered Interfaces', 'The Silent Archive',
            'Codebase Economics', 'Practical DDD', 'Cloud Native Strategies', 'Modern API Security',
            'Story of Civilizations', 'The Product Operator', 'Compilers in Practice', 'Advanced SQL Workflows',
            'Sustainable Teams', 'Critical Thinking Toolkit', 'Design for Inclusion', 'Parallel Minds',
            'Business Forecasting', 'Network Effects Handbook',
        ];

        collect($bookTitles)->each(function (string $title, int $index) use ($categories, $authors): void {
            $book = Book::query()->updateOrCreate(
                ['isbn' => 'ISBN-'.str_pad((string) ($index + 1), 6, '0', STR_PAD_LEFT)],
                [
                    'title' => $title,
                    'category_id' => $categories->random()->id,
                    'published_year' => random_int(1995, 2025),
                    'copies' => random_int(2, 8),
                ]
            );

            $book->authors()->sync(
                $authors->random(random_int(1, 3))->pluck('id')->all()
            );
        });

        $members = Member::query()->whereNotNull('user_id')->get();
        $books = Book::all();

        for ($i = 0; $i < 140; $i++) {
            $book = $books->random();
            $member = $members->random();
            $status = fake()->randomElement([
                Borrow::STATUS_PENDING,
                Borrow::STATUS_APPROVED,
                Borrow::STATUS_APPROVED,
                Borrow::STATUS_RETURNED,
                Borrow::STATUS_RETURNED,
                Borrow::STATUS_REJECTED,
            ]);

            $borrowDate = now()->copy()->subDays(random_int(1, 180));
            $dueDate = $borrowDate->copy()->addDays(random_int(7, 21));
            $returnDate = null;
            $returned = false;
            $processedBy = null;
            $processedAt = null;
            $processedNote = null;

            if ($status === Borrow::STATUS_RETURNED) {
                $returnDate = $dueDate->copy()->subDays(random_int(0, 5));
                if ($returnDate->lessThan($borrowDate)) {
                    $returnDate = $borrowDate->copy()->addDays(random_int(1, 4));
                }
                $returned = true;
            }

            if ($status === Borrow::STATUS_PENDING) {
                $borrowDate = now()->copy()->subDays(random_int(0, 3));
                $dueDate = now()->copy()->addDays(random_int(7, 20));
            }

            if ($status !== Borrow::STATUS_PENDING) {
                $processedBy = fake()->randomElement([$admin->id, $staff->id]);
                $processedAt = now()->subDays(random_int(0, 30));
                $processedNote = fake()->optional(0.5)->sentence();
            }

            Borrow::query()->create([
                'book_id' => $book->id,
                'member_id' => $member->id,
                'borrow_date' => $borrowDate->toDateString(),
                'due_date' => $dueDate->toDateString(),
                'return_date' => $returnDate?->toDateString(),
                'returned' => $returned,
                'status' => $status,
                'requested_note' => fake()->optional(0.4)->sentence(),
                'processed_note' => $processedNote,
                'processed_by' => $processedBy,
                'processed_at' => $processedAt,
            ]);
        }
    }

    private function createMemberProfile(User $user, int $sequence, bool $allowUpdate = true): void
    {
        $payload = [
            'user_id' => $user->id,
            'membership_no' => 'LIB-'.now()->format('Y').'-'.str_pad((string) $sequence, 4, '0', STR_PAD_LEFT),
            'name' => $user->name,
            'email' => $user->email,
            'phone' => '+1-555-'.str_pad((string) random_int(1000, 9999), 4, '0', STR_PAD_LEFT),
            'address' => fake()->streetAddress(),
        ];

        if ($allowUpdate) {
            Member::query()->updateOrCreate(['user_id' => $user->id], $payload);

            return;
        }

        Member::query()->firstOrCreate(['user_id' => $user->id], $payload);
    }
}

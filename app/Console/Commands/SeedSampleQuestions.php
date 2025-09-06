<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SeedSampleQuestions extends Command
{
    protected $signature = 'student:seed-questions {--force : Insert even if some exist}';
    protected $description = 'Seed a set of sample Aptitude and Technical questions into Supabase Postgres';

    public function handle(): int
    {
        $cats = DB::table('categories')->whereIn('name', ['Aptitude','Technical'])->pluck('id', 'name');
        $aptId = $cats['Aptitude'] ?? null;
        $techId = $cats['Technical'] ?? null;

        if (!$aptId && !$techId) {
            $this->error('Categories Aptitude/Technical not found. Create them first.');
            return Command::FAILURE;
        }

        $rows = [];
        if ($aptId) {
            $rows = array_merge($rows, [
                [ 'category_id' => $aptId, 'question' => 'What is 25% of 240?', 'options' => json_encode(['48','50','52','60']), 'correct_option' => '0' ],
                [ 'category_id' => $aptId, 'question' => 'Ratio of 3:4 equals?', 'options' => json_encode(['0.65','0.66','0.75','0.8']), 'correct_option' => '2' ],
                [ 'category_id' => $aptId, 'question' => 'Simple interest on 1000 at 10% for 2 years?', 'options' => json_encode(['100','150','200','250']), 'correct_option' => '2' ],
            ]);
        }
        if ($techId) {
            $rows = array_merge($rows, [
                [ 'category_id' => $techId, 'question' => 'Time complexity of binary search?', 'options' => json_encode(['O(n)','O(log n)','O(n log n)','O(1)']), 'correct_option' => '1' ],
                [ 'category_id' => $techId, 'question' => 'HTTP is built on which protocol?', 'options' => json_encode(['TCP','UDP','ICMP','IP']), 'correct_option' => '0' ],
                [ 'category_id' => $techId, 'question' => 'Which SQL command creates a table?', 'options' => json_encode(['MAKE TABLE','CREATE TABLE','NEW TABLE','ADD TABLE']), 'correct_option' => '1' ],
            ]);
        }

        $inserted = 0;
        foreach ($rows as $r) {
            $exists = DB::table('questions')
                ->where('category_id', $r['category_id'])
                ->where('question', $r['question'])
                ->exists();
            if ($exists && !$this->option('force')) { continue; }
            DB::table('questions')->insert($r);
            $inserted++;
        }

        $this->info("Inserted {$inserted} questions.");
        return Command::SUCCESS;
    }
}



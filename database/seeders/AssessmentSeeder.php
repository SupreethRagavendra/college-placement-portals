<?php

namespace Database\Seeders;

use App\Models\Assessment;
use App\Models\Question;
use Illuminate\Database\Seeder;

class AssessmentSeeder extends Seeder
{
    public function run(): void
    {
        // Create sample assessments
        $aptitudeAssessment = Assessment::create([
            'name' => 'General Aptitude Test',
            'description' => 'A comprehensive aptitude test covering logical reasoning, mathematics, and verbal ability.',
            'category' => 'Aptitude',
            'time_limit' => 30,
            'is_active' => true,
        ]);

        $technicalAssessment = Assessment::create([
            'name' => 'Programming Fundamentals',
            'description' => 'Basic programming concepts and problem-solving skills.',
            'category' => 'Technical',
            'time_limit' => 45,
            'is_active' => true,
        ]);

        // Create sample questions for Aptitude
        $aptitudeQuestions = [
            [
                'question' => 'If 2x + 3 = 11, what is the value of x?',
                'options' => ['2', '4', '6', '8'],
                'correct_option' => 1,
                'category_id' => 1, // Aptitude
            ],
            [
                'question' => 'Which number comes next in the sequence: 2, 6, 12, 20, ?',
                'options' => ['28', '30', '32', '34'],
                'correct_option' => 1,
                'category_id' => 1, // Aptitude
            ],
            [
                'question' => 'If all roses are flowers and some flowers are red, which statement is correct?',
                'options' => ['All roses are red', 'Some roses are red', 'No roses are red', 'Cannot be determined'],
                'correct_option' => 3,
                'category_id' => 1, // Aptitude
            ],
        ];

        // Create sample questions for Technical
        $technicalQuestions = [
            [
                'question' => 'Which of the following is a valid PHP variable name?',
                'options' => ['$2variable', '$variable2', '$var-name', '$var name'],
                'correct_option' => 1,
                'category_id' => 2, // Technical
            ],
            [
                'question' => 'What does OOP stand for in programming?',
                'options' => ['Object Oriented Programming', 'Online Optimization Process', 'Operational Output Program', 'Organized Object Procedure'],
                'correct_option' => 0,
                'category_id' => 2, // Technical
            ],
            [
                'question' => 'Which data structure follows LIFO (Last In First Out) principle?',
                'options' => ['Queue', 'Stack', 'Array', 'Linked List'],
                'correct_option' => 1,
                'category_id' => 2, // Technical
            ],
        ];

        // Create questions and link to assessments
        foreach ($aptitudeQuestions as $questionData) {
            $question = Question::create($questionData);
            $aptitudeAssessment->questions()->attach($question->id);
        }

        foreach ($technicalQuestions as $questionData) {
            $question = Question::create($questionData);
            $technicalAssessment->questions()->attach($question->id);
        }
    }
}
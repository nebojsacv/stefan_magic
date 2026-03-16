<?php

namespace Database\Seeders;

use App\Models\ClassificationQuestion;
use Illuminate\Database\Seeder;

class ClassificationQuestionsSeeder extends Seeder
{
    public function run(): void
    {
        ClassificationQuestion::truncate();

        $questions = [
            [
                'key' => 'q1',
                'label' => 'Criticality',
                'description' => 'If this vendor becomes unavailable, will your organization\'s operations be significantly impacted?',
                'triggers_tier' => 'high',
                'order_index' => 1,
            ],
            [
                'key' => 'q2',
                'label' => 'Personal Data Processing',
                'description' => 'Does this vendor process personal data on your behalf?',
                'triggers_tier' => 'medium',
                'order_index' => 2,
            ],
            [
                'key' => 'q3',
                'label' => 'Confidential Business Information',
                'description' => 'Does this vendor process or store confidential or sensitive business information?',
                'triggers_tier' => 'medium',
                'order_index' => 3,
            ],
            [
                'key' => 'q4',
                'label' => 'Software & ICT Service Delivery',
                'description' => 'Does this vendor provide software, an application, or ICT platform that supports your processes?',
                'triggers_tier' => 'medium',
                'order_index' => 4,
            ],
            [
                'key' => 'q5',
                'label' => 'Operational / Legal / Reputational Impact',
                'description' => 'Would a failure or security incident in this vendor cause significant operational, legal or reputational harm?',
                'triggers_tier' => 'high',
                'order_index' => 5,
            ],
        ];

        foreach ($questions as $question) {
            ClassificationQuestion::create($question);
        }
    }
}

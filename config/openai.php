<?php

return [

    /*
    |--------------------------------------------------------------------------
    | OpenAI API Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for OpenAI GPT-4 and GPT-4 Vision integration
    |
    */

    'api_key' => env('OPENAI_API_KEY'),

    'model' => env('OPENAI_MODEL', 'gpt-4-turbo-preview'),

    'vision_model' => env('OPENAI_VISION_MODEL', 'gpt-4-vision-preview'),

    'temperature' => env('OPENAI_TEMPERATURE', 0.3),

    'max_tokens' => env('OPENAI_MAX_TOKENS', 4096),

    /*
    |--------------------------------------------------------------------------
    | AI Cost Limits (USD per month per package tier)
    |--------------------------------------------------------------------------
    */

    'cost_limits' => [
        'free' => env('AI_COST_LIMIT_FREE', 0),
        'basic' => env('AI_COST_LIMIT_BASIC', 50),
        'pro' => env('AI_COST_LIMIT_PRO', 200),
        'enterprise' => env('AI_COST_LIMIT_ENTERPRISE', 1000),
    ],

    /*
    |--------------------------------------------------------------------------
    | AI Features Toggle
    |--------------------------------------------------------------------------
    */

    'features' => [
        'answer_analysis' => env('AI_FEATURE_ANSWER_ANALYSIS', true),
        'evidence_extraction' => env('AI_FEATURE_EVIDENCE_EXTRACTION', true),
        'risk_assessment' => env('AI_FEATURE_RISK_ASSESSMENT', true),
        'mitigation_plans' => env('AI_FEATURE_MITIGATION_PLANS', true),
        'approval_workflow' => env('AI_FEATURE_APPROVAL_WORKFLOW', false),
    ],

];

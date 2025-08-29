<?php

namespace Hubiko\AIContent\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Hubiko\AIContent\Entities\AIContent;
use Hubiko\AIContent\Entities\AITemplate;
use Hubiko\AIContent\Entities\AIUsage;

class AIService
{
    protected $provider;
    protected $apiKey;
    protected $baseUrl;
    protected $defaultModel;
    protected $providerConfig;

    public function __construct($provider = null)
    {
        $this->provider = $provider ?: config('ai-content.default_provider');
        $this->providerConfig = config("ai-content.providers.{$this->provider}");
        
        if (!$this->providerConfig) {
            throw new \Exception("AI provider '{$this->provider}' not configured");
        }
        
        $this->apiKey = $this->providerConfig['api_key'];
        $this->baseUrl = $this->providerConfig['base_url'];
        $this->defaultModel = $this->providerConfig['default_model'] ?? 'gpt-3.5-turbo';
    }

    /**
     * Generate content using AI
     */
    public function generateContent(array $params)
    {
        $startTime = microtime(true);

        try {
            $prompt = $this->buildPrompt($params);
            $model = $params['model'] ?? $this->defaultModel;
            
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])->timeout(60)->post($this->baseUrl . '/chat/completions', [
                'model' => $model,
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => $this->getSystemPrompt($params['content_type'])
                    ],
                    [
                        'role' => 'user',
                        'content' => $prompt
                    ]
                ],
                'max_tokens' => $this->getMaxTokens($params['length']),
                'temperature' => $this->getTemperature($params['tone']),
            ]);

            if (!$response->successful()) {
                throw new \Exception('AI API request failed: ' . $response->body());
            }

            $data = $response->json();
            $generationTime = microtime(true) - $startTime;

            return [
                'content' => $data['choices'][0]['message']['content'],
                'model' => $model,
                'tokens_used' => $data['usage']['total_tokens'] ?? 0,
                'generation_time' => round($generationTime, 2),
                'cost' => $this->calculateCost($data['usage']['total_tokens'] ?? 0, $model),
                'quality_score' => $this->calculateQualityScore($data['choices'][0]['message']['content'])
            ];

        } catch (\Exception $e) {
            Log::error('AI Content Generation Failed', [
                'error' => $e->getMessage(),
                'params' => $params
            ]);
            
            throw $e;
        }
    }

    /**
     * Build the prompt based on parameters
     */
    protected function buildPrompt(array $params)
    {
        $prompt = $params['prompt'];
        
        // Add content type context
        $prompt .= "\n\nContent Type: " . ucfirst(str_replace('_', ' ', $params['content_type']));
        
        // Add tone instruction
        $prompt .= "\nTone: " . ucfirst($params['tone']);
        
        // Add length instruction
        $lengthInstruction = $this->getLengthInstruction($params['length']);
        $prompt .= "\nLength: " . $lengthInstruction;
        
        // Add keywords if provided
        if (!empty($params['keywords'])) {
            $keywords = is_array($params['keywords']) ? implode(', ', $params['keywords']) : $params['keywords'];
            $prompt .= "\nKeywords to include: " . $keywords;
        }
        
        // Add language instruction
        $prompt .= "\nLanguage: " . ($params['language'] ?? 'English');
        
        return $prompt;
    }

    /**
     * Get system prompt based on content type
     */
    protected function getSystemPrompt($contentType)
    {
        $prompts = [
            'article' => 'You are a professional content writer specializing in creating informative and engaging articles. Write well-structured content with clear headings, proper flow, and valuable insights.',
            'blog_post' => 'You are a skilled blogger who creates engaging, conversational blog posts. Focus on connecting with readers, providing value, and maintaining an engaging tone throughout.',
            'social_media' => 'You are a social media content creator who understands how to craft compelling, shareable posts that engage audiences across different platforms.',
            'email' => 'You are an email marketing specialist who creates compelling email content that drives engagement and conversions while maintaining a personal connection.',
            'product_description' => 'You are a product copywriter who creates compelling, detailed product descriptions that highlight benefits and drive purchasing decisions.',
            'ad_copy' => 'You are an advertising copywriter who creates persuasive, attention-grabbing ad copy that converts prospects into customers.'
        ];

        return $prompts[$contentType] ?? 'You are a professional content writer who creates high-quality, engaging content tailored to specific requirements.';
    }

    /**
     * Get max tokens based on length
     */
    protected function getMaxTokens($length)
    {
        $tokens = [
            'short' => 500,
            'medium' => 1000,
            'long' => 2000
        ];

        return $tokens[$length] ?? 1000;
    }

    /**
     * Get temperature based on tone
     */
    protected function getTemperature($tone)
    {
        $temperatures = [
            'professional' => 0.3,
            'formal' => 0.2,
            'informative' => 0.4,
            'casual' => 0.7,
            'friendly' => 0.6,
            'persuasive' => 0.5
        ];

        return $temperatures[$tone] ?? 0.5;
    }

    /**
     * Get length instruction
     */
    protected function getLengthInstruction($length)
    {
        $instructions = [
            'short' => 'Write a concise piece of 100-300 words',
            'medium' => 'Write a moderate length piece of 300-600 words',
            'long' => 'Write a comprehensive piece of 600+ words'
        ];

        return $instructions[$length] ?? 'Write a moderate length piece';
    }

    /**
     * Calculate cost based on tokens and model
     */
    protected function calculateCost($tokens, $model)
    {
        $modelConfig = $this->providerConfig['models'][$model] ?? null;
        
        if ($modelConfig && isset($modelConfig['cost_per_1k_tokens'])) {
            $costPer1kTokens = $modelConfig['cost_per_1k_tokens'];
            return ($tokens / 1000) * $costPer1kTokens;
        }
        
        // Fallback pricing
        $costPerToken = 0.000002; // $0.002 per 1K tokens
        return $tokens * $costPerToken;
    }

    /**
     * Get available models for current provider
     */
    public function getAvailableModels()
    {
        return array_keys($this->providerConfig['models'] ?? []);
    }

    /**
     * Get provider name
     */
    public function getProvider()
    {
        return $this->provider;
    }

    /**
     * Calculate quality score based on content
     */
    protected function calculateQualityScore($content)
    {
        $score = 0;
        
        // Length factor
        $wordCount = str_word_count($content);
        if ($wordCount > 50) $score += 20;
        if ($wordCount > 200) $score += 20;
        
        // Structure factor (paragraphs)
        $paragraphs = count(array_filter(explode("\n\n", $content)));
        if ($paragraphs > 1) $score += 20;
        
        // Readability factor (sentence variety)
        $sentences = preg_split('/[.!?]+/', $content);
        $avgSentenceLength = $wordCount / max(count($sentences), 1);
        if ($avgSentenceLength > 10 && $avgSentenceLength < 25) $score += 20;
        
        // Engagement factor (questions, exclamations)
        if (preg_match('/[?!]/', $content)) $score += 20;
        
        return min($score, 100);
    }

    /**
     * Test AI connection
     */
    public function testConnection()
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
            ])->get($this->baseUrl . '/models');

            return $response->successful();
        } catch (\Exception $e) {
            return false;
        }
    }
}

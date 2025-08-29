<?php

namespace Hubiko\AIContent\Database\Seeders;

use Illuminate\Database\Seeder;
use Hubiko\AIContent\Entities\AITemplate;

class AIContentSeeder extends Seeder
{
    /**
     * Run the database seeder.
     */
    public function run(): void
    {
        $this->createSystemTemplates();
    }

    /**
     * Create system templates
     */
    private function createSystemTemplates()
    {
        $templates = [
            [
                'name' => 'Blog Post Template',
                'description' => 'Professional blog post template with engaging introduction and conclusion',
                'category' => 'content',
                'prompt_template' => 'Write a comprehensive blog post about {{topic}}. Include an engaging introduction, main points with examples, and a compelling conclusion. Target audience: {{audience}}. Focus on providing value and actionable insights.',
                'variables' => ['topic', 'audience'],
                'content_type' => 'blog_post',
                'default_tone' => 'friendly',
                'default_length' => 'medium',
                'is_active' => true,
                'is_system' => true,
                'workspace_id' => 1,
                'created_by' => 1
            ],
            [
                'name' => 'Product Description Template',
                'description' => 'Compelling product description template that highlights features and benefits',
                'category' => 'marketing',
                'prompt_template' => 'Create a compelling product description for {{product_name}}. Highlight key features: {{features}}. Focus on benefits for the customer. Include specifications if relevant and end with a strong call-to-action.',
                'variables' => ['product_name', 'features'],
                'content_type' => 'product_description',
                'default_tone' => 'persuasive',
                'default_length' => 'short',
                'is_active' => true,
                'is_system' => true,
                'workspace_id' => 1,
                'created_by' => 1
            ],
            [
                'name' => 'Social Media Post Template',
                'description' => 'Engaging social media post template for various platforms',
                'category' => 'social_media',
                'prompt_template' => 'Create an engaging social media post about {{topic}} for {{platform}}. Make it shareable, include relevant hashtags, and encourage engagement. Keep it concise and impactful.',
                'variables' => ['topic', 'platform'],
                'content_type' => 'social_media',
                'default_tone' => 'casual',
                'default_length' => 'short',
                'is_active' => true,
                'is_system' => true,
                'workspace_id' => 1,
                'created_by' => 1
            ],
            [
                'name' => 'Email Marketing Template',
                'description' => 'Professional email template with clear call-to-action',
                'category' => 'email',
                'prompt_template' => 'Write a professional marketing email about {{subject}}. Include a compelling subject line, personalized greeting, clear value proposition, and strong call-to-action. Target: {{target_audience}}.',
                'variables' => ['subject', 'target_audience'],
                'content_type' => 'email',
                'default_tone' => 'professional',
                'default_length' => 'medium',
                'is_active' => true,
                'is_system' => true,
                'workspace_id' => 1,
                'created_by' => 1
            ],
            [
                'name' => 'Article Template',
                'description' => 'Informative article template with proper structure and research-based content',
                'category' => 'content',
                'prompt_template' => 'Write an informative article about {{topic}}. Include proper headings, subheadings, and well-researched content. Provide statistics, examples, and actionable insights. Target word count: {{word_count}}.',
                'variables' => ['topic', 'word_count'],
                'content_type' => 'article',
                'default_tone' => 'informative',
                'default_length' => 'long',
                'is_active' => true,
                'is_system' => true,
                'workspace_id' => 1,
                'created_by' => 1
            ],
            [
                'name' => 'Ad Copy Template',
                'description' => 'Persuasive advertisement copy template for marketing campaigns',
                'category' => 'marketing',
                'prompt_template' => 'Create persuasive ad copy for {{product_service}}. Highlight the main benefit: {{main_benefit}}. Address the target audience pain points and include a compelling call-to-action. Keep it concise and impactful.',
                'variables' => ['product_service', 'main_benefit'],
                'content_type' => 'ad_copy',
                'default_tone' => 'persuasive',
                'default_length' => 'short',
                'is_active' => true,
                'is_system' => true,
                'workspace_id' => 1,
                'created_by' => 1
            ]
        ];

        foreach ($templates as $template) {
            AITemplate::updateOrCreate(
                ['name' => $template['name'], 'is_system' => true],
                $template
            );
        }
    }
}

<?php

namespace Hubiko\AIContent\Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Workspace;
use Hubiko\AIContent\Entities\AIContent;
use Hubiko\AIContent\Entities\AITemplate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;

class AIContentTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $user;
    protected $workspace;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create test user and workspace
        $this->workspace = Workspace::factory()->create();
        $this->user = User::factory()->create([
            'workspace_id' => $this->workspace->id,
            'type' => 'company'
        ]);
        
        $this->actingAs($this->user);
    }

    /** @test */
    public function it_can_display_ai_content_dashboard()
    {
        $response = $this->get(route('ai-content.dashboard'));
        
        $response->assertStatus(200);
        $response->assertViewIs('AIContent::dashboard.index');
    }

    /** @test */
    public function it_can_list_ai_content()
    {
        // Create test content
        AIContent::factory()->count(3)->create([
            'workspace_id' => $this->workspace->id,
            'created_by' => $this->user->id
        ]);

        $response = $this->get(route('ai-content.content.index'));
        
        $response->assertStatus(200);
        $response->assertViewIs('AIContent::content.index');
        $response->assertViewHas('contents');
    }

    /** @test */
    public function it_can_create_ai_content()
    {
        $template = AITemplate::factory()->create([
            'workspace_id' => $this->workspace->id,
            'created_by' => $this->user->id
        ]);

        $contentData = [
            'title' => 'Test AI Content',
            'content_type' => 'blog_post',
            'tone' => 'professional',
            'length' => 'medium',
            'language' => 'en',
            'template_id' => $template->id,
            'keywords' => ['test', 'ai', 'content'],
            'prompt' => 'Write a test blog post about AI content generation'
        ];

        $response = $this->post(route('ai-content.content.store'), $contentData);
        
        $response->assertRedirect();
        $this->assertDatabaseHas('ai_contents', [
            'title' => 'Test AI Content',
            'workspace_id' => $this->workspace->id,
            'created_by' => $this->user->id
        ]);
    }

    /** @test */
    public function it_can_show_ai_content()
    {
        $content = AIContent::factory()->create([
            'workspace_id' => $this->workspace->id,
            'created_by' => $this->user->id
        ]);

        $response = $this->get(route('ai-content.content.show', $content));
        
        $response->assertStatus(200);
        $response->assertViewIs('AIContent::content.show');
        $response->assertViewHas('content', $content);
    }

    /** @test */
    public function it_can_update_ai_content()
    {
        $content = AIContent::factory()->create([
            'workspace_id' => $this->workspace->id,
            'created_by' => $this->user->id
        ]);

        $updateData = [
            'title' => 'Updated AI Content',
            'generated_content' => 'Updated content here',
            'status' => 'published'
        ];

        $response = $this->put(route('ai-content.content.update', $content), $updateData);
        
        $response->assertRedirect();
        $this->assertDatabaseHas('ai_contents', [
            'id' => $content->id,
            'title' => 'Updated AI Content',
            'status' => 'published'
        ]);
    }

    /** @test */
    public function it_can_delete_ai_content()
    {
        $content = AIContent::factory()->create([
            'workspace_id' => $this->workspace->id,
            'created_by' => $this->user->id
        ]);

        $response = $this->delete(route('ai-content.content.destroy', $content));
        
        $response->assertRedirect();
        $this->assertSoftDeleted('ai_contents', ['id' => $content->id]);
    }

    /** @test */
    public function it_can_list_ai_templates()
    {
        AITemplate::factory()->count(3)->create([
            'workspace_id' => $this->workspace->id,
            'created_by' => $this->user->id
        ]);

        $response = $this->get(route('ai-content.templates.index'));
        
        $response->assertStatus(200);
        $response->assertViewIs('AIContent::templates.index');
        $response->assertViewHas('templates');
    }

    /** @test */
    public function it_can_create_ai_template()
    {
        $templateData = [
            'name' => 'Test Template',
            'description' => 'A test template',
            'category' => 'content',
            'prompt_template' => 'Write about {{topic}} for {{audience}}',
            'variables' => ['topic', 'audience'],
            'content_type' => 'blog_post',
            'default_tone' => 'professional',
            'default_length' => 'medium',
            'is_active' => true
        ];

        $response = $this->post(route('ai-content.templates.store'), $templateData);
        
        $response->assertRedirect();
        $this->assertDatabaseHas('ai_templates', [
            'name' => 'Test Template',
            'workspace_id' => $this->workspace->id,
            'created_by' => $this->user->id
        ]);
    }

    /** @test */
    public function it_can_toggle_template_status()
    {
        $template = AITemplate::factory()->create([
            'workspace_id' => $this->workspace->id,
            'created_by' => $this->user->id,
            'is_active' => true
        ]);

        $response = $this->post(route('ai-content.templates.toggle', $template));
        
        $response->assertRedirect();
        $this->assertDatabaseHas('ai_templates', [
            'id' => $template->id,
            'is_active' => false
        ]);
    }

    /** @test */
    public function it_prevents_unauthorized_access()
    {
        $otherWorkspace = Workspace::factory()->create();
        $content = AIContent::factory()->create([
            'workspace_id' => $otherWorkspace->id
        ]);

        $response = $this->get(route('ai-content.content.show', $content));
        
        $response->assertStatus(404);
    }

    /** @test */
    public function it_validates_required_fields_for_content_creation()
    {
        $response = $this->post(route('ai-content.content.store'), []);
        
        $response->assertSessionHasErrors(['title', 'content_type', 'prompt']);
    }

    /** @test */
    public function it_validates_required_fields_for_template_creation()
    {
        $response = $this->post(route('ai-content.templates.store'), []);
        
        $response->assertSessionHasErrors(['name', 'category', 'prompt_template', 'content_type']);
    }
}

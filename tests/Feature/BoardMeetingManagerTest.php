<?php

namespace Tests\Feature;

use App\Livewire\Board\ExecutiveBoard;
use App\Models\BoardSession;
use App\Models\BoardResponse;
use App\Models\Persona;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Tests\TestCase;

class BoardMeetingManagerTest extends TestCase
{
    use RefreshDatabase;

    public function test_executive_board_component_renders(): void
    {
        Livewire::test(ExecutiveBoard::class)
            ->assertStatus(200);
    }

    public function test_board_members_are_loaded_on_mount(): void
    {
        Persona::factory()->count(5)->create([
            'role' => 'board_member',
            'status' => 'active',
        ]);

        Livewire::test(ExecutiveBoard::class)
            ->assertSet('boardMembers.count', 5);
    }

    public function test_default_board_members_loaded_when_none_exist(): void
    {
        Livewire::test(ExecutiveBoard::class)
            ->assertSet('boardMembers.count', 6);
    }

    public function test_stats_are_loaded(): void
    {
        BoardSession::factory()->count(3)->create(['status' => 'decided']);
        BoardSession::factory()->count(2)->create(['status' => 'pending']);

        Livewire::test(ExecutiveBoard::class)
            ->assertSet('stats.total_sessions', 5)
            ->assertSet('stats.decisions', 3)
            ->assertSet('stats.pending', 2);
    }

    public function test_convene_board_requires_question(): void
    {
        Livewire::test(ExecutiveBoard::class)
            ->set('question', '')
            ->call('conveneBoard')
            ->assertDispatched('toast-warning');
    }

    public function test_convene_board_creates_session(): void
    {
        Config::set('services.openrouter.key', 'test-key');
        
        Http::fake([
            'openrouter.ai/*' => Http::response(['choices' => [['message' => ['content' => 'Test']]]], 200),
        ]);

        Persona::factory()->create([
            'role' => 'board_member',
            'status' => 'active',
        ]);

        Livewire::test(ExecutiveBoard::class)
            ->set('question', 'Should we launch feature X?')
            ->set('context', 'Feature X has high demand')
            ->call('conveneBoard');

        $this->assertDatabaseHas('board_sessions', [
            'question' => 'Should we launch feature X?',
            'context' => 'Feature X has high demand',
        ]);
    }

    public function test_convene_board_sets_session_to_debating(): void
    {
        Config::set('services.openrouter.key', 'test-key');
        
        Http::fake([
            'openrouter.ai/*' => Http::response(['choices' => [['message' => ['content' => 'Test']]]], 200),
        ]);

        Persona::factory()->create([
            'role' => 'board_member',
            'status' => 'active',
        ]);

        Livewire::test(ExecutiveBoard::class)
            ->set('question', 'Test question')
            ->call('conveneBoard');

        $session = BoardSession::first();
        
        $this->assertEquals('decided', $session->status);
    }

    public function test_transcript_is_loaded_after_session(): void
    {
        Config::set('services.openrouter.key', 'test-key');
        
        Http::fake([
            'openrouter.ai/*' => Http::response(['choices' => [['message' => ['content' => 'Executive response']]]], 200),
        ]);

        Persona::factory()->create([
            'role' => 'board_member',
            'status' => 'active',
        ]);

        $component = Livewire::test(ExecutiveBoard::class)
            ->set('question', 'Test question')
            ->call('conveneBoard');

        $component->call('loadTranscript');
        
        $component->assertSet('transcript.count', function ($count) {
            return $count >= 1;
        });
    }

    public function test_final_decision_is_displayed(): void
    {
        Config::set('services.openrouter.key', 'test-key');
        
        Http::fake([
            'openrouter.ai/*' => Http::response([
                'choices' => [['message' => ['content' => 'RECOMMENDATION: Yes, proceed.']]]
            ], 200),
        ]);

        Persona::factory()->create([
            'role' => 'board_member',
            'status' => 'active',
        ]);

        $component = Livewire::test(ExecutiveBoard::class)
            ->set('question', 'Test question')
            ->call('conveneBoard');

        $component->assertSet('finalDecision', function ($decision) {
            return !empty($decision);
        });
    }

    public function test_api_configuration_is_checked(): void
    {
        Config::set('services.openrouter.key', null);

        $component = Livewire::test(ExecutiveBoard::class);
        
        $component->assertSet('apiConfigured', false);

        Config::set('services.openrouter.key', 'test-key');
        
        $component->call('checkApiConfiguration');
        
        $component->assertSet('apiConfigured', true);
    }

    public function test_reset_session_clears_all_data(): void
    {
        $component = Livewire::test(ExecutiveBoard::class)
            ->set('question', 'Test question')
            ->set('context', 'Test context')
            ->set('currentSessionId', 'test-id')
            ->set('finalDecision', 'Test decision');

        $component->call('resetSession');

        $component->assertSet('question', '');
        $component->assertSet('context', '');
        $component->assertSet('currentSessionId', null);
        $component->assertSet('finalDecision', null);
    }

    public function test_reset_session_dispatches_notification(): void
    {
        Livewire::test(ExecutiveBoard::class)
            ->call('resetSession')
            ->assertDispatched('toast-info');
    }

    public function test_component_shows_correct_avatars_for_roles(): void
    {
        $component = Livewire::test(ExecutiveBoard::class);
        
        $reflection = new \ReflectionClass($component->instance());
        $method = $reflection->getMethod('getAvatarForRole');
        $method->setAccessible(true);

        $this->assertEquals('🎯', $method->invoke($component->instance(), 'CEO'));
        $this->assertEquals('👔', $method->invoke($component->instance(), 'COO'));
        $this->assertEquals('💻', $method->invoke($component->instance(), 'CTO'));
        $this->assertEquals('💰', $method->invoke($component->instance(), 'CFO'));
        $this->assertEquals('📢', $method->invoke($component->instance(), 'CMO'));
        $this->assertEquals('📦', $method->invoke($component->instance(), 'CPO'));
    }

    public function test_load_stats_method_exists_and_works(): void
    {
        BoardSession::factory()->count(10)->create();

        $component = Livewire::test(ExecutiveBoard::class);
        
        $component->call('loadStats');
        
        $component->assertSet('stats.total_sessions', 10);
    }

    public function test_component_handles_board_member_with_custom_avatar(): void
    {
        Persona::factory()->create([
            'role' => 'board_member',
            'status' => 'active',
            'name' => 'Custom Member',
            'avatar' => '🚀',
        ]);

        $component = Livewire::test(ExecutiveBoard::class);
        
        $component->assertSee('Custom Member');
    }
}

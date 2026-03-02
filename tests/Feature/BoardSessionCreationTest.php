<?php

namespace Tests\Feature;

use App\Models\BoardSession;
use App\Models\BoardResponse;
use App\Models\Persona;
use App\Services\BoardOrchestrator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class BoardSessionCreationTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_board_session(): void
    {
        $session = BoardSession::create([
            'question' => 'Should we enter a new market?',
            'context' => 'Market analysis shows potential growth.',
            'status' => 'pending',
        ]);

        $this->assertNotNull($session->id);
        $this->assertEquals('pending', $session->status);
        $this->assertNotNull($session->created_at);
    }

    public function test_session_uuid_is_auto_generated(): void
    {
        $session = BoardSession::factory()->create();

        $this->assertNotEmpty($session->id);
        $this->assertEquals(36, strlen($session->id));
    }

    public function test_session_with_full_context(): void
    {
        $session = BoardSession::factory()->create([
            'question' => 'Should we prioritize feature X or Y?',
            'context' => 'Feature X has 80% user demand. Feature Y is technically easier.',
            'status' => 'pending',
        ]);

        $this->assertEquals('Should we prioritize feature X or Y?', $session->question);
        $this->assertStringContainsString('Feature X', $session->context);
    }

    public function test_session_without_context(): void
    {
        $session = BoardSession::factory()->create([
            'context' => null,
        ]);

        $this->assertNull($session->context);
    }

    public function test_session_factory_pending_state(): void
    {
        $session = BoardSession::factory()->pending()->create();

        $this->assertEquals('pending', $session->status);
        $this->assertNull($session->final_decision);
    }

    public function test_session_factory_debating_state(): void
    {
        $session = BoardSession::factory()->debating()->create();

        $this->assertEquals('debating', $session->status);
    }

    public function test_session_factory_decided_state(): void
    {
        $session = BoardSession::factory()->decided()->create();

        $this->assertEquals('decided', $session->status);
        $this->assertNotNull($session->final_decision);
        $this->assertNotNull($session->decided_at);
    }

    public function test_full_session_workflow(): void
    {
        Config::set('services.openrouter.key', 'test-key');
        
        Http::fake([
            'openrouter.ai/*' => Http::response([
                'choices' => [['message' => ['content' => 'Board member perspective']]
            ]], 200),
        ]);

        Persona::factory()->count(5)->create([
            'role' => 'board_member',
            'status' => 'active',
        ]);

        // Step 1: Create session
        $session = BoardSession::factory()->pending()->create([
            'question' => 'Should we launch the product?',
        ]);

        $this->assertEquals('pending', $session->status);

        // Step 2: Run session
        $orchestrator = new BoardOrchestrator();
        $orchestrator->runSession($session->id, $session->question);

        // Step 3: Verify session is decided
        $session->refresh();
        
        $this->assertEquals('decided', $session->status);
        $this->assertNotNull($session->final_decision);
        $this->assertNotNull($session->decided_at);

        // Step 4: Verify responses were created
        $this->assertGreaterThan(0, $session->responses()->count());
    }

    public function test_session_creation_via_livewire(): void
    {
        Config::set('services.openrouter.key', 'test-key');
        
        Http::fake([
            'openrouter.ai/*' => Http::response(['choices' => [['message' => ['content' => 'Test']]]], 200),
        ]);

        Persona::factory()->create([
            'role' => 'board_member',
            'status' => 'active',
        ]);

        $initialCount = BoardSession::count();

        \Livewire\Livewire::test(\App\Livewire\Board\ExecutiveBoard::class)
            ->set('question', 'New session question')
            ->call('conveneBoard');

        $this->assertEquals($initialCount + 1, BoardSession::count());
    }

    public function test_session_cascades_delete_to_responses(): void
    {
        $session = BoardSession::factory()->create();
        
        BoardResponse::factory()->count(3)->forSession($session)->create();

        $this->assertEquals(3, $session->responses()->count());

        $session->delete();

        $this->assertEquals(0, BoardResponse::where('session_id', $session->id)->count());
    }

    public function test_multiple_concurrent_sessions(): void
    {
        $sessions = BoardSession::factory()->count(5)->create();

        $this->assertEquals(5, BoardSession::count());
        
        $sessions->each(function ($session) {
            $this->assertNotNull($session->id);
            $this->assertNotNull($session->question);
        });
    }

    public function test_session_with_special_characters(): void
    {
        $session = BoardSession::factory()->create([
            'question' => 'Should we invest in AI/ML? (Cost: $1M+)',
            'context' => 'ROI expected: 300% over 3 years.',
        ]);

        $this->assertStringContainsString('AI/ML', $session->question);
        $this->assertStringContainsString('$1M', $session->question);
    }

    public function test_session_question_length_validation(): void
    {
        $longQuestion = str_repeat('Long question text ', 100);
        
        $session = BoardSession::factory()->create([
            'question' => $longQuestion,
        ]);

        $this->assertNotNull($session->id);
        $this->assertEquals($longQuestion, $session->question);
    }

    public function test_session_status_transitions(): void
    {
        $session = BoardSession::factory()->pending()->create();
        
        $this->assertEquals('pending', $session->status);

        $session->update(['status' => 'debating']);
        $this->assertEquals('debating', $session->status);

        $session->update([
            'status' => 'decided',
            'final_decision' => 'Final decision',
            'decided_at' => now(),
        ]);
        $this->assertEquals('decided', $session->status);
    }

    public function test_session_scopes_chain_correctly(): void
    {
        BoardSession::factory()->pending()->create();
        BoardSession::factory()->pending()->create();
        BoardSession::factory()->decided()->create();

        $pending = BoardSession::pending()->get();
        $this->assertEquals(2, $pending->count());
    }

    public function test_session_retrieval_by_id(): void
    {
        $session = BoardSession::factory()->create();

        $retrieved = BoardSession::find($session->id);

        $this->assertNotNull($retrieved);
        $this->assertEquals($session->id, $retrieved->id);
    }
}

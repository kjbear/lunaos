<?php

namespace Tests\Unit\Models;

use App\Models\BoardResponse;
use App\Models\BoardSession;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase as BaseTestCase;

class BoardSessionTest extends BaseTestCase
{
    use RefreshDatabase;

    public function test_it_has_uuid_primary_key(): void
    {
        $session = BoardSession::factory()->create();
        
        $this->assertTrue(app('db')->getPdo()->quote($session->id) !== 'NULL');
        $this->assertNotEmpty($session->id);
        $this->assertIsString($session->id);
    }

    public function test_it_has_correct_fillable_attributes(): void
    {
        $session = BoardSession::factory()->create([
            'question' => 'Should we launch feature X?',
            'context' => 'Feature X has been requested by users.',
            'status' => 'pending',
        ]);

        $this->assertEquals('Should we launch feature X?', $session->question);
        $this->assertEquals('Feature X has been requested by users.', $session->context);
        $this->assertEquals('pending', $session->status);
    }

    public function test_it_has_many_responses(): void
    {
        $session = BoardSession::factory()->create();
        $response1 = BoardResponse::factory()->forSession($session)->create(['response_order' => 0]);
        $response2 = BoardResponse::factory()->forSession($session)->create(['response_order' => 1]);
        $response3 = BoardResponse::factory()->forSession($session)->create(['response_order' => 2]);

        $this->assertTrue($session->responses()->exists());
        $this->assertEquals(3, $session->responses()->count());
        $this->assertContains($response1->id, $session->responses->pluck('id')->toArray());
        $this->assertContains($response2->id, $session->responses->pluck('id')->toArray());
        $this->assertContains($response3->id, $session->responses->pluck('id')->toArray());
    }

    public function test_responses_are_ordered_by_response_order(): void
    {
        $session = BoardSession::factory()->create();
        
        BoardResponse::factory()->forSession($session)->create(['response_order' => 2]);
        BoardResponse::factory()->forSession($session)->create(['response_order' => 0]);
        BoardResponse::factory()->forSession($session)->create(['response_order' => 1]);

        $orderedResponses = $session->responses;
        
        $this->assertEquals(0, $orderedResponses[0]->response_order);
        $this->assertEquals(1, $orderedResponses[1]->response_order);
        $this->assertEquals(2, $orderedResponses[2]->response_order);
    }

    public function test_pending_scope_returns_only_pending_sessions(): void
    {
        $pendingSession = BoardSession::factory()->pending()->create();
        $debatingSession = BoardSession::factory()->debating()->create();
        $decidedSession = BoardSession::factory()->decided()->create();

        $pendingSessions = BoardSession::pending()->get();

        $this->assertTrue($pendingSessions->contains($pendingSession));
        $this->assertFalse($pendingSessions->contains($debatingSession));
        $this->assertFalse($pendingSessions->contains($decidedSession));
        $this->assertEquals(1, $pendingSessions->count());
    }

    public function test_debating_scope_returns_only_debating_sessions(): void
    {
        $pendingSession = BoardSession::factory()->pending()->create();
        $debatingSession = BoardSession::factory()->debating()->create();
        $decidedSession = BoardSession::factory()->decided()->create();

        $debatingSessions = BoardSession::debating()->get();

        $this->assertTrue($debatingSessions->contains($debatingSession));
        $this->assertFalse($debatingSessions->contains($pendingSession));
        $this->assertFalse($debatingSessions->contains($decidedSession));
        $this->assertEquals(1, $debatingSessions->count());
    }

    public function test_decided_scope_returns_only_decided_sessions(): void
    {
        $pendingSession = BoardSession::factory()->pending()->create();
        $debatingSession = BoardSession::factory()->debating()->create();
        $decidedSession = BoardSession::factory()->decided()->create();

        $decidedSessions = BoardSession::decided()->get();

        $this->assertTrue($decidedSessions->contains($decidedSession));
        $this->assertFalse($decidedSessions->contains($pendingSession));
        $this->assertFalse($decidedSessions->contains($debatingSession));
        $this->assertEquals(1, $decidedSessions->count());
    }

    public function test_decided_at_is_cast_to_datetime(): void
    {
        $session = BoardSession::factory()->decided()->create();
        
        $this->assertInstanceOf(\DateTime::class, $session->decided_at);
    }

    public function test_cascading_delete_deletes_responses(): void
    {
        $session = BoardSession::factory()->create();
        BoardResponse::factory()->forSession($session)->create();
        BoardResponse::factory()->forSession($session)->create();

        $this->assertEquals(2, $session->responses()->count());

        $session->delete();

        $this->assertEquals(0, BoardResponse::where('session_id', $session->id)->count());
    }

    public function test_board_response_belongs_to_session(): void
    {
        $session = BoardSession::factory()->create();
        $response = BoardResponse::factory()->forSession($session)->create();

        $this->assertTrue($response->session()->exists());
        $this->assertEquals($session->id, $response->session->id);
    }
}

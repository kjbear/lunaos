<?php

namespace Tests\Feature;

use App\Agents\Personas\COOPersona;
use App\Agents\Personas\CFOPersona;
use App\Agents\Personas\CTOPersona;
use App\Agents\Personas\CMOPersona;
use App\Agents\Personas\CPOPersona;
use App\Services\BoardDebateOrchestrator;
use App\Services\BoardDecisionConsolidator;
use App\Models\BoardSession;
use App\Models\BoardResponse;
use Tests\TestCase;

/**
 * Test the executive board debate orchestration system.
 */
class ExecutiveBoardDebateTest extends TestCase
{
    /**
     * Test that all persona classes can be instantiated.
     */
    public function test_personas_instantiate(): void
    {
        $personas = [
            new COOPersona(),
            new CFOPersona(),
            new CTOPersona(),
            new CMOPersona(),
            new CPOPersona(),
        ];

        foreach ($personas as $persona) {
            $this->assertInstanceOf(\App\Agents\Personas\ExecutivePersona::class, $persona);
            $this->assertNotEmpty($persona->name);
            $this->assertNotEmpty($persona->title);
            $this->assertNotEmpty($persona->getSystemPrompt());
            $this->assertIsArray($persona->toArray());
        }
    }

    /**
     * Test persona prompt building.
     */
    public function test_persona_prompt_building(): void
    {
        $persona = new COOPersona();
        
        $prompt = $persona->buildPrompt(
            "Should we prioritize feature X or Y?",
            "Budget is limited to $50k",
            [],
            1
        );

        $this->assertStringContainsString('BOARD MEETING - ROUND 1', $prompt);
        $this->assertStringContainsString('Should we prioritize feature X or Y?', $prompt);
        $this->assertStringContainsString('Budget is limited to $50k', $prompt);
        $this->assertStringContainsString('COO', $prompt);
    }

    /**
     * Test persona prompt building with previous responses.
     */
    public function test_persona_prompt_with_previous_responses(): void
    {
        $persona = new CFOPersona();
        
        $previousResponses = [
            [
                'name' => 'Gwynne',
                'title' => 'COO',
                'response' => 'We should focus on operational efficiency first.',
            ],
        ];

        $prompt = $persona->buildPrompt(
            "What should be our Q1 priority?",
            null,
            $previousResponses,
            2
        );

        $this->assertStringContainsString('ROUND 2', $prompt);
        $this->assertStringContainsString('COO (Gwynne)', $prompt);
        $this->assertStringContainsString('operational efficiency', $prompt);
    }

    /**
     * Test debate orchestrator initialization.
     */
    public function test_orchestrator_initializes(): void
    {
        $orchestrator = new BoardDebateOrchestrator();
        
        $personas = $orchestrator->getPersonas();
        
        $this->assertIsArray($personas);
        $this->assertCount(5, $personas); // Should have 5 default personas
        
        foreach ($personas as $persona) {
            $this->assertInstanceOf(\App\Agents\Personas\ExecutivePersona::class, $persona);
        }
    }

    /**
     * Test decision consolidator.
     */
    public function test_decision_consolidator(): void
    {
        $consolidator = new BoardDecisionConsolidator();
        
        $responses = [
            [
                'name' => 'Gwynne',
                'title' => 'COO',
                'round' => 1,
                'response' => 'We should prioritize operational efficiency.',
            ],
            [
                'name' => 'Warren',
                'title' => 'CFO',
                'round' => 1,
                'response' => 'ROI should be our primary consideration.',
            ],
        ];

        $result = $consolidator->consolidate(
            "What should be our top priority?",
            $responses,
            "Limited budget, need to show results in Q1"
        );

        $this->assertArrayHasKey('recommendation', $result);
        $this->assertArrayHasKey('confidence_score', $result);
        $this->assertArrayHasKey('dissenting_opinions', $result);
        $this->assertIsFloat($result['confidence_score']);
        $this->assertIsArray($result['dissenting_opinions']);
    }

    /**
     * Test full debate flow (without actual API calls).
     */
    public function test_debate_flow_structure(): void
    {
        $orchestrator = new BoardDebateOrchestrator();
        
        // Set minimal rounds for test
        $orchestrator->setMaxRounds(2);
        
        // Verify orchestrator has expected methods
        $this->assertTrue(method_exists($orchestrator, 'runDebate'));
        $this->assertTrue(method_exists($orchestrator, 'getPersonas'));
        $this->assertTrue(method_exists($orchestrator, 'setPersonas'));
        $this->assertTrue(method_exists($orchestrator, 'setMaxRounds'));
    }

    /**
     * Test session database creation.
     */
    public function test_board_session_creation(): void
    {
        $session = BoardSession::create([
            'question' => 'Test question for board debate',
            'context' => 'Test context',
            'status' => 'pending',
            'rounds_planned' => 2,
        ]);

        $this->assertNotNull($session->id);
        $this->assertEquals('pending', $session->status);
        $this->assertEquals('Test question for board debate', $session->question);
        
        // Cleanup
        $session->delete();
    }

    /**
     * Test board response creation.
     */
    public function test_board_response_creation(): void
    {
        $session = BoardSession::create([
            'question' => 'Test question',
            'status' => 'debating',
        ]);

        $response = BoardResponse::create([
            'session_id' => $session->id,
            'member_name' => 'Test Executive',
            'member_role' => 'CTO',
            'response' => 'This is a test response',
            'model_used' => 'glm-5',
            'response_order' => 1,
            'round' => 1,
        ]);

        $this->assertNotNull($response->id);
        $this->assertEquals($session->id, $response->session_id);
        $this->assertEquals(1, $response->round);
        
        // Test relationships
        $this->assertInstanceOf(BoardSession::class, $response->session);
        
        // Cleanup
        $response->delete();
        $session->delete();
    }

    /**
     * Test config loading.
     */
    public function test_config_loaded(): void
    {
        $personas = config('executive-board.personas');
        $this->assertIsArray($personas);
        $this->assertNotEmpty($personas);

        $model = config('executive-board.model');
        $this->assertEquals('glm-5', $model);

        $rounds = config('executive-board.rounds');
        $this->assertEquals(2, $rounds);

        $timeout = config('executive-board.timeout_seconds');
        $this->assertEquals(120, $timeout);
    }
}

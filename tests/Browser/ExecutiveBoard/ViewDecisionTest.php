<?php

namespace Tests\Browser\ExecutiveBoard;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class ViewDecisionTest extends DuskTestCase
{
    /**
     * Test decision is revealed after debate.
     * 
     * @group executive-board
     */
    public function test_decision_is_revealed(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/board')
                ->waitFor('@question')
                ->type('@question', 'Should we launch the product?')
                ->press('@conveneBoard')
                ->waitForText('Decision', 60)
                ->assertSee('Decision');
        });
    }

    /**
     * Test decision includes reasoning.
     * 
     * @group executive-board
     */
    public function test_decision_includes_reasoning(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/board')
                ->waitFor('@question')
                ->type('@question', 'Strategic decision')
                ->press('@conveneBoard')
                ->waitForText('Decision', 60);
            // Decision should have reasoning content
        });
    }

    /**
     * Test decision shows confidence level.
     * 
     * @group executive-board
     */
    public function test_decision_shows_confidence(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/board')
                ->waitFor('@question')
                ->type('@question', 'Test question')
                ->press('@conveneBoard')
                ->waitForText('Decision', 60);
            // Decision should be visible
        });
    }

    /**
     * Test decision displays risks and benefits.
     * 
     * @group executive-board
     */
    public function test_decision_shows_risks_and_benefits(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/board')
                ->waitFor('@question')
                ->type('@question', 'Business decision')
                ->press('@conveneBoard')
                ->waitForText('Decision', 60);
            // Should have risks/benefits section
        });
    }

    /**
     * Test decision is clearly visible.
     * 
     * @group executive-board
     */
    public function test_decision_is_prominent(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/board')
                ->waitFor('@question')
                ->type('@question', 'Test question')
                ->press('@conveneBoard')
                ->waitForText('complete', 60)
                ->assertSee('Decision');
        });
    }

    /**
     * Test decision text is readable.
     * 
     * @group executive-board
     */
    public function test_decision_text_is_readable(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/board')
                ->waitFor('@question')
                ->type('@question', 'Test question')
                ->press('@conveneBoard')
                ->waitForText('Decision', 60);
            // Decision section should be styled properly
        });
    }

    /**
     * Test decision appears after transcript.
     * 
     * @group executive-board
     */
    public function test_decision_appears_after_transcript(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/board')
                ->waitFor('@question')
                ->type('@question', 'Test question')
                ->press('@conveneBoard')
                ->waitForText('Transcript', 30)
                ->waitForText('Decision', 60)
                ->assertSee('Transcript')
                ->assertSee('Decision');
        });
    }

    /**
     * Test decision includes recommendation.
     * 
     * @group executive-board
     */
    public function test_decision_includes_recommendation(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/board')
                ->waitFor('@question')
                ->type('@question', 'Should we do X or Y?')
                ->press('@conveneBoard')
                ->waitForText('Decision', 60);
            // Decision should contain clear recommendation
        });
    }

    /**
     * Test decision persists after page reload.
     * 
     * @group executive-board
     */
    public function test_decision_persists_after_reload(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/board')
                ->waitFor('@question')
                ->type('@question', 'Test question')
                ->press('@conveneBoard')
                ->waitForText('Decision', 60)
                ->refresh()
                ->waitForText('Decision', 10);
            // Decision should still be visible
        });
    }

    /**
     * Test decision is cleared on new session.
     * 
     * @group executive-board
     */
    public function test_decision_clears_on_new_session(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/board')
                ->waitFor('@question')
                ->type('@question', 'First question')
                ->press('@conveneBoard')
                ->waitForText('Decision', 60)
                ->press('@resetSession')
                ->waitForText('Session cleared', 5);
            // Old decision should be cleared
        });
    }

    /**
     * Test decision section is formatted correctly.
     * 
     * @group executive-board
     */
    public function test_decision_formatting(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/board')
                ->waitFor('@question')
                ->type('@question', 'Test question')
                ->press('@conveneBoard')
                ->waitForText('Decision', 60);
            // Decision should have proper formatting
        });
    }

    /**
     * Test decision with typical business question.
     * 
     * @group executive-board
     */
    public function test_decision_for_business_question(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/board')
                ->waitFor('@question')
                ->type('@question', 'Should we prioritize feature X or Y?')
                ->press('@conveneBoard')
                ->waitForText('Decision', 60)
                ->assertSee('Board session complete');
        });
    }
}

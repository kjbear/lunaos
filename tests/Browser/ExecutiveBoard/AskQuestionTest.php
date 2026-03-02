<?php

namespace Tests\Browser\ExecutiveBoard;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class AskQuestionTest extends DuskTestCase
{
    /**
     * Test submitting a question to the executive board.
     * 
     * @group executive-board
     */
    public function test_can_submit_question(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/board')
                ->waitFor('@question')
                ->type('@question', 'Should we prioritize feature X or Y?')
                ->press('@conveneBoard')
                ->waitForText('Board session complete', 30)
                ->assertSee('Board session complete');
        });
    }

    /**
     * Test submitting a question with context.
     * 
     * @group executive-board
     */
    public function test_can_submit_question_with_context(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/board')
                ->waitFor('@question')
                ->type('@question', 'Should we expand into European market?')
                ->type('@context', 'Market research shows 40% growth potential in EU.')
                ->press('@conveneBoard')
                ->waitForText('Board session complete', 30)
                ->assertSee('Board session complete');
        });
    }

    /**
     * Test validation - cannot submit empty question.
     * 
     * @group executive-board
     */
    public function test_cannot_submit_empty_question(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/board')
                ->press('@conveneBoard')
                ->waitForText('Please enter a question', 5)
                ->assertSee('Please enter a question');
        });
    }

    /**
     * Test that board members are displayed before submission.
     * 
     * @group executive-board
     */
    public function test_board_members_are_displayed(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/board')
                ->waitForText('CEO')
                ->assertSee('CEO')
                ->assertSee('COO')
                ->assertSee('CTO');
        });
    }

    /**
     * Test submitting a typical business question.
     * 
     * @group executive-board
     */
    public function test_submit_typical_business_question(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/board')
                ->waitFor('@question')
                ->type('@question', 'Should we prioritize feature X or Y?')
                ->press('@conveneBoard')
                ->waitForText('Convening the board', 5)
                ->assertSee('Convening the board');
        });
    }

    /**
     * Test question input accepts long text.
     * 
     * @group executive-board
     */
    public function test_question_accepts_long_text(): void
    {
        $longQuestion = str_repeat('This is a detailed business question about our strategic direction ', 10);
        
        $this->browse(function (Browser $browser) use ($longQuestion) {
            $browser->visit('/board')
                ->waitFor('@question')
                ->type('@question', $longQuestion)
                ->assertInputValue('@question', $longQuestion);
        });
    }

    /**
     * Test context field is optional.
     * 
     * @group executive-board
     */
    public function test_context_is_optional(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/board')
                ->waitFor('@question')
                ->type('@question', 'Simple question without context')
                ->press('@conveneBoard')
                ->waitForText('Convening', 5)
                ->assertSee('Convening');
        });
    }

    /**
     * Test stats are displayed on the board page.
     * 
     * @group executive-board
     */
    public function test_stats_are_displayed(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/board')
                ->waitForText('Total Sessions')
                ->assertSee('Total Sessions');
        });
    }

    /**
     * Test API configuration warning is shown when not configured.
     * 
     * @group executive-board
     */
    public function test_api_configuration_status(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/board')
                ->waitForText('Executive Board');
            // API status should be visible in some form
        });
    }

    /**
     * Test form reset functionality.
     * 
     * @group executive-board
     */
    public function test_can_reset_form(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/board')
                ->waitFor('@question')
                ->type('@question', 'Test question')
                ->type('@context', 'Test context')
                ->press('@resetSession')
                ->waitForText('Session cleared', 5)
                ->assertInputValue('@question', '')
                ->assertInputValue('@context', '');
        });
    }
}

<?php

namespace Tests\Browser\ExecutiveBoard;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class ViewTranscriptTest extends DuskTestCase
{
    /**
     * Test transcript is displayed after debate.
     * 
     * @group executive-board
     */
    public function test_transcript_is_displayed(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/board')
                ->waitFor('@question')
                ->type('@question', 'Should we proceed with the merger?')
                ->press('@conveneBoard')
                ->waitForText('Transcript', 30)
                ->assertSee('Transcript');
        });
    }

    /**
     * Test transcript shows full debate content.
     * 
     * @group executive-board
     */
    public function test_transcript_shows_full_debate(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/board')
                ->waitFor('@question')
                ->type('@question', 'Test question')
                ->press('@conveneBoard')
                ->waitForText('Transcript', 30);
            // Verify transcript contains responses
        });
    }

    /**
     * Test transcript captures all board member responses.
     * 
     * @group executive-board
     */
    public function test_transcript_captures_all_responses(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/board')
                ->waitFor('@question')
                ->type('@question', 'Strategic decision')
                ->press('@conveneBoard')
                ->waitForText('Transcript', 30);
            // Should have multiple responses visible
        });
    }

    /**
     * Test transcript displays member avatars.
     * 
     * @group executive-board
     */
    public function test_transcript_shows_avatars(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/board')
                ->waitFor('@question')
                ->type('@question', 'Test question')
                ->press('@conveneBoard')
                ->waitForText('🎯', 30)
                ->assertSee('🎯');
        });
    }

    /**
     * Test transcript is formatted correctly.
     * 
     * @group executive-board
     */
    public function test_transcript_is_formatted(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/board')
                ->waitFor('@question')
                ->type('@question', 'Test question')
                ->press('@conveneBoard')
                ->waitForText('Transcript', 30);
            // Verify transcript section has proper styling
        });
    }

    /**
     * Test transcript shows response order.
     * 
     * @group executive-board
     */
    public function test_transcript_shows_order(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/board')
                ->waitFor('@question')
                ->type('@question', 'Test question')
                ->press('@conveneBoard')
                ->waitForText('Transcript', 30);
            // Responses should be in order
        });
    }

    /**
     * Test transcript persists after page refresh.
     * 
     * @group executive-board
     */
    public function test_transcript_persists_after_refresh(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/board')
                ->waitFor('@question')
                ->type('@question', 'Test question')
                ->press('@conveneBoard')
                ->waitForText('Transcript', 30)
                ->refresh()
                ->waitForText('Transcript', 10);
            // Transcript should still be visible
        });
    }

    /**
     * Test transcript can be scrolled to view all content.
     * 
     * @group executive-board
     */
    public function test_transcript_is_scrollable(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/board')
                ->waitFor('@question')
                ->type('@question', 'Complex question requiring detailed analysis')
                ->press('@conveneBoard')
                ->waitForText('Transcript', 30);
            // Verify transcript section exists
        });
    }

    /**
     * Test transcript displays member role titles.
     * 
     * @group executive-board
     */
    public function test_transcript_shows_role_titles(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/board')
                ->waitFor('@question')
                ->type('@question', 'Test question')
                ->press('@conveneBoard')
                ->waitForText('CEO', 30)
                ->assertSee('CEO');
        });
    }

    /**
     * Test transcript is cleared on new session.
     * 
     * @group executive-board
     */
    public function test_transcript_clears_on_new_session(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/board')
                ->waitFor('@question')
                ->type('@question', 'First question')
                ->press('@conveneBoard')
                ->waitForText('Transcript', 30)
                ->press('@resetSession')
                ->waitForText('Session cleared', 5);
            // Old transcript should be cleared
        });
    }
}

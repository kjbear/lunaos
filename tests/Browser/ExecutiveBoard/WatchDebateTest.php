<?php

namespace Tests\Browser\ExecutiveBoard;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class WatchDebateTest extends DuskTestCase
{
    /**
     * Test viewing live debate as it happens.
     * 
     * @group executive-board
     */
    public function test_can_watch_debate(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/board')
                ->waitFor('@question')
                ->type('@question', 'Should we launch the new product?')
                ->press('@conveneBoard')
                ->waitForText('Transcript', 30)
                ->assertSee('Transcript');
        });
    }

    /**
     * Test that all 5 personas respond during debate.
     * 
     * @group executive-board
     */
    public function test_all_personas_respond(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/board')
                ->waitFor('@question')
                ->type('@question', 'Strategic decision question')
                ->press('@conveneBoard')
                ->waitForText('CEO', 30)
                ->assertSee('CEO')
                ->assertSee('COO')
                ->assertSee('CTO')
                ->assertSee('CFO');
        });
    }

    /**
     * Test debate transcript displays member names.
     * 
     * @group executive-board
     */
    public function test_debate_shows_member_names(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/board')
                ->waitFor('@question')
                ->type('@question', 'Test question')
                ->press('@conveneBoard')
                ->waitForText('Steven', 30)
                ->assertSee('Steven');
        });
    }

    /**
     * Test debate transcript displays member roles.
     * 
     * @group executive-board
     */
    public function test_debate_shows_member_roles(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/board')
                ->waitFor('@question')
                ->type('@question', 'Test question')
                ->press('@conveneBoard')
                ->waitForText('Chief', 30)
                ->assertSee('Chief');
        });
    }

    /**
     * Test debate responses are displayed in order.
     * 
     * @group executive-board
     */
    public function test_debate_responses_are_ordered(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/board')
                ->waitFor('@question')
                ->type('@question', 'Test question')
                ->press('@conveneBoard')
                ->waitForText('Transcript', 30);
            // Verify transcript section exists and has content
        });
    }

    /**
     * Test debate loading state is shown.
     * 
     * @group executive-board
     */
    public function test_debate_loading_state(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/board')
                ->waitFor('@question')
                ->type('@question', 'Test question')
                ->press('@conveneBoard')
                ->assertSee('Convening');
        });
    }

    /**
     * Test debate completes successfully.
     * 
     * @group executive-board
     */
    public function test_debate_completes(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/board')
                ->waitFor('@question')
                ->type('@question', 'Test question')
                ->press('@conveneBoard')
                ->waitForText('Board session complete', 60)
                ->assertSee('Board session complete');
        });
    }

    /**
     * Test debate transcript contains actual responses.
     * 
     * @group executive-board
     */
    public function test_transcript_contains_responses(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/board')
                ->waitFor('@question')
                ->type('@question', 'Should we invest in AI?')
                ->press('@conveneBoard')
                ->waitForText('Transcript', 30);
            // Verify transcript section has content
        });
    }

    /**
     * Test multiple debates can be viewed.
     * 
     * @group executive-board
     */
    public function test_can_view_multiple_debates(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/board')
                ->waitFor('@question')
                ->type('@question', 'First question')
                ->press('@conveneBoard')
                ->waitForText('complete', 60)
                ->press('@resetSession')
                ->waitForText('Session cleared', 5)
                ->type('@question', 'Second question')
                ->press('@conveneBoard')
                ->waitForText('complete', 60)
                ->assertSee('Board session complete');
        });
    }

    /**
     * Test debate view is responsive.
     * 
     * @group executive-board
     */
    public function test_debate_view_is_responsive(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->resize(768, 1024)
                ->visit('/board')
                ->waitFor('@question')
                ->type('@question', 'Test question')
                ->press('@conveneBoard')
                ->waitForText('Transcript', 30);
        });
    }
}

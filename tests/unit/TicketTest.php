<?php

use App\Order;
use App\Ticket;
use App\Concert;
use Carbon\Carbon;
use App\Facades\TicketCode;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class TicketTest extends TestCase
{
	use DatabaseMigrations;

    /** @test */
    function a_ticket_can_be_reserved()
    {
        $ticket = factory(Ticket::class)->create();
        $this->assertNull($ticket->reserved_at);

        $ticket->reserve();

        $this->assertNotNull($ticket->fresh()->reserved_at);
    }
    /** @test */
    function a_ticket_can_be_released()
    {
        $ticket = factory(Ticket::class)->states('reserved')->create();
        $this->assertNotNull($ticket->reserved_at);

        $ticket->release();

        $this->assertNull($ticket->fresh()->reserved_at);
    }

    /** @test */
    function a_ticket_can_be_claimed_for_an_order()
    {
        $order = factory(Order::class)->create();
        $ticket = factory(Ticket::class)->create(['code' => null]);
        TicketCode::shouldReceive('generateFor')->with($ticket)->andReturn('TICKETCODE1');

        $ticket->claimFor($order);

        // Assert that the ticket is saved to the order
        $this->assertContains($ticket->id, $order->tickets->pluck('id'));

        // Assert that the ticket has the expected ticket code generated
        $this->assertEquals('TICKETCODE1', $ticket->code);
    }
}
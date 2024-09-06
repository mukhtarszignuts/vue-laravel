<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;

use App\Models\Events;
use Illuminate\Http\Response;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;


class EventTest extends TestCase
{
    use RefreshDatabase;


    /** @test */
    public function it_event_list_successfully()
    {
        // authentication user api calling
        $user = User::factory()->create();
        $input = [
            'page' => 1,
            'per_page' => 10,
        ];
        $response = $this->actingAs($user)->post('api/admin/event/list', $input);

        $response->assertStatus(200);
    }

    /** @test */
    public function it_event_store_successfully()
    {
        $user = User::factory()->create();
        // Arrange: Prepare the data and create any necessary prerequisites.
        $data = [
            'type'       => 'Meeting',
            'title'      => 'Project Kickoff',
            'url'        => 'http://example.com',
            'start_date' => '2024-09-01',
            'end_date'   => '2024-09-01',
            'is_allday'  => true,
        ];

        // Act: Make the HTTP request to the store endpoint.
        $response = $this->actingAs($user)->postJson('api/admin/event/create', $data);

        // Assert: Check that the response is as expected.
        $response->assertStatus(Response::HTTP_OK)->assertJson([
            'message' => 'create event successfully..!',
        ]);


        // Assert: Check if the event is actually stored in the database.
        $this->assertDatabaseHas('events', [
            'type'       => 'Meeting',
            'title'      => 'Project Kickoff',
            'url'        => 'http://example.com',
            'start_date' => '2024-09-01',
            'end_date'   => '2024-09-01',
            'is_allday'  => true,
        ]);
    }

    /** @test */
    public function it_updates_an_event_successfully()
    {
        $user = User::factory()->create();
        // Arrange: Create an event to update.
        $event = Events::factory()->create();

        // Define updated data.
        $updatedData = [
            'id'         => $event->id,
            'type'       => 'Workshop',
            'title'      => 'Updated Workshop Title',
            'url'        => 'http://updatedexample.com',
            'start_date' => '2024-10-01',
            'end_date'   => '2024-10-01',
            'is_allday'  => true,
        ];

        // Act: Make the HTTP request to the update endpoint.
        $response = $this->actingAs($user)->postJson('/api/admin/event/update',$updatedData);

        // Assert: Check that the response is as expected.
        $response->assertStatus(Response::HTTP_OK)
                 ->assertJson([
                     'message' => 'Update events successfully..!',
                     'data'   => $updatedData
                 ]);

        // Assert: Check that the event has been updated in the database.
        $this->assertDatabaseHas('events', $updatedData);
    }

    /** @test */
    public function it_event_delete_successfully() {

        $user = User::factory()->create();

         // Arrange: Create an event to delete.
         $event = Events::factory()->create();

         // Act: Make the HTTP request to the delete endpoint.
         $response = $this->actingAs($user)->getJson('api/admin/event/delete/' . $event->id);
 
         // Assert: Check that the response is as expected.
         $response->assertStatus(Response::HTTP_OK)
                  ->assertJson([
                      'message' => 'events delete successfully!',
                  ]);
 
         // Assert: Check that the event is no longer in the database.
         $this->assertDatabaseMissing('events', [
             'id' => $event->id,
         ]);
    }

    /** @test */
    public function it_returns_an_error_when_event_not_found()
    {
        $user = User::factory()->create();

        // Arrange: Define a non-existent event ID.
        $nonExistentId = 999;

        // Act: Make the HTTP request to the delete endpoint with the non-existent ID.
        $response = $this->actingAs($user)->getJson('api/admin/event/delete/' . $nonExistentId);

        // Assert: Check that the response contains the correct error message.
        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJson([
                'message' => 'Events not found',
            ]);
    }
}

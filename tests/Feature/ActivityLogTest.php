<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Activitylog\Models\Activity;
use Tests\TestCase;

class ActivityLogTest extends TestCase
{
    use RefreshDatabase;

    public function test_activity_is_recorded_with_its_causer_and_subject(): void
    {
        $user = User::factory()->create();

        activity()
            ->causedBy($user)
            ->performedOn($user)
            ->withProperties(['field' => 'name'])
            ->log('updated profile');

        $activity = Activity::query()->where('description', 'updated profile')->sole();
        $this->assertSame('updated profile', $activity->description);
        $this->assertTrue($activity->causer->is($user));
        $this->assertTrue($activity->subject->is($user));
        $this->assertSame('name', $activity->properties['field']);
    }
}

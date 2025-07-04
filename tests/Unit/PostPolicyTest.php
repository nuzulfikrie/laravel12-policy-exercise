<?php

use App\Models\Post;
use App\Models\User;
use App\Policies\PostPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    $this->policy = new PostPolicy();
    $this->user = User::factory()->create();
    $this->otherUser = User::factory()->create();
    $this->post = Post::factory()->create(['user_id' => $this->user->id]);
});

describe('PostPolicy Unit Tests', function () {

    describe('viewAny method', function () {
        it('allows anyone to view posts list', function () {
            $result = $this->policy->viewAny($this->user);
            expect($result)->toBeTrue();
        });

        it('allows unauthenticated users to view posts list', function () {
            $result = $this->policy->viewAny(null);
            expect($result)->toBeTrue();
        });
    });

    describe('view method', function () {
        it('allows post owner to view their own post', function () {
            $result = $this->policy->view($this->user, $this->post);
            expect($result)->toBeTrue();
        });

        it('denies unauthenticated users from viewing posts', function () {
            $result = $this->policy->view(null, $this->post);
            expect($result)->toBeFalse();
        });

        it('denies other users from viewing posts they do not own', function () {
            $result = $this->policy->view($this->otherUser, $this->post);
            expect($result)->toBeFalse();
        });
    });

    describe('create method', function () {
        it('allows authenticated users to create posts', function () {
            $result = $this->policy->create($this->user);
            expect($result)->toBeTrue();
        });

        it('denies unauthenticated users from creating posts', function () {
            $result = $this->policy->create(null);
            expect($result)->toBeFalse();
        });
    });

    describe('update method', function () {
        it('allows post owner to update their post', function () {
            $result = $this->policy->update($this->user, $this->post);
            expect($result)->toBeTrue();
        });

        it('denies other users from updating posts they do not own', function () {
            $result = $this->policy->update($this->otherUser, $this->post);
            expect($result)->toBeFalse();
        });

        it('denies unauthenticated users from updating posts', function () {
            $result = $this->policy->update(null, $this->post);
            expect($result)->toBeFalse();
        });

        it('correctly compares user IDs', function () {
            // Test with different user IDs
            $differentUser = User::factory()->create();
            $result = $this->policy->update($differentUser, $this->post);
            expect($result)->toBeFalse();
        });
    });

    describe('delete method', function () {
        it('allows post owner to delete their post', function () {
            $result = $this->policy->delete($this->user, $this->post);
            expect($result)->toBeTrue();
        });

        it('denies other users from deleting posts they do not own', function () {
            $result = $this->policy->delete($this->otherUser, $this->post);
            expect($result)->toBeFalse();
        });

        it('denies unauthenticated users from deleting posts', function () {
            $result = $this->policy->delete(null, $this->post);
            expect($result)->toBeFalse();
        });

        it('correctly compares user IDs for deletion', function () {
            $differentUser = User::factory()->create();
            $result = $this->policy->delete($differentUser, $this->post);
            expect($result)->toBeFalse();
        });
    });

    describe('semak method', function () {
        it('allows post owner to review their post', function () {
            $result = $this->policy->semak($this->user, $this->post);
            expect($result)->toBeTrue();
        });

        it('denies other users from reviewing posts they do not own', function () {
            $result = $this->policy->semak($this->otherUser, $this->post);
            expect($result)->toBeFalse();
        });

        it('denies unauthenticated users from reviewing posts', function () {
            $result = $this->policy->semak(null, $this->post);
            expect($result)->toBeFalse();
        });
    });
});

describe('PostPolicy Edge Cases', function () {

    it('handles null user gracefully in create method', function () {
        $result = $this->policy->create(null);
        expect($result)->toBeFalse();
    });

    it('handles null user gracefully in update method', function () {
        $result = $this->policy->update(null, $this->post);
        expect($result)->toBeFalse();
    });

    it('handles null user gracefully in delete method', function () {
        $result = $this->policy->delete(null, $this->post);
        expect($result)->toBeFalse();
    });

    it('handles null user gracefully in semak method', function () {
        $result = $this->policy->semak(null, $this->post);
        expect($result)->toBeFalse();
    });

    it('handles posts with different user_id values', function () {
        $differentUser = User::factory()->create();
        $postWithDifferentOwner = Post::factory()->create(['user_id' => $differentUser->id]);

        $result = $this->policy->update($this->user, $postWithDifferentOwner);
        expect($result)->toBeFalse();

        $result = $this->policy->delete($this->user, $postWithDifferentOwner);
        expect($result)->toBeFalse();
    });

    it('handles string vs integer user_id comparison', function () {
        // Test with string user_id (as might happen with some databases)
        $postWithStringUserId = Post::factory()->create(['user_id' => (string) $this->user->id]);

        $result = $this->policy->update($this->user, $postWithStringUserId);
        expect($result)->toBeTrue();

        $result = $this->policy->delete($this->user, $postWithStringUserId);
        expect($result)->toBeTrue();
    });
});

describe('PostPolicy Integration with Laravel Authorization', function () {

    it('works with Laravel\'s Gate facade', function () {
        $this->actingAs($this->user);

        expect(Gate::allows('viewAny', Post::class))->toBeTrue();
        expect(Gate::allows('view', $this->post))->toBeTrue();
        expect(Gate::allows('create', Post::class))->toBeTrue();
        expect(Gate::allows('update', $this->post))->toBeTrue();
        expect(Gate::allows('delete', $this->post))->toBeTrue();
        expect(Gate::allows('semak', $this->post))->toBeTrue();
    });

    it('works with other users through Gate facade', function () {
        $this->actingAs($this->otherUser);

        expect(Gate::allows('viewAny', Post::class))->toBeTrue();
        expect(Gate::allows('view', $this->post))->toBeFalse();
        expect(Gate::allows('create', Post::class))->toBeTrue();
        expect(Gate::allows('update', $this->post))->toBeFalse();
        expect(Gate::allows('delete', $this->post))->toBeFalse();
        expect(Gate::allows('semak', $this->post))->toBeFalse();
    });

    it('works with unauthenticated users through Gate facade', function () {
        expect(Gate::allows('viewAny', Post::class))->toBeTrue();
        expect(Gate::allows('view', $this->post))->toBeFalse();
        expect(Gate::allows('create', Post::class))->toBeFalse();
        expect(Gate::allows('update', $this->post))->toBeFalse();
        expect(Gate::allows('delete', $this->post))->toBeFalse();
        expect(Gate::allows('semak', $this->post))->toBeFalse();
    });
});

describe('PostPolicy Performance Tests', function () {

    it('performs authorization checks efficiently', function () {
        $startTime = microtime(true);

        for ($i = 0; $i < 1000; $i++) {
            $this->policy->update($this->user, $this->post);
        }

        $endTime = microtime(true);
        $executionTime = $endTime - $startTime;

        // Should complete 1000 checks in less than 1 second
        expect($executionTime)->toBeLessThan(1.0);
    });

    it('handles multiple posts efficiently', function () {
        $posts = Post::factory()->count(100)->create(['user_id' => $this->user->id]);

        $startTime = microtime(true);

        foreach ($posts as $post) {
            $this->policy->update($this->user, $post);
        }

        $endTime = microtime(true);
        $executionTime = $endTime - $startTime;

        // Should complete 100 checks in less than 0.1 seconds
        expect($executionTime)->toBeLessThan(0.1);
    });
});

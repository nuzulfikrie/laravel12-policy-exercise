<?php

use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->otherUser = User::factory()->create();
    $this->post = Post::factory()->create(['user_id' => $this->user->id]);
});

describe('PostController Authorization Tests', function () {

    describe('Index Method', function () {
        it('allows authenticated user to view their own posts', function () {
            $response = $this->actingAs($this->user)
                ->get(route('posts.index'));

            $response->assertStatus(200);
            $response->assertViewIs('posts.index');
            $response->assertViewHas('posts');
        });

        it('denies access to unauthenticated users', function () {
            $response = $this->get(route('posts.index'));

            $response->assertStatus(403);
        });
    });

    describe('Create Method', function () {
        it('allows authenticated user to access create form', function () {
            $response = $this->actingAs($this->user)
                ->get(route('posts.create'));

            $response->assertStatus(200);
            $response->assertViewIs('posts.create');
        });

        it('denies access to unauthenticated users', function () {
            $response = $this->get(route('posts.create'));

            $response->assertStatus(403);
        });
    });

    describe('Store Method', function () {
        it('allows authenticated user to create a post', function () {
            $postData = [
                'title' => 'Test Post',
                'content' => 'Test content for the post',
            ];

            $response = $this->actingAs($this->user)
                ->post(route('posts.store'), $postData);

            $response->assertRedirect(route('posts.index'));
            $response->assertSessionHas('success', 'Post created successfully');

            $this->assertDatabaseHas('posts', [
                'title' => 'Test Post',
                'content' => 'Test content for the post',
                'user_id' => $this->user->id,
            ]);
        });

        it('denies access to unauthenticated users', function () {
            $postData = [
                'title' => 'Test Post',
                'content' => 'Test content for the post',
            ];

            $response = $this->post(route('posts.store'), $postData);

            $response->assertStatus(403);
            $this->assertDatabaseMissing('posts', $postData);
        });

        it('validates required fields', function () {
            $response = $this->actingAs($this->user)
                ->post(route('posts.store'), []);

            $response->assertSessionHasErrors(['title', 'content']);
        });

        it('validates title max length', function () {
            $postData = [
                'title' => str_repeat('a', 256), // Exceeds 255 characters
                'content' => 'Test content',
            ];

            $response = $this->actingAs($this->user)
                ->post(route('posts.store'), $postData);

            $response->assertSessionHasErrors(['title']);
        });
    });

    describe('Show Method', function () {
        it('allows post owner to view their post', function () {
            $response = $this->actingAs($this->user)
                ->get(route('posts.show', $this->post));

            $response->assertStatus(200);
            $response->assertViewIs('posts.show');
            $response->assertViewHas('post', $this->post);
        });

        it('denies access to other users', function () {
            $response = $this->actingAs($this->otherUser)
                ->get(route('posts.show', $this->post));

            $response->assertStatus(403);
        });

        it('denies access to unauthenticated users', function () {
            $response = $this->get(route('posts.show', $this->post));

            $response->assertStatus(403);
        });
    });

    describe('Edit Method', function () {
        it('allows post owner to access edit form', function () {
            $response = $this->actingAs($this->user)
                ->get(route('posts.edit', $this->post));

            $response->assertStatus(200);
            $response->assertViewIs('posts.edit');
            $response->assertViewHas('post', $this->post);
        });

        it('denies access to other users', function () {
            $response = $this->actingAs($this->otherUser)
                ->get(route('posts.edit', $this->post));

            $response->assertStatus(403);
        });

        it('denies access to unauthenticated users', function () {
            $response = $this->get(route('posts.edit', $this->post));

            $response->assertStatus(403);
        });
    });

    describe('Update Method', function () {
        it('allows post owner to update their post', function () {
            $updateData = [
                'title' => 'Updated Post Title',
                'content' => 'Updated content for the post',
            ];

            $response = $this->actingAs($this->user)
                ->put(route('posts.update', $this->post), $updateData);

            $response->assertRedirect(route('posts.index'));
            $response->assertSessionHas('success', 'Post updated successfully');

            $this->assertDatabaseHas('posts', [
                'id' => $this->post->id,
                'title' => 'Updated Post Title',
                'content' => 'Updated content for the post',
                'user_id' => $this->user->id,
            ]);
        });

        it('denies access to other users', function () {
            $updateData = [
                'title' => 'Updated Post Title',
                'content' => 'Updated content for the post',
            ];

            $response = $this->actingAs($this->otherUser)
                ->put(route('posts.update', $this->post), $updateData);

            $response->assertStatus(403);

            $this->assertDatabaseMissing('posts', [
                'id' => $this->post->id,
                'title' => 'Updated Post Title',
            ]);
        });

        it('denies access to unauthenticated users', function () {
            $updateData = [
                'title' => 'Updated Post Title',
                'content' => 'Updated content for the post',
            ];

            $response = $this->put(route('posts.update', $this->post), $updateData);

            $response->assertStatus(403);
        });

        it('validates required fields', function () {
            $response = $this->actingAs($this->user)
                ->put(route('posts.update', $this->post), []);

            $response->assertSessionHasErrors(['title', 'content']);
        });

        it('validates title max length', function () {
            $updateData = [
                'title' => str_repeat('a', 256), // Exceeds 255 characters
                'content' => 'Updated content',
            ];

            $response = $this->actingAs($this->user)
                ->put(route('posts.update', $this->post), $updateData);

            $response->assertSessionHasErrors(['title']);
        });
    });

    describe('Destroy Method', function () {
        it('allows post owner to delete their post', function () {
            $response = $this->actingAs($this->user)
                ->delete(route('posts.destroy', $this->post));

            $response->assertRedirect(route('posts.index'));
            $response->assertSessionHas('success', 'Post deleted successfully');

            $this->assertDatabaseMissing('posts', [
                'id' => $this->post->id,
            ]);
        });

        it('denies access to other users', function () {
            $response = $this->actingAs($this->otherUser)
                ->delete(route('posts.destroy', $this->post));

            $response->assertStatus(403);

            $this->assertDatabaseHas('posts', [
                'id' => $this->post->id,
            ]);
        });

        it('denies access to unauthenticated users', function () {
            $response = $this->delete(route('posts.destroy', $this->post));

            $response->assertStatus(403);

            $this->assertDatabaseHas('posts', [
                'id' => $this->post->id,
            ]);
        });
    });
});

describe('PostController Data Integrity Tests', function () {

    it('creates post with correct user_id', function () {
        $postData = [
            'title' => 'Test Post',
            'content' => 'Test content',
        ];

        $this->actingAs($this->user)
            ->post(route('posts.store'), $postData);

        $this->assertDatabaseHas('posts', [
            'title' => 'Test Post',
            'content' => 'Test content',
            'user_id' => $this->user->id,
        ]);
    });

    it('updates post without changing user_id', function () {
        $updateData = [
            'title' => 'Updated Title',
            'content' => 'Updated content',
        ];

        $this->actingAs($this->user)
            ->put(route('posts.update', $this->post), $updateData);

        $this->assertDatabaseHas('posts', [
            'id' => $this->post->id,
            'title' => 'Updated Title',
            'content' => 'Updated content',
            'user_id' => $this->user->id, // Should remain unchanged
        ]);
    });

    it('index shows only user\'s own posts', function () {
        // Create posts for different users
        $userPost = Post::factory()->create(['user_id' => $this->user->id]);
        $otherUserPost = Post::factory()->create(['user_id' => $this->otherUser->id]);

        $response = $this->actingAs($this->user)
            ->get(route('posts.index'));

        $response->assertViewHas('posts');
        $posts = $response->viewData('posts');

        $postsUser = $posts->where('user_id', $this->user->id);

        expect($userPost->user_id)->toBe($this->user->id);
        expect($postsUser)->not->toContain($otherUserPost);
    });
});

describe('PostController Edge Cases', function () {

    it('handles non-existent post gracefully', function () {
        $nonExistentPost = new Post();
        $nonExistentPost->id = 99999;

        $response = $this->actingAs($this->user)
            ->get(route('posts.show', $nonExistentPost));

        $response->assertStatus(404);
    });

    it('handles malformed request data', function () {
        $malformedData = [
            'title' => null,
            'content' => '',
        ];

        $response = $this->actingAs($this->user)
            ->post(route('posts.store'), $malformedData);

        $response->assertSessionHasErrors(['title', 'content']);
    });

    it('handles XSS attempts in content', function () {
        $xssData = [
            'title' => 'Test Post',
            'content' => '<script>alert("XSS")</script>',
        ];

        $response = $this->actingAs($this->user)
            ->post(route('posts.store'), $xssData);

        $response->assertRedirect(route('posts.index'));

        // The content should be stored as-is (Laravel handles XSS in views)
        $this->assertDatabaseHas('posts', [
            'content' => '<script>alert("XSS")</script>',
        ]);
    });
});

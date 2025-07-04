<?php

namespace Tests\Helpers;

use App\Models\Post;
use App\Models\User;

class PostTestHelper
{
    /**
     * Create test post data
     * @param array<string, mixed> $overrides
     * @return array<string, string>
     */
    public static function createPostData(array $overrides = []): array
    {
        return array_merge([
            'title' => 'Test Post Title',
            'content' => 'Test post content for testing purposes.',
        ], $overrides);
    }

    /**
     * Create invalid post data for validation testing
     * @return array<string, string>
     */
    public static function createInvalidPostData(): array
    {
        return [
            'title' => '', // Empty title
            'content' => '', // Empty content
        ];
    }

    /**
     * Create post data that exceeds validation limits
     * @return array<string, string>
     */
    public static function createOversizedPostData(): array
    {
        return [
            'title' => str_repeat('a', 256), // Exceeds 255 character limit
            'content' => 'Valid content',
        ];
    }

    /**
     * Create XSS test data
     * @return array<string, string>
     */
    public static function createXssPostData(): array
    {
        return [
            'title' => 'Test Post',
            'content' => '<script>alert("XSS")</script><img src="x" onerror="alert(\'XSS\')">',
        ];
    }

    /**
     * Assert that a post was created with correct data
     * @param array<string, string> $postData
     */
    public static function assertPostCreated(array $postData, User $user): void
    {
        expect(Post::where('user_id', $user->id)->count())->toBeGreaterThan(0);

        $post = Post::where('user_id', $user->id)
            ->where('title', $postData['title'])
            ->where('content', $postData['content'])
            ->first();

        expect($post)->not->toBeNull();
        expect($post->user_id)->toBe($user->id);
    }

    /**
     * Assert that a post was updated with correct data
     * @param array<string, string> $updateData
     */
    public static function assertPostUpdated(Post $post, array $updateData): void
    {
        $post->refresh();

        expect($post->title)->toBe($updateData['title']);
        expect($post->content)->toBe($updateData['content']);
    }

    /**
     * Assert that a post was deleted
     */
    public static function assertPostDeleted(Post $post): void
    {
        expect(Post::find($post->id))->toBeNull();
    }

    /**
     * Assert that a post was not deleted
     */
    public static function assertPostNotDeleted(Post $post): void
    {
        expect(Post::find($post->id))->not->toBeNull();
    }
}

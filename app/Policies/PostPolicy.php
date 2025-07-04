<?php

namespace App\Policies;

use App\Models\Post;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class PostPolicy
{
    use HandlesAuthorization;

    public function viewAny(?User $user): bool
    {
        return true; // Sesiapa boleh melihat senarai post
    }

    public function view(?User $user, Post $post): bool
    {
        return $user && $user->id == $post->user_id; // Hanya pemilik post boleh melihat post tunggal
    }

    public function create(?User $user): bool
    {
        return $user !== null; // Hanya pengguna yang log masuk boleh cipta post
    }

    public function update(?User $user, Post $post): bool
    {
        return $user && $user->id == $post->user_id; // Hanya pemilik post boleh kemas kini
    }

    public function delete(?User $user, Post $post): bool
    {
        return $user && $user->id == $post->user_id; // Hanya pemilik post boleh hapus
    }

    public function semak(?User $user, Post $post): bool
    {
        return $user && $user->id == $post->user_id; // Hanya pemilik post boleh semak
    }
}

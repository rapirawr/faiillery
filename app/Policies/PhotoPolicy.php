<?php

namespace App\Policies;

use App\Models\Photo;
use App\Models\User;

class PhotoPolicy
{
    /**
     * Determine whether the user can update the photo.
     */
    public function update(User $user, Photo $photo): bool
    {
        return $user->id === $photo->user_id;
    }

    /**
     * Determine whether the user can delete the photo.
     */
    public function delete(User $user, Photo $photo): bool
    {
        return $user->id === $photo->user_id;
    }
}

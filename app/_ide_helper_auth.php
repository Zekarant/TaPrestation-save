<?php

/**
 * IDE Helper for Auth facade — tells static analysis that Auth::user() returns our User model.
 */

namespace Illuminate\Support\Facades {

    /**
     * @method static \App\Models\User|null user()
     * @method static int|string|null id()
     * @method static bool check()
     * @method static bool guest()
     */
    class Auth
    {
    }
}

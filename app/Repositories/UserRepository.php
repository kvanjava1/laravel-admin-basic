<?php

namespace App\Repositories;

use App\Models\User;
use Illuminate\Support\Facades\DB;

class UserRepository
{
    /**
     * Create a new user.
     *
     * @param array $data
     * @return User
     */
    public function create(array $data)
    {
        return User::create($data);
    }

    /**
     * Update an existing user.
     *
     * @param User $user
     * @param array $data
     * @return User
     */
    public function update(User $user, array $data)
    {
        $user->update($data);
        return $user;
    }

    /**
     * Get paginated users.
     */
    public function paginate(int $perPage = 10, array $filters = [])
    {
        $query = User::with(['status', 'roles'])->latest();

        // 1. Search (Name or Email)
        if (!empty($filters['search'])) {
            $search = '%' . $filters['search'] . '%';
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', $search)
                    ->orWhere('email', 'like', $search);
            });
        }

        // 2. Status Name Filter
        if (!empty($filters['status'])) {
            $query->whereHas('status', function ($q) use ($filters) {
                $q->where('name', $filters['status']);
            });
        }

        // 3. Created At Date Filters
        if (!empty($filters['created_from'])) {
            $query->whereDate('created_at', '>=', $filters['created_from']);
        }
        if (!empty($filters['created_to'])) {
            $query->whereDate('created_at', '<=', $filters['created_to']);
        }

        // 4. Updated At Date Filters
        if (!empty($filters['updated_from'])) {
            $query->whereDate('updated_at', '>=', $filters['updated_from']);
        }
        if (!empty($filters['updated_to'])) {
            $query->whereDate('updated_at', '<=', $filters['updated_to']);
        }

        return $query->paginate($perPage);
    }

    /**
     * Get user statistics by status.
     */
    public function getStats()
    {
        return User::join('user_statuses', 'users.status_id', '=', 'user_statuses.id')
            ->select('user_statuses.name', DB::raw('count(*) as total'))
            ->groupBy('user_statuses.name')
            ->pluck('total', 'name');
    }

    /**
     * Delete a user.
     *
     * @param User $user
     * @return bool|null
     */
    public function delete(User $user)
    {
        return $user->delete();
    }
}

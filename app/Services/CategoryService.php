<?php

namespace App\Services;

use App\Models\Category;
use App\Models\User;

class CategoryService
{
    /**
     * Copy default category templates to a newly registered user (K-18).
     */
    public function copyDefaultCategoriesToUser(User $user): void
    {
        $templates = Category::defaultTemplates()->get();

        foreach ($templates as $template) {
            Category::create([
                'user_id' => $user->id,
                'name' => $template->name,
                'type' => $template->type,
                'color' => $template->color,
                'icon' => $template->icon,
                'archived_at' => null,
            ]);
        }
    }
}

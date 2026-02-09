<?php

namespace Admin\Services;

class SlugService
{
    public function createSlug(string $title): string
    {
        $slug = strtolower($title);

        $slug = preg_replace('/[^a-z0-9\s-]/', '', $slug);

        $slug = preg_replace('/[\s-]+/', '-', $slug);

        $slug = trim($slug, '-');

        return $slug;
    }
}
